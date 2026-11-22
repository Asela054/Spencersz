<?php
class Comboinfo extends CI_Model{

    private function genCode(){
        $row=$this->db->query("SELECT MAX(CAST(SUBSTRING(product_code,4) AS UNSIGNED)) AS n FROM tbl_product WHERE product_code LIKE 'CMB%'")->row();
        $n=((int)$row->n)+1;
        return 'CMB'.str_pad($n,4,'0',STR_PAD_LEFT);
    }

    private function fail($msg){
        return array('status'=>0,'msg'=>$msg);
    }

    private function flash($icon,$msg,$type){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$msg;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;
        $this->session->set_flashdata('msg', json_encode($actionObj));
    }

    // total available stock of a product (all branches, or one branch)
    private function Stockqty($productID, $branchID=null){
        $this->db->select_sum('qty');
        $this->db->from('tbl_stock');
        $this->db->where('tbl_product_idtbl_product', $productID);
        $this->db->where('status', 1);
        $this->db->where('stock_status', 1);
        if(!empty($branchID)){$this->db->where('tbl_company_branch_idtbl_company_branch', $branchID);}
        $r=$this->db->get()->row();
        return (float)($r->qty ? $r->qty : 0);
    }

    public function Getproducts(){
        $this->db->select('idtbl_product, product_code, product_name, model_no, cost_price, selling_price, warranty_months, has_serial');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->where('is_combo', 0);
        $this->db->order_by('product_name', 'ASC');
        return $this->db->get()->result();
    }

    public function Productwarranty($productID){
        $p=$this->db->select('warranty_months')->get_where('tbl_product', array('idtbl_product'=>$productID))->row();
        $parts=$this->db->select('warranty_name AS name, warranty_months AS months')
                        ->get_where('tbl_product_warranty', array('tbl_product_idtbl_product'=>$productID, 'status'=>1))->result();
        return array(
            'warranty_months'=> $p ? (int)$p->warranty_months : 0,
            'parts'=> $parts
        );
    }

