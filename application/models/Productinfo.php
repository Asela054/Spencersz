<?php
class Productinfo extends CI_Model{
    public function Generateproductcode($categoryID, $lock = FALSE){
        $prefix = $this->Getcategoryprefix($categoryID);
        $sql = "SELECT MAX(CAST(SUBSTRING(`product_code`, ?) AS UNSIGNED)) AS maxno
                FROM `tbl_product`
                WHERE `product_code` LIKE ? AND `product_code` REGEXP ?".($lock ? " FOR UPDATE" : "");
        $row = $this->db->query($sql, array(strlen($prefix) + 1, $prefix.'%', '^'.$prefix.'[0-9]+$'))->row();
        $next = ($row && $row->maxno !== NULL) ? ((int)$row->maxno + 1) : 1;
        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function Getcategoryprefix($categoryID){
        if(empty($categoryID)){ return 'GEN'; }

        $row = $this->db->select('category')
                        ->where('idtbl_category', $categoryID)
                        ->get('tbl_category')->row();
        if(!$row){ return 'GEN'; }

        // 1) prefix already used by this category's products (most used one wins)
        $used = $this->db->query("SELECT LEFT(`product_code`, 3) AS prefix, COUNT(*) AS n
                                  FROM `tbl_product`
                                  WHERE `tbl_category_idtbl_category` = ?
                                  AND `product_code` REGEXP '^[A-Z]{3}[0-9]+$'
                                  GROUP BY LEFT(`product_code`, 3)
                                  ORDER BY n DESC, prefix ASC
                                  LIMIT 1", array((int)$categoryID))->row();
        if($used && !empty($used->prefix)){ return strtoupper($used->prefix); }

        // 2) new category: build a new 3-letter prefix from the category name that no other category uses
        $words  = preg_split('/[^A-Z0-9]+/', strtoupper($row->category), -1, PREG_SPLIT_NO_EMPTY);
        $joined = implode('', $words);
        $first  = ($joined !== '') ? $joined[0] : 'X';

        $candidates = array();
        if(strlen($joined) >= 3){ $candidates[] = substr($joined, 0, 3); }
        if(count($words) >= 3){ $candidates[] = $words[0][0].$words[1][0].$words[2][0]; }
        if(count($words) >= 2){
            $candidates[] = $words[0][0].substr($words[1], 0, 2);
            $candidates[] = substr($words[0], 0, 2).$words[1][0];
        }
        for($i = 1; $i < strlen($joined); $i++){
            for($j = $i + 1; $j < strlen($joined); $j++){
                $candidates[] = $first.$joined[$i].$joined[$j];
            }
        }
        foreach(range('A', 'Z') as $a){ foreach(range('A', 'Z') as $b){ $candidates[] = $first.$a.$b; } }

        foreach(array_unique($candidates) as $c){
            if(strlen($c) != 3 || $c === 'GEN'){ continue; }
            if(!ctype_alpha($c)){ continue; }
            if($this->Prefixused($c, $categoryID)){ continue; }
            return $c;
        }
        return 'GEN';
    }

    private function Prefixused($prefix, $categoryID){
        // a prefix is taken if any product of another category (or with no category) uses it
        $p =$this->db->query("SELECT COUNT(*) AS n FROM `tbl_product`
                               WHERE `product_code` REGEXP ?
                               AND (`tbl_category_idtbl_category` IS NULL OR `tbl_category_idtbl_category` <> ?)",
                               array('^'.$prefix.'[0-9]+$', (int)$categoryID))->row();
        return ($p && $p->n > 0);
    }

    /* AJAX preview for the form (the real code is generated again when saving) */
    public function Getnextcode(){
        $categoryID = $this->input->post('category');
        echo json_encode(array('code' => $this->Generateproductcode($categoryID, FALSE)));
    }
    /* ============================================================== */

    /* Saves the extra warranties of a product.
       Old active rows are soft-deleted (status 3), then the submitted rows are inserted again. */
    private function Savewarranties($productID, $userID, $datetime){
        $names  = $this->input->post('warranty_name');
        $months = $this->input->post('warranty_part_months');

        $this->db->where('tbl_product_idtbl_product', $productID);
        $this->db->where('status', 1);
        $this->db->update('tbl_product_warranty', array(
            'status'=> '3',
            'updateuser'=> $userID,
            'updatedatetime'=> $datetime
        ));

        if(!empty($names) && is_array($names)){
            foreach($names as $i => $name){
                $name = trim($name);
                $m = isset($months[$i]) ? (int)$months[$i] : 0;
                if($name === '' || $m <= 0){ continue; }

                $this->db->insert('tbl_product_warranty', array(
                    'warranty_name'=> $name,
                    'warranty_months'=> $m,
                    'status'=> '1',
                    'insertdatetime'=> $datetime,
                    'tbl_user_idtbl_user'=> $userID,
                    'tbl_product_idtbl_product'=> $productID
                ));
            }
        }
    }

    /* Reads and checks the discount fields from the form.
       Returns an array with discount_percent, discount_start, discount_end and error (empty when valid).
       - percent must be between 0 and 100
       - when percent > 0 both dates are required and end date cannot be before start date
       - when percent is 0 the dates are stored as NULL */
    private function Getdiscountdata(){
        $percent = (float)$this->input->post('discount_percent');
        $start   = trim((string)$this->input->post('discount_start'));
        $end     = trim((string)$this->input->post('discount_end'));
        $error   = '';

        if($percent < 0 || $percent > 100){
            $error = 'Discount % must be between 0 and 100';
        }
        else if($percent > 0){
            if($start === '' || $end === ''){
                $error = 'Discount start date and end date are required';
            }
            else if($end < $start){
                $error = 'Discount end date cannot be before the start date';
            }
        }

        if($percent <= 0){
            $percent = 0;
            $start = '';
            $end = '';
        }

        return array(
            'discount_percent'=> $percent,
            'discount_start'=> ($start !== '') ? $start : NULL,
            'discount_end'=> ($end !== '') ? $end : NULL,
            'error'=> $error
        );
    }

    public function Productinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        // product_code and barcode are NOT taken from the form any more:
        // they are generated on insert and kept unchanged on update
        $product_name=$this->input->post('product_name');
        $model_no=$this->input->post('model_no');
        $description=$this->input->post('description');
        $tbl_category_idtbl_category=!empty($this->input->post('tbl_category_idtbl_category')) ? $this->input->post('tbl_category_idtbl_category') : NULL;
        $tbl_brand_idtbl_brand=!empty($this->input->post('tbl_brand_idtbl_brand')) ? $this->input->post('tbl_brand_idtbl_brand') : NULL;
        $tbl_unit_idtbl_unit=!empty($this->input->post('tbl_unit_idtbl_unit')) ? $this->input->post('tbl_unit_idtbl_unit') : NULL;
        $cost_price=!empty($this->input->post('cost_price')) ? $this->input->post('cost_price') : 0;
        $selling_price=!empty($this->input->post('selling_price')) ? $this->input->post('selling_price') : 0;
        $reorder_level=!empty($this->input->post('reorder_level')) ? $this->input->post('reorder_level') : 0;
        $has_serial=!empty($this->input->post('has_serial')) ? $this->input->post('has_serial') : 0;
        $warranty_months=!empty($this->input->post('warranty_months')) ? $this->input->post('warranty_months') : 0;

        // discount percentage, start date and end date
        $discount=$this->Getdiscountdata();
        if($discount['error'] !== ''){
            $this->db->trans_rollback();

            $actionObj=new stdClass();
            $actionObj->icon='fas fa-warning';
            $actionObj->title='';
            $actionObj->message=$discount['error'];
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='danger';

            $actionJSON=json_encode($actionObj);

            $this->session->set_flashdata('msg', $actionJSON);
            redirect('Product');
        }

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        if($recordOption==1){
            // auto product code + barcode (same value), locked inside this transaction
            $product_code = $this->Generateproductcode($tbl_category_idtbl_category, TRUE);
            $barcode = $product_code;

            $data = array(
                'product_code'=> $product_code,
                'barcode'=> $barcode,
                'product_name'=> $product_name,
                'model_no'=> $model_no,
                'description'=> $description,
                'tbl_category_idtbl_category'=> $tbl_category_idtbl_category,
                'tbl_brand_idtbl_brand'=> $tbl_brand_idtbl_brand,
                'tbl_unit_idtbl_unit'=> $tbl_unit_idtbl_unit,
                'cost_price'=> $cost_price,
                'selling_price'=> $selling_price,
                'discount_percent'=> $discount['discount_percent'],
                'discount_start'=> $discount['discount_start'],
                'discount_end'=> $discount['discount_end'],
                'reorder_level'=> $reorder_level,
                'has_serial'=> $has_serial,
                'warranty_months'=> $warranty_months,
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
            );

            $this->db->insert('tbl_product', $data);
            $newProductID = $this->db->insert_id();

            // extra warranties (compressor, motor ...)
            $this->Savewarranties($newProductID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Added Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            }
        }
        else{
            // product_code and barcode stay as they are (printed labels keep working)
            $data = array(
                'product_name'=> $product_name,
                'model_no'=> $model_no,
                'description'=> $description,
                'tbl_category_idtbl_category'=> $tbl_category_idtbl_category,
                'tbl_brand_idtbl_brand'=> $tbl_brand_idtbl_brand,
                'tbl_unit_idtbl_unit'=> $tbl_unit_idtbl_unit,
                'cost_price'=> $cost_price,
                'selling_price'=> $selling_price,
                'discount_percent'=> $discount['discount_percent'],
                'discount_start'=> $discount['discount_start'],
                'discount_end'=> $discount['discount_end'],
                'reorder_level'=> $reorder_level,
                'has_serial'=> $has_serial,
                'warranty_months'=> $warranty_months,
                'status'=> '1',
                'updatedatetime'=> $insertdatetime,
                'updateuser'=> $userID,
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

            // extra warranties (compressor, motor ...)
            $this->Savewarranties($recordID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-save';
                $actionObj->title='';
                $actionObj->message='Record Update Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='primary';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            }
        }
    }

    public function Productstatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $data = array(
                'status' => '1',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Record Activate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            }
        }
        else if($type==2){
            $data = array(
                'status' => '2',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-times';
                $actionObj->title='';
                $actionObj->message='Record Deactivate Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='warning';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            }
        }
        else if($type==3){
            $data = array(
                'status' => '3',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-trash-alt';
                $actionObj->title='';
                $actionObj->message='Record Remove Successfully';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            } else {
                $this->db->trans_rollback();

                $actionObj=new stdClass();
                $actionObj->icon='fas fa-warning';
                $actionObj->title='';
                $actionObj->message='Record Error';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='danger';

                $actionJSON=json_encode($actionObj);

                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Product');
            }
        }
    }

    public function Productedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_product');
        $this->db->where('idtbl_product', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_product;
        $obj->product_code=$respond->row(0)->product_code;
        $obj->barcode=$respond->row(0)->barcode;
        $obj->product_name=$respond->row(0)->product_name;
        $obj->model_no=$respond->row(0)->model_no;
        $obj->description=$respond->row(0)->description;
        $obj->tbl_category_idtbl_category=$respond->row(0)->tbl_category_idtbl_category;
        $obj->tbl_brand_idtbl_brand=$respond->row(0)->tbl_brand_idtbl_brand;
        $obj->tbl_unit_idtbl_unit=$respond->row(0)->tbl_unit_idtbl_unit;
        $obj->cost_price=$respond->row(0)->cost_price;
        $obj->selling_price=$respond->row(0)->selling_price;
        $obj->discount_percent=$respond->row(0)->discount_percent;
        $obj->discount_start=!empty($respond->row(0)->discount_start) ? $respond->row(0)->discount_start : '';
        $obj->discount_end=!empty($respond->row(0)->discount_end) ? $respond->row(0)->discount_end : '';
        $obj->reorder_level=$respond->row(0)->reorder_level;
        $obj->has_serial=$respond->row(0)->has_serial;
        $obj->warranty_months=$respond->row(0)->warranty_months;

        // extra warranties
        $this->db->select('warranty_name, warranty_months');
        $this->db->from('tbl_product_warranty');
        $this->db->where('tbl_product_idtbl_product', $recordID);
        $this->db->where('status', 1);
        $this->db->order_by('idtbl_product_warranty', 'ASC');
        $obj->warranties=$this->db->get()->result();

        echo json_encode($obj);
    }

    public function Getcategorylist(){
        $this->db->select('idtbl_category, category');
        $this->db->from('tbl_category');
        $this->db->where('status', 1);
        $this->db->order_by('category', 'ASC');
        return $this->db->get()->result();
    }
    public function Getbrandlist(){
        $this->db->select('idtbl_brand, brand');
        $this->db->from('tbl_brand');
        $this->db->where('status', 1);
        $this->db->order_by('brand', 'ASC');
        return $this->db->get()->result();
    }
    public function Getunitlist(){
        $this->db->select('idtbl_unit, unit');
        $this->db->from('tbl_unit');
        $this->db->where('status', 1);
        $this->db->order_by('unit', 'ASC');
        return $this->db->get()->result();
    }
}