    public function Combosave(){
        $userID=$_SESSION['userid'];
        $now=date('Y-m-d H:i:s');

        $name=trim($this->input->post('combo_name'));
        $desc=trim($this->input->post('description'));
        $price=round((float)$this->input->post('combo_price'),2);
        $items=json_decode($this->input->post('items'), true);
        $option=$this->input->post('recordOption');
        $comboID=(int)$this->input->post('recordID');

        if($name==''){return $this->fail('Combo name is required');}
        if($price<=0){return $this->fail('Combo price must be greater than 0');}
        if(!is_array($items) || count($items)<2){return $this->fail('A combo needs at least 2 components');}

        // ---- build and validate component lines
        $lines=array(); $seen=array();
        $totalCost=0; $listTotal=0; $totalWeight=0;

        foreach($items as $it){
            $pid=(int)$it['product_id'];
            $qty=(int)$it['qty'];
            if($pid<=0 || $qty<1){return $this->fail('Invalid component or quantity');}
            if(isset($seen[$pid])){return $this->fail('Same product added twice');}
            $seen[$pid]=1;

            $p=$this->db->get_where('tbl_product', array('idtbl_product'=>$pid, 'is_combo'=>0, 'status'=>1))->row();
            if(!$p){return $this->fail('A selected product is not available');}

            $weight=($p->selling_price>0) ? (float)$p->selling_price : (float)$p->cost_price;

            $parts=array();
            if(!empty($it['parts']) && is_array($it['parts'])){
                foreach($it['parts'] as $pt){
                    $pn=trim($pt['name']); $pm=(int)$pt['months'];
                    if($pn!='' && $pm>0){$parts[]=array('name'=>$pn,'months'=>$pm);}
                }
            }

            $lines[]=array(
                'product_id'=>$pid,
                'qty'=>$qty,
                'cost'=>(float)$p->cost_price,
                'list'=>(float)$p->selling_price,
                'weight'=>$weight,
                'warranty_months'=>max(0,(int)$it['warranty_months']),
                'parts'=>$parts,
                'unit_price'=>0
            );
            $totalCost+=$p->cost_price*$qty;
            $listTotal+=$p->selling_price*$qty;
            $totalWeight+=$weight*$qty;
        }
        if($totalWeight<=0){return $this->fail('Components have no price or cost');}

        // ---- spread the combo price over the components (last line takes the rounding)
        $allocated=0; $n=count($lines);
        foreach($lines as $k=>$l){
            if($k==$n-1){
                $lineTotal=round($price-$allocated,2);
            }else{
                $lineTotal=round($price*($l['weight']*$l['qty'])/$totalWeight,2);
                $allocated+=$lineTotal;
            }
            $lines[$k]['unit_price']=round($lineTotal/$l['qty'],4);
        }

        // ---- duplicate name check
        $this->db->from('tbl_product');
        $this->db->where('product_name', $name);
        $this->db->where_in('status', array(1,2));
        if($option!=1 && $comboID>0){
            $cur=$this->db->get_where('tbl_combo', array('idtbl_combo'=>$comboID))->row();
            if($cur){$this->db->where('idtbl_product !=', $cur->tbl_product_idtbl_product);}
        }
        if($this->db->count_all_results()>0){return $this->fail('A product with this name already exists');}

        $combo=null;
        if($option!=1){
            $combo=$this->db->get_where('tbl_combo', array('idtbl_combo'=>$comboID))->row();
            if(!$combo){return $this->fail('Combo not found');}
        }

        // category + unit
        $cat=$this->db->get_where('tbl_category', array('category'=>'Combo Pack'))->row();
        $catID=$cat ? $cat->idtbl_category : null;
        $unit=$this->db->query("SELECT idtbl_unit FROM tbl_unit WHERE status=1 ORDER BY idtbl_unit LIMIT 1")->row();
        $unitID=$unit ? $unit->idtbl_unit : null;

        $this->db->trans_start();

        if($option==1){
            $code=$this->genCode();
            $this->db->insert('tbl_product', array(
                'product_code'=>$code,
                'barcode'=>$code,
                'product_name'=>$name,
                'model_no'=>'COMBO',
                'description'=>$desc,
                'tbl_category_idtbl_category'=>$catID,
                'tbl_brand_idtbl_brand'=>null,
                'tbl_unit_idtbl_unit'=>$unitID,
                'cost_price'=>round($totalCost,2),
                'selling_price'=>$price,
                'discount_percent'=>0,
                'reorder_level'=>0,
                'has_serial'=>0,          // serials are captured on the components
                'warranty_months'=>0,     // warranty is taken from the components
                'has_free_scheme'=>0,
                'is_combo'=>1,
                'status'=>1,
                'insertdatetime'=>$now,
                'tbl_user_idtbl_user'=>$userID
            ));
            $productID=$this->db->insert_id();

            $this->db->insert('tbl_combo', array(
                'combo_name'=>$name,
                'description'=>$desc,
                'total_cost'=>round($totalCost,4),
                'list_total'=>round($listTotal,4),
                'combo_price'=>$price,
                'status'=>1,
                'insertdatetime'=>$now,
                'tbl_user_idtbl_user'=>$userID,
                'tbl_product_idtbl_product'=>$productID
            ));
            $comboID=$this->db->insert_id();
        }else{
            $productID=$combo->tbl_product_idtbl_product;
            $old=$this->db->select('selling_price')->get_where('tbl_product', array('idtbl_product'=>$productID))->row();

            $this->db->where('idtbl_product', $productID);
            $this->db->update('tbl_product', array(
                'product_name'=>$name,
                'description'=>$desc,
                'cost_price'=>round($totalCost,2),
                'selling_price'=>$price,
                'updatedatetime'=>$now,
                'updateuser'=>$userID
            ));

            $this->db->where('idtbl_combo', $comboID);
            $this->db->update('tbl_combo', array(
                'combo_name'=>$name,
                'description'=>$desc,
                'total_cost'=>round($totalCost,4),
                'list_total'=>round($listTotal,4),
                'combo_price'=>$price,
                'updatedatetime'=>$now,
                'updateuser'=>$userID
            ));

            if($old && (float)$old->selling_price != (float)$price){
                $this->db->insert('tbl_product_price_history', array(
                    'old_selling_price'=>$old->selling_price,
                    'new_selling_price'=>$price,
                    'remark'=>'Combo update',
                    'status'=>1,
                    'insertdatetime'=>$now,
                    'tbl_user_idtbl_user'=>$userID,
                    'tbl_product_idtbl_product'=>$productID
                ));
            }

            // replace components (nothing else references them)
            $this->db->query("DELETE w FROM tbl_combo_detail_warranty w INNER JOIN tbl_combo_detail d ON d.idtbl_combo_detail=w.tbl_combo_detail_idtbl_combo_detail WHERE d.tbl_combo_idtbl_combo=?", array($comboID));
            $this->db->delete('tbl_combo_detail', array('tbl_combo_idtbl_combo'=>$comboID));
        }

        foreach($lines as $l){
            $this->db->insert('tbl_combo_detail', array(
                'qty'=>$l['qty'],
                'cost_price'=>$l['cost'],
                'list_price'=>$l['list'],
                'unit_price'=>$l['unit_price'],
                'warranty_months'=>$l['warranty_months'],
                'status'=>1,
                'insertdatetime'=>$now,
                'tbl_user_idtbl_user'=>$userID,
                'tbl_combo_idtbl_combo'=>$comboID,
                'tbl_product_idtbl_product'=>$l['product_id']
            ));
            $detailID=$this->db->insert_id();

            foreach($l['parts'] as $pt){
                $this->db->insert('tbl_combo_detail_warranty', array(
                    'warranty_name'=>$pt['name'],
                    'warranty_months'=>$pt['months'],
                    'status'=>1,
                    'insertdatetime'=>$now,
                    'tbl_user_idtbl_user'=>$userID,
                    'tbl_combo_detail_idtbl_combo_detail'=>$detailID
                ));
            }
        }

        $this->db->trans_complete();

        if($this->db->trans_status()===FALSE){
            return $this->fail('Record Error');
        }
        return array('status'=>1, 'msg'=>($option==1 ? 'Combo Added Successfully' : 'Combo Updated Successfully'));
    }

    // combo header + components (+ parts warranties, + stock when $withStock)
    public function Combodetail($comboID, $withStock=false){
        $c=$this->db->query("SELECT c.*, p.product_code FROM tbl_combo c INNER JOIN tbl_product p ON p.idtbl_product=c.tbl_product_idtbl_product WHERE c.idtbl_combo=? AND c.status IN (1,2)", array($comboID))->row();
        if(!$c){return array('status'=>0);}

        $details=$this->db->query("SELECT d.*, p.product_code, p.product_name, p.model_no, p.cost_price AS cur_cost, p.selling_price AS cur_price, p.has_serial
                                   FROM tbl_combo_detail d INNER JOIN tbl_product p ON p.idtbl_product=d.tbl_product_idtbl_product
                                   WHERE d.tbl_combo_idtbl_combo=? AND d.status=1 ORDER BY d.idtbl_combo_detail", array($comboID))->result();

        $items=array(); $combosAvail=null;
        foreach($details as $d){
            $parts=$this->db->select('warranty_name AS name, warranty_months AS months')
                            ->get_where('tbl_combo_detail_warranty', array('tbl_combo_detail_idtbl_combo_detail'=>$d->idtbl_combo_detail, 'status'=>1))->result();
            $row=array(
                'product_id'=>$d->tbl_product_idtbl_product,
                'product_code'=>$d->product_code,
                'product_name'=>$d->product_name,
                'model_no'=>$d->model_no,
                'qty'=>(int)$d->qty,
                'cost_price'=>(float)$d->cur_cost,
                'selling_price'=>(float)$d->cur_price,
                'unit_price'=>(float)$d->unit_price,
                'warranty_months'=>(int)$d->warranty_months,
                'parts'=>$parts
            );
            if($withStock){
                $stock=$this->Stockqty($d->tbl_product_idtbl_product);
                $row['stock']=$stock;
                $can=floor($stock/max(1,$d->qty));
                $combosAvail=($combosAvail===null) ? $can : min($combosAvail,$can);
            }
            $items[]=$row;
        }

        return array(
            'status'=>1,
            'id'=>$c->idtbl_combo,
            'product_id'=>$c->tbl_product_idtbl_product,
            'code'=>$c->product_code,
            'combo_name'=>$c->combo_name,
            'description'=>$c->description,
            'combo_price'=>(float)$c->combo_price,
            'total_cost'=>(float)$c->total_cost,
            'items'=>$items,
            'combos_available'=>($combosAvail===null ? 0 : $combosAvail)
        );
    }

    public function Combostatus($x, $y){
        $userID=$_SESSION['userid'];
        $comboID=(int)$x;
        $type=(int)$y;
        $now=date('Y-m-d H:i:s');

        $combo=$this->db->get_where('tbl_combo', array('idtbl_combo'=>$comboID))->row();
        if(!$combo || !in_array($type, array(1,2,3))){redirect('Combo');}

        $this->db->trans_start();
        $this->db->where('idtbl_combo', $comboID);
        $this->db->update('tbl_combo', array('status'=>$type, 'updateuser'=>$userID, 'updatedatetime'=>$now));

        $this->db->where('idtbl_product', $combo->tbl_product_idtbl_product);
        $this->db->update('tbl_product', array('status'=>$type, 'updateuser'=>$userID, 'updatedatetime'=>$now));
        $this->db->trans_complete();

        if($this->db->trans_status()===TRUE){
            if($type==1){$this->flash('fas fa-check','Record Activate Successfully','success');}
            else if($type==2){$this->flash('fas fa-times','Record Deactivate Successfully','warning');}
            else{$this->flash('fas fa-trash-alt','Record Remove Successfully','danger');}
        }else{
            $this->flash('fas fa-warning','Record Error','danger');
        }
        redirect('Combo');
    }

    // Lines to add on an invoice when a combo product is sold
    public function Comboexpand($productID, $packs=1, $branchID=null){
        $c=$this->db->get_where('tbl_combo', array('tbl_product_idtbl_product'=>$productID, 'status'=>1))->row();
        if(!$c){return array('status'=>0, 'msg'=>'Combo not found or inactive');}

        $details=$this->db->query("SELECT d.*, p.product_code, p.product_name, p.has_serial
                                   FROM tbl_combo_detail d INNER JOIN tbl_product p ON p.idtbl_product=d.tbl_product_idtbl_product
                                   WHERE d.tbl_combo_idtbl_combo=? AND d.status=1 ORDER BY d.idtbl_combo_detail", array($c->idtbl_combo))->result();

        $lines=array(); $ok=true;
        foreach($details as $d){
            $need=$d->qty*$packs;
            $stock=$this->Stockqty($d->tbl_product_idtbl_product, $branchID);
            if($stock<$need){$ok=false;}

            $parts=$this->db->select('warranty_name AS name, warranty_months AS months')
                            ->get_where('tbl_combo_detail_warranty', array('tbl_combo_detail_idtbl_combo_detail'=>$d->idtbl_combo_detail, 'status'=>1))->result();

            $lines[]=array(
                'product_id'=>$d->tbl_product_idtbl_product,
                'product_code'=>$d->product_code,
                'product_name'=>$d->product_name,
                'qty'=>$need,
                'unitprice'=>(float)$d->unit_price,       // prorated share of the combo price
                'list_price'=>(float)$d->list_price,
                'costunitprice'=>(float)$d->cost_price,
                'has_serial'=>(int)$d->has_serial,        // ask for serial numbers for these lines
                'warranty_months'=>(int)$d->warranty_months,
                'warranty_parts'=>json_encode($parts),    // same JSON format as tbl_invoice_detail.warranty_parts
                'stock'=>$stock
            );
        }
        return array('status'=>1, 'combo_name'=>$c->combo_name, 'stock_ok'=>$ok, 'lines'=>$lines);
    }
}