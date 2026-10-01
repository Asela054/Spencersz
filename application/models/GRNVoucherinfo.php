<?php
use Dompdf\Dompdf;
use Dompdf\Options;
class GRNVoucherinfo extends CI_Model{
    public function Getcostlisttype() {
        $this->db->select('`idtbl_import_cost_types`, `cost_type`');
        $this->db->from('tbl_import_cost_types');
        $this->db->where('status', 1);
    
        return $respond=$this->db->get();
    }
    public function Getsupplierlist() {
        $this->db->select('idtbl_supplier, suppliername');
        $this->db->from('tbl_supplier');
        $this->db->where('status', 1);
    
        return $this->db->get();
    }    
    public function get_grn_details($grnno) {
        // $this->db->select('tbl_print_grndetail.*, tbl_print_material_info.idtbl_print_material_info, tbl_print_material_info.materialinfocode, tbl_print_material_info.materialname, tbl_measurements.measure_type');
        $this->db->select('tbl_print_grndetail.*, tbl_print_material_info.idtbl_print_material_info, tbl_print_material_info.materialinfocode, tbl_print_material_info.materialname,
        CASE 
            WHEN tbl_print_grndetail.pieces > 0 THEN tbl_measurements2.idtbl_mesurements 
            ELSE tbl_measurements.idtbl_mesurements 
        END AS idtbl_mesurements,
        CASE 
            WHEN tbl_print_grndetail.pieces > 0 THEN tbl_measurements2.measure_type 
            ELSE tbl_measurements.measure_type 
        END AS measure_type', false);
		$this->db->from('tbl_print_grndetail');
        $this->db->join('tbl_print_material_info', 'tbl_print_material_info.idtbl_print_material_info = tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info', 'left');
		$this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail.tbl_print_grn_idtbl_print_grn', 'left');
		// $this->db->join('tbl_machine', 'tbl_machine.idtbl_machine = tbl_print_grndetail.tbl_machine_id', 'left');
		$this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_grndetail.tbl_measurements_idtbl_mesurements', 'left');
		$this->db->join('tbl_uom_conversions', 'tbl_uom_conversions.main_uom = tbl_print_grndetail.tbl_measurements_idtbl_mesurements', 'left');
		$this->db->join('tbl_measurements as tbl_measurements2', 'tbl_measurements2.idtbl_mesurements = tbl_uom_conversions.convert_uom', 'left');
        $this->db->where('tbl_print_grn_idtbl_print_grn', $grnno);
        $this->db->where('tbl_print_grndetail.status', 1);
        $query = $this->db->get();

        $this->db->select('`idtbl_print_grn`, `grn_no`, `vatamount`, `total`, `discount`, `subtotal`, `vat`');
        $this->db->from('tbl_print_grn');
        $this->db->where('idtbl_print_grn', $grnno);
        $this->db->where('status', 1);
    
        $respond=$this->db->get();
        
        if ($query->num_rows() > 0) {
            $obj=new stdClass();
            $obj->idtbl_print_grn=$respond->row(0)->idtbl_print_grn;
            $obj->grn_no=$respond->row(0)->grn_no;
            $obj->vatamount=$respond->row(0)->vatamount;
            $obj->total=$respond->row(0)->total;
            $obj->discount=$respond->row(0)->discount;
            $obj->subtotal=$respond->row(0)->subtotal;
            $obj->vat=$respond->row(0)->vat;
            $obj->detailinfo=$query->result_array();

            return $obj;
        } else {
            return false;
        }
    } 
    public function Insertgrnvoucher() {
        $this->db->trans_begin();
    
        $userID = $_SESSION['userid'];
        $grnDetails = $this->input->post('grnDetails');
        $costDetails = $this->input->post('costDetails');
        $grndate = $this->input->post('date');
        $grnsubtotal = $this->input->post('grnsubtotal');
        $grndiscount = $this->input->post('grndiscount');
        $grnvatamount = str_replace(',', '', $this->input->post('grnvatamount'));
        $hidetotalorder = $this->input->post('totalGRN');
        $hidechargestotal = $this->input->post('totalCost');
        $remark = $this->input->post('remark');
        $grnno = $this->input->post('grnno');
        $invoiceno = $this->input->post('invoiceno');
        $company_id=$_SESSION['company_id'];
		$branch_id=$_SESSION['branch_id'];
    
        $updatedatetime = date('Y-m-d H:i:s');
    
        // Insert the GRN voucher
        $data = array(
            'date' => $grndate,
            'total' => $hidechargestotal,
            'grnsubtotal' => $grnsubtotal,
            'grndiscount' => $grndiscount,
            'grnvatamount' => $grnvatamount,
            'grntotal' => $hidetotalorder,
            'remarks' => $remark,
            'invoiceno' => $invoiceno,
            'approvestatus' => '0',
            'status' => '1',
            'insertdatetime' => $updatedatetime,
            'tbl_user_idtbl_user' => $userID,
            'tbl_print_grn_idtbl_print_grn' => $grnno,
            'tbl_company_idtbl_company'=> $company_id, 
			'tbl_company_branch_idtbl_company_branch'=> $branch_id
        );
    
        $this->db->insert('tbl_grn_vouchar_import_cost', $data);
        $grnvoucherID = $this->db->insert_id();

        $this->db->select('grntype');
        $this->db->from('tbl_print_grn');
        $this->db->where('idtbl_print_grn', $grnno);
        $this->db->where('status', 1);
        $respondgrn=$this->db->get();
    
        // Insert the GRN details
        foreach ($grnDetails as $grn) {
            if($respondgrn->row(0)->grntype==4){$grndetailID=$grn['grndetailD'];}
            else{$grndetailID=0;}
            $data = array(
                'date' => $grndate,
                'qty' => $grn['qty'],
                'costunitprice' => str_replace(",", "", $grn['unitprice']),
                'total' => str_replace(",", "", $grn['total']),
                'comment' => $grn['comment'],
                'measure_type_id' => $grn['measureID'],
                'status' => '1',
                'insertdatetime' => $updatedatetime,
                'tbl_user_idtbl_user' => $userID,
                'tbl_print_grn_idtbl_print_grn' => $grnno,
                'tbl_print_material_info_idtbl_print_material_info' => $grn['idtbl_print_material_info'],
                'tbl_print_grndetail_idtbl_print_grndetail' => $grndetailID
            );
    
            $this->db->insert('tbl_print_grndetail_after_costing', $data);
        }
    
        // Check if costDetails is empty
        if (!empty($costDetails)) {
            // Insert the cost details
            foreach ($costDetails as $cost) {
                $data = array(
                    'cost_amount' => $cost['chargeamount'],
                    'cost_tax' => $cost['costtax'],
                    'cost_total' => $cost['costtotal'],
                    'status' => '1',
                    'insertdatetime' => $updatedatetime,
                    'tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost' => $grnvoucherID,
                    'tbl_import_cost_types_idtbl_import_cost_types' => $cost['chargetypeid'],
                    'tbl_user_idtbl_user' => $userID,
                    'tbl_supplier_idtbl_supplier' => $cost['supplierid']
                );
    
                $this->db->insert('tbl_grn_vouchar_import_cost_detail', $data);
            }
        }

        //Good receive voucher issue
        $dataupdategrn=array(
            'voucherissue'=> '1',
            'updateuser'=> $userID,
            'updatedatetime'=> $updatedatetime
        );

        $this->db->where('idtbl_print_grn', $grnno);
        $this->db->update('tbl_print_grn', $dataupdategrn);
    
        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
    
            $actionObj = new stdClass();
            $actionObj->icon = 'fas fa-save';
            $actionObj->title = '';
            $actionObj->message = 'Record Added Successfully';
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'success';
    
            $actionJSON = json_encode($actionObj);
    
            $obj = new stdClass();
            $obj->status = 1;
            $obj->action = $actionJSON;
    
            echo json_encode($obj);
        } else {
            $this->db->trans_rollback();
    
            $actionObj = new stdClass();
            $actionObj->icon = 'fas fa-exclamation-triangle';
            $actionObj->title = '';
            $actionObj->message = 'Record Error';
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'danger';
    
            $actionJSON = json_encode($actionObj);
    
            $obj = new stdClass();
            $obj->status = 0;
            $obj->action = $actionJSON;
    
            echo json_encode($obj);
        }
    }
    public function Goodreceivevoucherview() {
        $recordID=$this->input->post('recordID');

        // $this->db->select("tbl_grn_vouchar_import_cost.*, `tbl_supplier`.`suppliername`, `tbl_supplier`.`telephone_no`, CONCAT(`tbl_supplier`.`address_line1`, ' ', `tbl_supplier`.`address_line2`, ' ', `tbl_supplier`.`city`) AS `address`, `tbl_company`.`company`, `tbl_company_branch`.`branch`, CONCAT(`tbl_company`.`address1`, ' ', `tbl_company`.`address2`) AS `companyaddress`, `tbl_print_grn`.`subtotalcost`, `tbl_print_grn`.`discountcost`, `tbl_print_grn`.`vatamountcost`, `tbl_print_grn`.`totalcost`");
        $this->db->select("tbl_grn_vouchar_import_cost.*, `tbl_supplier`.`suppliername`, `tbl_supplier`.`telephone_no`, CONCAT(`tbl_supplier`.`address_line1`, ' ', `tbl_supplier`.`address_line2`, ' ', `tbl_supplier`.`city`) AS `address`, `tbl_company`.`company`, `tbl_company_branch`.`branch`, CONCAT(`tbl_company`.`address1`, ' ', `tbl_company`.`address2`) AS `companyaddress`, `tbl_print_grn`.`subtotalcost`, `tbl_print_grn`.`discountcost`, `tbl_print_grn`.`vatamountcost`, `tbl_print_grn`.`totalcost`, `tbl_print_grn`.`grntype`");
        $this->db->from('tbl_grn_vouchar_import_cost');
        $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn', 'left');
        $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn_vouchar_import_cost.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn_vouchar_import_cost.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
        $this->db->where('tbl_grn_vouchar_import_cost.status', 1);
    
        $respond=$this->db->get();
    
        $this->db->select('tbl_grn_vouchar_import_cost_detail.*, tbl_import_cost_types.cost_type');
        $this->db->from('tbl_grn_vouchar_import_cost_detail');
        $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost = tbl_grn_vouchar_import_cost_detail.tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', 'left');
        $this->db->join('tbl_import_cost_types', 'tbl_import_cost_types.idtbl_import_cost_types = tbl_grn_vouchar_import_cost_detail.tbl_import_cost_types_idtbl_import_cost_types', 'left');
        $this->db->where('tbl_grn_vouchar_import_cost_detail.tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', $recordID);
        $this->db->where('tbl_grn_vouchar_import_cost_detail.status', 1);
    
        $responddetail=$this->db->get();

        $grnID=$respond->row(0)->tbl_print_grn_idtbl_print_grn;

        if($respond->row(0)->grntype==4){
            $this->db->select('tbl_print_grndetail_after_costing.costunitprice,tbl_print_grndetail_after_costing.qty,tbl_print_grndetail_after_costing.total,tbl_print_grn.grndate,tbl_print_grn.grn_no,tbl_print_grn.tbl_material_group_idtbl_material_group, tbl_print_grndetail.comment AS `material_display`,tbl_measurements.measure_type, tbl_print_grndetail.unitprice, tbl_print_grndetail.unit_discount');
            $this->db->from('tbl_print_grndetail_after_costing');

            $this->db->join('tbl_print_grndetail', 'tbl_print_grndetail.idtbl_print_grndetail = tbl_print_grndetail_after_costing.tbl_print_grndetail_idtbl_print_grndetail AND tbl_print_grndetail.tbl_print_grn_idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');

            $this->db->join('tbl_print_material_info', 'tbl_print_material_info.idtbl_print_material_info = tbl_print_grndetail_after_costing.tbl_print_material_info_idtbl_print_material_info', 'left');
            $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');
            $this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_grndetail_after_costing.measure_type_id', 'left');
            $this->db->where('tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', $grnID);
            $this->db->where('tbl_print_grndetail_after_costing.status', 1);
        }
        else{
            $this->db->select('tbl_print_grndetail_after_costing.costunitprice,tbl_print_grndetail_after_costing.qty,tbl_print_grndetail_after_costing.total,tbl_print_grn.grndate,tbl_print_grn.grn_no,tbl_print_grn.tbl_material_group_idtbl_material_group, CONCAT(`tbl_print_material_info`.`materialinfocode`, " / ", `tbl_print_material_info`.`materialname`) AS `material_display`,tbl_measurements.measure_type, tbl_print_grndetail.unitprice, tbl_print_grndetail.unit_discount');
            $this->db->from('tbl_print_grndetail_after_costing');

            $this->db->join('tbl_print_grndetail', 'tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info = tbl_print_grndetail_after_costing.tbl_print_material_info_idtbl_print_material_info AND tbl_print_grndetail.tbl_print_grn_idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');

            $this->db->join('tbl_print_material_info', 'tbl_print_material_info.idtbl_print_material_info = tbl_print_grndetail_after_costing.tbl_print_material_info_idtbl_print_material_info', 'left');
            $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');
            $this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_grndetail_after_costing.measure_type_id', 'left');
            $this->db->where('tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', $grnID);
            $this->db->where('tbl_print_grndetail_after_costing.status', 1);
        }

		$respondgrndetail=$this->db->get();

        $html='';
    
        $html.='
        <div class="row">
            <div class="col-6 small"><label class="small font-weight-bold text-dark mb-1">Date:</label> '.$respond->row(0)->date.'<br><label class="small font-weight-bold text-dark mb-1">Company:</label> '.$respond->row(0)->company.'<br><label class="small font-weight-bold text-dark mb-1">Branch:</label> '.$respond->row(0)->branch.'</div>
            <div class="col-6 small"><label class="small font-weight-bold text-dark mb-1">Supplier:</label> '.$respond->row(0)->suppliername.'<br><label class="small font-weight-bold text-dark mb-1">Phone:</label> '.$respond->row(0)->telephone_no.'<br><label class="small font-weight-bold text-dark mb-1">Address:</label> '.$respond->row(0)->address.'</div>
        </div>
        <h6 class="title-style small my-2 font-weight-bold"><span>GRN Information</span></h6>
        <table class="table table-striped table-bordered table-sm small"> 
            <thead> 
                <tr> 
                    <th>Material Info</th> 
                    <th>Unit Price</th> 
                    <th class="text-center">Qty</th>
                    <th class="text-center">Uom</th> 
                    <th class="text-center">Discount</th> 
                    <th class="text-right">Total</th> 
                </tr> 
            </thead> 
            <tbody>';
		    foreach($respondgrndetail->result() as $rowgrninfo) {
                $total=number_format(($rowgrninfo->qty*$rowgrninfo->unitprice), 2);
                $html .= '<tr>
                    <td>' . $rowgrninfo->material_display . '</td>
                    <td>' . (!empty($rowgrninfo->costunitprice) 
                        ? number_format($rowgrninfo->costunitprice, 2, '.', ',') 
                        : number_format($rowgrninfo->unitprice, 2, '.', ',')) . '</td>
                    <td class="text-center">' . $rowgrninfo->qty . '</td>
                    <td class="text-center">' . $rowgrninfo->measure_type . '</td>
                    <td class="text-center">' . $rowgrninfo->unit_discount . '</td>
                    <td class="text-right">' . number_format($rowgrninfo->total, 2, '.', ',') . '</td>
                </tr>';					
		    }
		    $html .= '</tbody>
		</table>
        <h6 class="title-style small my-2 font-weight-bold"><span>Other Cost Information</span></h6>
        <table class="table table-striped table-bordered table-sm small">
            <thead>
                <tr>
                    <th>Cost Type</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>';
            foreach($responddetail->result() as $roworderinfo) {
                $html.='
                <tr>
                    <td>'.$roworderinfo->cost_type.'</td>
                    <td class="text-right">'.number_format($roworderinfo->cost_amount, 2).'</td>
                </tr>
                ';
            }
            $html.='</tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">'.number_format($respond->row(0)->total, 2).'</th>
                </tr>
            </tfoot>
        </table>
        <div class="row">
            <div class="col-9 text-right small font-weight-bold">Discount</div>
            <div class="col-3 text-right small">'.number_format($respond->row(0)->grndiscount, 2).'</div>
        </div>
        <div class="row">
            <div class="col-9 text-right small font-weight-bold">Sub Total</div>
            <div class="col-3 text-right small">'.number_format($respond->row(0)->grnsubtotal, 2).'</div>
        </div>
        <div class="row">
            <div class="col-9 text-right small font-weight-bold">Vat</div>
            <div class="col-3 text-right small">'.number_format($respond->row(0)->grnvatamount, 2).'</div>
        </div>
        <div class="row">
            <div class="col-9 text-right font-weight-bold">Net Total</div>
            <div class="col-3 text-right font-weight-bold">'.number_format($respond->row(0)->grntotal, 2).'</div>
        </div>        
        ';
    
        echo $html;
    }
    public function Getgrnaccsupllier() {
        $searchTerm = $this->input->post('searchTerm');
        $companyid=$_SESSION['company_id'];
        $branchid=$_SESSION['branch_id'];

        if(!isset($searchTerm)){        
            $this->db->select('`tbl_print_grn`.`idtbl_print_grn`, `tbl_print_grn`.`grn_no`, `tbl_print_grn`.`invoicenum`, `tbl_supplier`.`suppliername`');
            $this->db->from('tbl_print_grn');
            $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
            // $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn = tbl_print_grn.idtbl_print_grn', 'left');
            $this->db->where('tbl_print_grn.status', 1);
            $this->db->where('tbl_print_grn.approvestatus', 1);
            // $this->db->where('tbl_grn_vouchar_import_cost.approvestatus', 0);
            $this->db->where('tbl_print_grn.voucherissue', 0);
            $this->db->where('tbl_print_grn.tbl_company_idtbl_company', $companyid);
            $this->db->where('tbl_print_grn.tbl_company_branch_idtbl_company_branch', $branchid);
            $this->db->limit(5); 
            $respond = $this->db->get();
		}
		else{            
            if(!empty($searchTerm)){
				$this->db->select('`tbl_print_grn`.`idtbl_print_grn`, `tbl_print_grn`.`grn_no`, `tbl_print_grn`.`invoicenum`, `tbl_supplier`.`suppliername`');
                $this->db->from('tbl_print_grn');
                $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
                // $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn = tbl_print_grn.idtbl_print_grn', 'left');
                $this->db->where('tbl_print_grn.status', 1);
                $this->db->where('tbl_print_grn.approvestatus', 1);
                // $this->db->where('tbl_grn_vouchar_import_cost.approvestatus', 0);
                $this->db->where('tbl_print_grn.voucherissue', 0);
                $this->db->where('tbl_print_grn.tbl_company_idtbl_company', $companyid);
                $this->db->where('tbl_print_grn.tbl_company_branch_idtbl_company_branch', $branchid);
                $this->db->like('`tbl_print_grn`.`grn_no`', $searchTerm, 'both'); 
                $respond = $this->db->get();
			}
			else{
				$this->db->select('`tbl_print_grn`.`idtbl_print_grn`, `tbl_print_grn`.`grn_no`, `tbl_print_grn`.`invoicenum`, `tbl_supplier`.`suppliername`');
                $this->db->from('tbl_print_grn');
                $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
                // $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn = tbl_print_grn.idtbl_print_grn', 'left');
                $this->db->where('tbl_print_grn.status', 1);
                $this->db->where('tbl_print_grn.approvestatus', 1);
                // $this->db->where('tbl_grn_vouchar_import_cost.approvestatus', 0);
                $this->db->where('tbl_print_grn.voucherissue', 0);
                $this->db->where('tbl_print_grn.tbl_company_idtbl_company', $companyid);
                $this->db->where('tbl_print_grn.tbl_company_branch_idtbl_company_branch', $branchid);
                $this->db->limit(5); 
                $respond = $this->db->get();
			}
		}
    
        $data=array();
        
        foreach ($respond->result() as $row) {
            $data[]=array("id"=>$row->idtbl_print_grn, "text"=>$row->grn_no, "suppname" => $row->suppliername, "invoicenum" => $row->invoicenum);
        }
        
        echo json_encode($data);
    }
    public function Goodreceivevoucherstatus($x, $y) {
        $userID=$_SESSION['userid'];
        $company=$_SESSION['company_id'];
        $branch=$_SESSION['branch_id'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');
    
        if($type==3) {
            $this->db->select('`tbl_print_grn_idtbl_print_grn`');
			$this->db->from('tbl_grn_vouchar_import_cost');
			$this->db->where('idtbl_grn_vouchar_import_cost', $recordID);

			$respondgrn=$this->db->get();

            $data=array(
                'status'=> '3',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime);
    
            $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
            $this->db->update('tbl_grn_vouchar_import_cost', $data);

            $datadetail=array(
                'status'=> '3',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime);
    
            $this->db->where('tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', $recordID);
            $this->db->update('tbl_grn_vouchar_import_cost_detail', $datadetail);

            $datadetailafter=array(
                'status'=> '3',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime);
    
            $this->db->where('tbl_print_grn_idtbl_print_grn', $respondgrn->row(0)->tbl_print_grn_idtbl_print_grn);
            $this->db->update('tbl_print_grndetail_after_costing', $datadetailafter);

            //Good receive not voucher issue
            $dataupdategrn=array(
                'voucherissue'=> '0',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_print_grn', $respondgrn->row(0)->tbl_print_grn_idtbl_print_grn);
            $this->db->update('tbl_print_grn', $dataupdategrn);
            
            $this->db->trans_complete();
    
            if ($this->db->trans_status()===TRUE) {
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
                redirect('GRNVoucher');
            }
    
            else {
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
                redirect('GRNVoucher');
            }
        }
    }
    // public function GRNVoucherapprove(){
    //     $this->db->trans_begin();
    //     $userID=$_SESSION['userid'];
    //     $company=$_SESSION['company_id'];
    //     $branch=$_SESSION['branch_id'];
    //     $recordID=$this->input->post('recordID');
    //     $confirmnot=$this->input->post('confirmnot');
    //     $updatedatetime=date('Y-m-d H:i:s');

    //     if($confirmnot==1){

    //         // Other cost approve
    //         $data=array(
    //             'approvestatus'=> $confirmnot,
    //             'updateuser'=> $userID,
    //             'updatedatetime'=> $updatedatetime
    //         );

    //         $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
    //         $this->db->update('tbl_grn_vouchar_import_cost', $data);
            
    //         // Update grn unit price with other cost
    //         $this->db->select('`tbl_print_grndetail_after_costing`.`costunitprice`, `tbl_print_grndetail_after_costing`.`total`, `tbl_print_grndetail_after_costing`.`tbl_print_material_info_idtbl_print_material_info`, `tbl_print_grndetail_after_costing`.`tbl_print_grn_idtbl_print_grn`');
    //         $this->db->from('tbl_print_grndetail_after_costing');
    //         $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');
    //         $this->db->where('tbl_print_grndetail_after_costing.status', 1);
    //         $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);

    //         $respond=$this->db->get();

    //         $newnettotal=0;

    //         foreach($respond->result() as $rowaftercostdata){
    //             $dataupdategrndetail=array(
    //                 'costunitprice'=> $rowaftercostdata->costunitprice,
    //                 'total'=> $rowaftercostdata->total,
    //                 'updateuser'=> $userID,
    //                 'updatedatetime'=> $updatedatetime
    //             );
    //             $this->db->where('tbl_print_grn_idtbl_print_grn', $rowaftercostdata->tbl_print_grn_idtbl_print_grn);
    //             $this->db->where('tbl_print_material_info_idtbl_print_material_info', $rowaftercostdata->tbl_print_material_info_idtbl_print_material_info);
    //             $this->db->update('tbl_print_grndetail', $dataupdategrndetail);

    //             $newnettotal+=$rowaftercostdata->total;
    //         }

    //         $this->db->select('`tbl_print_grn`.`vat`, `tbl_print_grn`.`idtbl_print_grn`, `tbl_print_grn`.`grn_no`, `tbl_print_grn`.`grntype`, `tbl_print_grn`.`grndate`, `tbl_print_grn`.`tbl_supplier_idtbl_supplier`, `tbl_print_grn`.`vatamount`, `tbl_print_grn`.`total`, `tbl_supplier`.`suppliername`, `tbl_grn_vouchar_import_cost`.`grnsubtotal`, `tbl_grn_vouchar_import_cost`.`grndiscount`, `tbl_grn_vouchar_import_cost`.`grnvatamount`, `tbl_grn_vouchar_import_cost`.`grntotal`');
    //         $this->db->from('tbl_grn_vouchar_import_cost');
    //         $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn', 'left');
    //         $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
    //         $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);

    //         $respondgrn=$this->db->get();
            
    //         // $newvattotal=($newnettotal*$respondgrn->row(0)->vat)/100;
    //         // $withvattotal=$newnettotal+$newvattotal;

    //         $dataupdategrn=array(
    //             'subtotalcost'=> $respondgrn->row(0)->grnsubtotal,
    //             'vatamountcost'=> $respondgrn->row(0)->grnvatamount,
    //             'discountcost'=> $respondgrn->row(0)->grndiscount,
    //             'totalcost'=> $respondgrn->row(0)->grntotal,
    //             'updateuser'=> $userID,
    //             'updatedatetime'=> $updatedatetime
    //         );

    //         $this->db->where('idtbl_print_grn', $respondgrn->row(0)->idtbl_print_grn);
    //         $this->db->update('tbl_print_grn', $dataupdategrn);

    //         // Expences detail insert
    //         if($respondgrn->row(0)->grntype==1){$expcode='SPR';}
    //         else if($respondgrn->row(0)->grntype==3){$expcode='GRN';}
    //         else if($respondgrn->row(0)->grntype==4){$expcode='MAC';}

    //         $dataexpence=array(
    //             'exptype'=> '1',
    //             'expcode'=> $expcode,
    //             'grnno'=> $respondgrn->row(0)->idtbl_print_grn,
    //             'grndate'=> $respondgrn->row(0)->grndate,
    //             'amount'=> $respondgrn->row(0)->grntotal,
    //             'invamount'=> $respondgrn->row(0)->total,
    //             'status'=> '1',
    //             'insertdatetime'=> $updatedatetime,
    //             'tbl_user_idtbl_user'=> $userID,
    //             'tbl_supplier_idtbl_supplier'=> $respondgrn->row(0)->tbl_supplier_idtbl_supplier,
    //             'tbl_company_idtbl_company'=> $company,
    //             'tbl_company_branch_idtbl_company_branch'=> $branch
    //         );
    //         $this->db->insert('tbl_expence_info', $dataexpence);

    //         // Parse to account API
    //         $narration=$respondgrn->row(0)->grn_no.' on '.$respondgrn->row(0)->grndate.' by '.$respondgrn->row(0)->suppliername;

    //         if($company==1){
    //             $chartofaccount=115;
    //             $detailaccount=0;
    //             $vatreserve=141;
    //         }

    //         $segregationdata=array();

    //         $obj=new stdClass();
    //         $obj->amount=str_replace(",", "", $newnettotal);
    //         $obj->narration=$narration;
    //         $obj->chartaccount=$chartofaccount;
    //         $obj->detailaccount=$detailaccount;

    //         array_push($segregationdata, $obj);

    //         if($respondgrn->row(0)->vatamount>0){
    //             $obj=new stdClass();
    //             $obj->amount=$respondgrn->row(0)->vatamount;
    //             $obj->narration=$narration;
    //             $obj->chartaccount=$vatreserve;
    //             $obj->detailaccount=$detailaccount;

    //             array_push($segregationdata, $obj);
    //         }

    //         $segregationdataencode=json_encode($segregationdata);
    //         $supplier=$respondgrn->row(0)->tbl_supplier_idtbl_supplier;
    //         $invoice=$respondgrn->row(0)->idtbl_print_grn;
    //         $invoiceamount=$respondgrn->row(0)->grntotal;

    //         $apiURL=$_SESSION['accountapiurl'].'Api/Payablesegregationinsertupdate';

    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $apiURL);
    //         curl_setopt($ch, CURLOPT_POST, 1);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, "userid=$userID&company=$company&branch=$branch&supplier=$supplier&invoice=$invoice&invoiceamount=$invoiceamount&segregationdata=$segregationdataencode");
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         $server_output = curl_exec($ch);
    //         $err = curl_error($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);

    //         // print_r($server_output);
            
    //         if ($this->db->trans_status() === TRUE && $httpCode == 200) {
    //             $this->db->trans_commit();

    //             $actionObj = new stdClass();
    //             $actionObj->icon = 'fas fa-save';
    //             $actionObj->title = '';
    //             $actionObj->message = 'GRN Voucher Confirmed Successfully';
    //             $actionObj->url = '';
    //             $actionObj->target = '_blank';
    //             $actionObj->type = 'success';

    //             $actionJSON = json_encode($actionObj);

    //             $obj = new stdClass();
    //             $obj->status = 1;
    //             $obj->action = $actionJSON;

    //             echo json_encode($obj);
    //         } else {
    //             $this->db->trans_rollback();

    //             $actionObj = new stdClass();
    //             $actionObj->icon = 'fas fa-exclamation-triangle';
    //             $actionObj->title = '';
    //             $actionObj->message = 'Record Error';
    //             $actionObj->url = '';
    //             $actionObj->target = '_blank';
    //             $actionObj->type = 'danger';

    //             $actionJSON = json_encode($actionObj);

    //             $obj = new stdClass();
    //             $obj->status = 0;
    //             $obj->action = $actionJSON;

    //             echo json_encode($obj);
    //         }
    //     }
    //     else{            
    //         // Other cost approve
    //         $data=array(
    //             'approvestatus'=> $confirmnot,
    //             'updateuser'=> $userID,
    //             'updatedatetime'=> $updatedatetime
    //         );

    //         $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
    //         $this->db->update('tbl_grn_vouchar_import_cost', $data);
            
    //         $this->db->trans_complete();

    //         if ($this->db->trans_status() === TRUE) {
    //             $this->db->trans_commit();
                
    //             $actionObj=new stdClass();
    //             $actionObj->icon='fas fa-times';
    //             $actionObj->title='';
    //             $actionObj->message='Record Rejected Successfully';
    //             $actionObj->url='';
    //             $actionObj->target='_blank';
    //             $actionObj->type='primary';
    
    //             $actionJSON=json_encode($actionObj);
                
    //             $obj=new stdClass();
    //             $obj->status=1;
    //             $obj->action=$actionJSON;
    
    //             echo json_encode($obj);            
    //         } else {
    //             $this->db->trans_rollback();
    
    //             $actionObj=new stdClass();
    //             $actionObj->icon='fas fa-warning';
    //             $actionObj->title='';
    //             $actionObj->message='Record Error';
    //             $actionObj->url='';
    //             $actionObj->target='_blank';
    //             $actionObj->type='danger';
    
    //             $actionJSON=json_encode($actionObj);
                
    //             $obj=new stdClass();
    //             $obj->status=0;
    //             $obj->action=$actionJSON;
    
    //             echo json_encode($obj);       
    //         }
    //     }
    // }
    public function GRNVoucherapprove() {
        $userID = $_SESSION['userid'];
        $company = $_SESSION['company_id'];
        $branch = $_SESSION['branch_id'];
        $recordID = $this->input->post('recordID');
        $confirmnot = $this->input->post('confirmnot');
        $updatedatetime = date('Y-m-d H:i:s');
    
        $obj = new stdClass();
        $actionObj = new stdClass();
    
        try {
            $this->db->trans_begin();
    
            if ($confirmnot == 1) {
                 // Get GRN details
                $this->db->select('`tbl_print_grn`.`batchno`, `tbl_print_grn`.`vat`, `tbl_print_grn`.`idtbl_print_grn`, `tbl_print_grn`.`grn_no`, `tbl_print_grn`.`grntype`, `tbl_print_grn`.`grndate`, `tbl_print_grn`.`tbl_supplier_idtbl_supplier`, `tbl_print_grn`.`vatamount`, `tbl_print_grn`.`total`, `tbl_supplier`.`suppliername`, `tbl_grn_vouchar_import_cost`.`grnsubtotal`, `tbl_grn_vouchar_import_cost`.`grndiscount`, `tbl_grn_vouchar_import_cost`.`grnvatamount`, `tbl_grn_vouchar_import_cost`.`grntotal`');
                $this->db->from('tbl_grn_vouchar_import_cost');
                $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn', 'left');
                $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
                $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
    
                $respondgrn = $this->db->get();
                $grnData = $respondgrn->row(0);
                
                // APPROVE PROCESS
                $data = array(
                    'approvestatus' => $confirmnot,
                    'approveby' => $userID,
                    'updateuser' => $userID,
                    'updatedatetime' => $updatedatetime
                );
    
                $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
                $this->db->update('tbl_grn_vouchar_import_cost', $data);
                
                // Update GRN unit price with other cost
                $this->db->select('`tbl_print_grndetail_after_costing`.`costunitprice`, `tbl_print_grndetail_after_costing`.`total`, `tbl_print_grndetail_after_costing`.`tbl_print_material_info_idtbl_print_material_info`, `tbl_print_grndetail_after_costing`.`tbl_print_grn_idtbl_print_grn`, `tbl_print_grndetail_after_costing`.`tbl_print_grndetail_idtbl_print_grndetail`');
                $this->db->from('tbl_print_grndetail_after_costing');
                $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn = tbl_print_grndetail_after_costing.tbl_print_grn_idtbl_print_grn', 'left');
                $this->db->where('tbl_print_grndetail_after_costing.status', 1);
                $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
    
                $respond = $this->db->get();
                $newnettotal = 0;
    
                foreach ($respond->result() as $rowaftercostdata) {
                    $dataupdategrndetail = array(
                        'costunitprice' => $rowaftercostdata->costunitprice,
                        'total' => $rowaftercostdata->total,
                        'updateuser' => $userID,
                        'updatedatetime' => $updatedatetime
                    );
                    $this->db->where('tbl_print_grn_idtbl_print_grn', $rowaftercostdata->tbl_print_grn_idtbl_print_grn);
                    $this->db->where('tbl_print_material_info_idtbl_print_material_info', $rowaftercostdata->tbl_print_material_info_idtbl_print_material_info);
                    if($grnData->grntype==4){
                        $this->db->where('idtbl_print_grndetail', $rowaftercostdata->tbl_print_grndetail_idtbl_print_grndetail);
                    }
                    $this->db->update('tbl_print_grndetail', $dataupdategrndetail);
    
                    $newnettotal += $rowaftercostdata->total;

                    $dataupdatestock = array(
                        'unitprice' => $rowaftercostdata->costunitprice,
                        'total' => $rowaftercostdata->total,
                        'updateuser' => $userID,
                        'updatedatetime' => $updatedatetime
                    );
                    $this->db->where('tbl_print_material_info_idtbl_print_material_info', $rowaftercostdata->tbl_print_material_info_idtbl_print_material_info);
                    $this->db->where('tbl_company_idtbl_company', $company);
                    $this->db->where('tbl_company_branch_idtbl_company_branch', $branch);
                    $this->db->where('batchno', $grnData->batchno);
                    $this->db->update('tbl_print_stock', $dataupdatestock);

                    //Update job card issue material unit price update
                    $sqlupdateissuematerial = "UPDATE tbl_jobcard_issue_meterial jim
                            INNER JOIN tbl_jobcard jc ON jc.idtbl_jobcard = jim.tbl_jobcard_idtbl_jobcard
                            SET jim.unitprice = ?, jim.updateuser = ?, jim.updatedatetime = ?
                            WHERE jim.tbl_print_material_info_idtbl_print_material_info = ?
                            AND jc.tbl_company_idtbl_company = ?
                            AND jc.tbl_company_branch_idtbl_company_branch = ?
                            AND jim.batchno = ?";

                    $this->db->query($sqlupdateissuematerial, array(
                        $rowaftercostdata->costunitprice,
                        $userID,
                        $updatedatetime,
                        $rowaftercostdata->tbl_print_material_info_idtbl_print_material_info,
                        $company,
                        $branch,
                        $grnData->batchno
                    ));
                }
    
                // Update GRN with cost details
                $dataupdategrn = array(
                    'subtotalcost' => $grnData->grnsubtotal,
                    'vatamountcost' => $grnData->grnvatamount,
                    'discountcost' => $grnData->grndiscount,
                    'totalcost' => $grnData->grntotal,
                    'updateuser' => $userID,
                    'updatedatetime' => $updatedatetime
                );
                
                $this->db->where('idtbl_print_grn', $grnData->idtbl_print_grn);
                $this->db->update('tbl_print_grn', $dataupdategrn);

                // GET API SEGREGATION DATA
                $APIstatus = $this->load->model('Apiinfo');
                $APIstatus = $this->Apiinfo->GRNApi($grnData->idtbl_print_grn);
                
                $segregationdataencode = json_encode($APIstatus);
                $supplier = $grnData->tbl_supplier_idtbl_supplier;
                $invoice = $grnData->grn_no;
                $invoicedate = $grnData->grndate;
                $invoiceamount = $grnData->grntotal;
    
                // Make API call
                $apiURL = $_SESSION['accountapiurl'].'Api/Payablesegregationinsertupdate';
                
                // Use http_build_query for safer parameter encoding
                $postData = http_build_query([
                    'userid' => $userID,
                    'company' => $company,
                    'branch' => $branch,
                    'supplier' => $supplier,
                    'invoice' => $invoice,
                    'invoicedate' => $invoicedate,
                    'invoiceamount' => $invoiceamount,
                    'segregationdata' => $segregationdataencode
                ]);

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $apiURL,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]
                ]);
                
                $server_output = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // print_r($server_output);
                // die();
                // Check both HTTP status and API response
                $apiResponse = json_decode($server_output, true);
                
                if ($httpCode != 200 || !isset($apiResponse['status']) || $apiResponse['status'] !== 'success') {
                    $errorMsg = $apiResponse['message'] ?? 'API request failed';
                    throw new Exception($errorMsg);
                }   
            } else {
                $this->db->select('tbl_print_grn_idtbl_print_grn');
                $this->db->form('tbl_grn_vouchar_import_cost');
                $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
                $respond = $this->db->get();

                // REJECT PROCESS
                $data = array(
                    'approvestatus' => $confirmnot,
                    'updateuser' => $userID,
                    'updatedatetime' => $updatedatetime
                );
    
                $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
                $this->db->update('tbl_grn_vouchar_import_cost', $data);

                //Update detail table
                $data = array(
                    'status' => '2',
                    'updateuser' => $userID,
                    'updatedatetime' => $updatedatetime
                );
    
                $this->db->where('tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', $recordID);
                $this->db->update('tbl_grn_vouchar_import_cost_detail', $data);

                //Update detail table
                $data = array(
                    'status' => '2',
                    'updateuser' => $userID,
                    'updatedatetime' => $updatedatetime
                );
    
                $this->db->where('tbl_print_grn_idtbl_print_grn', $respond->row(0)->tbl_print_grn_idtbl_print_grn);
                $this->db->update('tbl_print_grndetail_after_costing', $data);

                //Good receive not voucher issue
                $this->db->select('`tbl_print_grn_idtbl_print_grn`');
                $this->db->from('tbl_grn_vouchar_import_cost');
                $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
            
                $respond=$this->db->get();
                
                $dataupdategrn=array(
                    'voucherissue'=> '0',
                    'updateuser'=> $userID,
                    'updatedatetime'=> $updatedatetime
                );

                $this->db->where('idtbl_print_grn', $respond->row(0)->tbl_print_grn_idtbl_print_grn);
                $this->db->update('tbl_print_grn', $dataupdategrn);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }
            
            $this->db->trans_commit();
    
            $actionObj->icon = 'fas fa-check-circle';
            $actionObj->title = '';
            $actionObj->message = ($confirmnot == 1) ? 'GRN Voucher Confirmed Successfully' : 'Record Rejected Successfully';
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'success';
    
            $obj->status = 1;
            $obj->action = json_encode($actionObj);
    
        } catch (Exception $e) {
            $this->db->trans_rollback();
            
            error_log("GRNVoucherapprove Error: " . $e->getMessage());
            
            $actionObj->icon = 'fas fa-exclamation-triangle';
            $actionObj->title = '';
            $actionObj->message = 'Operation Failed: ' . $e->getMessage();
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'danger';
    
            $obj->status = 0;
            $obj->action = json_encode($actionObj);
        }
    
        echo json_encode($obj);
    }
    public function VoucherPdf($x){
        $recordID=$x;
        $insertdatetime=date('Y-m-d H:i:s');

        $this->db->select("tbl_grn_vouchar_import_cost.*, `tbl_supplier`.`suppliername`, `tbl_supplier`.`telephone_no`, CONCAT(`tbl_supplier`.`address_line1`, ' ', `tbl_supplier`.`address_line2`, ' ', `tbl_supplier`.`city`) AS `address`, `tbl_company`.`company`, `tbl_company_branch`.`branch`, CONCAT(`tbl_company`.`address1`, ' ', `tbl_company`.`address2`) AS `companyaddress`");
        $this->db->from('tbl_grn_vouchar_import_cost');
        $this->db->join('tbl_print_grn', 'tbl_print_grn.idtbl_print_grn = tbl_grn_vouchar_import_cost.tbl_print_grn_idtbl_print_grn', 'left');
        $this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_print_grn.tbl_supplier_idtbl_supplier', 'left');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn_vouchar_import_cost.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn_vouchar_import_cost.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
        $this->db->where('tbl_grn_vouchar_import_cost.status', 1);
    
        $respond=$this->db->get();

        $this->db->select('tbl_grn_vouchar_import_cost_detail.*, tbl_import_cost_types.cost_type');
        $this->db->from('tbl_grn_vouchar_import_cost_detail');
        $this->db->join('tbl_grn_vouchar_import_cost', 'tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost = tbl_grn_vouchar_import_cost_detail.tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', 'left');
        $this->db->join('tbl_import_cost_types', 'tbl_import_cost_types.idtbl_import_cost_types = tbl_grn_vouchar_import_cost_detail.tbl_import_cost_types_idtbl_import_cost_types', 'left');
        $this->db->where('tbl_grn_vouchar_import_cost_detail.tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost', $recordID);
        $this->db->where('tbl_grn_vouchar_import_cost_detail.status', 1);
    
        $responddetail=$this->db->get();

        // $this->db->select('*, COALESCE(tbl_print_grn.idtbl_print_grn, 0) AS idtbl_print_grn, COALESCE(tbl_print_grn.total, 0) AS grn_total, COALESCE(tbl_print_grn.discount, 0) AS discount, COALESCE(tbl_print_grndetail.qty, 0) AS qty, COALESCE(tbl_print_grndetail.unitprice, 0) AS unitprice');
        // $this->db->from('tbl_print_grn');
        // $this->db->join('tbl_print_grndetail', 'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail.tbl_print_grn_idtbl_print_grn', 'left');
        // $this->db->join('tbl_print_material_info', 'tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info = tbl_print_material_info.idtbl_print_material_info', 'left');
        // $this->db->join('tbl_machine', 'tbl_print_grndetail.tbl_machine_id = tbl_machine.idtbl_machine', 'left');
        // $this->db->join('tbl_supplier', 'tbl_print_grn.tbl_supplier_idtbl_supplier = tbl_supplier.idtbl_supplier', 'left');
        // $this->db->join('tbl_location', 'tbl_print_grn.tbl_location_idtbl_location = tbl_location.idtbl_location', 'left');
        // $this->db->join('tbl_order_type', 'tbl_print_grn.tbl_order_type_idtbl_order_type = tbl_order_type.idtbl_order_type', 'left');
        // $this->db->join('tbl_measurements', 'tbl_print_grndetail.tbl_measurements_idtbl_mesurements = tbl_measurements.idtbl_mesurements', 'left');
        // $this->db->where('tbl_print_grn.idtbl_print_grn' ,$recordID);
        // $query = $this->db->get();

        // if ($query->num_rows() > 0) {
        //     $company_id = $query->row(0)->tbl_company_idtbl_company;
        
        //     $prefix = 'MO';
        //     if ($company_id == 2) {
        //         $prefix = 'FT';
        //     } elseif ($company_id == 3) {
        //         $prefix = 'RM';
        //     }
        // }

        $dataArray = [];
        $count = 0;
        $section = 1;

        $totalSum = 0;
        $othercosttotal = $respond->row()->total;

        foreach ($responddetail->result() as $rowlist) {
            if ($count % 10 == 0) {
                $dataArray[$section] = [];
            }
        
            $dataArray[$section][] = [
                'costtype' => $rowlist->cost_type,
                'amount' => $rowlist->cost_amount
            ];
        
            $count++;
        
            if ($count % 10 == 0) {
                $section++;
            }
        } 

        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Goods Received Note</title>
            <style>
                @page {
                    size: 220mm 140mm;
                    margin: 5mm 5mm 5mm 5mm; /* top right bottom left */
                    font-family: Arial, sans-serif;
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.5;
                    text-align:left;
                    margin-top: 115px;
                }

                /** Define the header rules **/
                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 210px;
                }

                /** Define the footer rules **/
                footer {
                    position: fixed; 
                    bottom: 0px; 
                    left: 0px; 
                    right: 0px;
                    height: 128px;
                }
            </style>
        </head>
        <body>
        <header>
            <table style="width:100%;border-collapse: collapse;">
                <tr>
                    <td style="text-align: center;vertical-align: top;padding: 0px;font-size: 18px;font-weight: bold;">
                        <u>Good Receive Voucher</u>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table style="width:100%;border-collapse: collapse;">
                            <td width="60%" style="vertical-align: top;">
                                <p style="margin:0px;font-size: 13px;font-weight: bold;">'. $respond->row()->suppliername .'</p>
                                <p style="margin:0px;font-size: 13px;">'. $respond->row()->address .',</p>
                                <p style="margin:0px;font-size: 13px;">'. $respond->row()->telephone_no .'</p>
                            </td>
                            <td style="vertical-align: top;">
                                <p style="font-size: 15px;font-weight: bold; margin-top: 0px; margin-bottom: 0px;text-transform: uppercase;">'.$respond->row()->company.'</p>
                                <p style="margin:0px;font-size:13px;text-transform: uppercase;">' . $respond->row()->companyaddress . '</p>
                            </td>
                        </table>
                    </td>
                </tr>
            </table>
        </header>
        <footer>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;" rowspan="2">Received by</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;" rowspan="2">Approved by</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;width: 15%;padding-left: 5px;">Voucher No</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;"></td>
                    <th style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;text-align: center;" colspan="2"><i>Accounts Department</i></th>
                </tr>
                <tr>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;">Date</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;"></td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;width: 15%;padding-left: 5px;">Prepared By </td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;"></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;" rowspan="3" colspan="2">Remarks</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;" rowspan="3" colspan="2">Contact Person</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;padding-left: 5px;">Checked By</td>
                    <td style="vertical-align: top;font-size: 12px;border: 1px thin solid;"></td>
                </tr>
                <tr>
                    <td style="vertical-align: bottom;font-size: 12px;border: 1px thin solid;text-align: center;padding-top: 12px;" rowspan="2" colspan="2">
                        ...................................<br>Accountant
                    </td>
                </tr>
                <tr></tr>
            </table>
        </footer>
        ';
        foreach ($dataArray as $index => $section) {
			$html.='
            <main>
                <table style="width:100%;border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding-left: 10px;text-align:left;font-size: 12px;border: 1px thin solid;">Cost Type</th>
                            <th style="text-align:right;font-size: 12px;border: 1px thin solid;padding-right: 5px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>';
						foreach ($section as $row) {
							$html .= '<tr>
								<td style="font-size: 12px;border: 1px thin solid;padding-left: 10px;">' . htmlspecialchars($row['costtype']) . '</td>
								<td style="text-align:right;font-size: 12px;border: 1px thin solid;padding-right: 5px;">' . number_format(htmlspecialchars($row['amount']), 2) . '</td>
							</tr>';
						}
					$html.='</tbody>';
                    if ($index === count($dataArray) - 1) {}
                    else{
                        $html .= '<tfoot>
                            <tr>
                                <th style="font-size:12px;padding-right: 10px;text-align:right;">Total</th>
                                <th style="border: 1px solid #000;font-size:12px;text-align: right;padding-right: 5px;">'.number_format($othercosttotal, 2).'</th>
                            </tr>
                        </tfoot>';
                    }
                $html.='</table>
            </main>
            ';
        }   
            $html.='</body>
        </html>
        '; 
        // echo $html;
        $this->load->library('pdf');

        $options = new Options();
		$options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Goods Received Note - ". $recordID .".pdf", ["Attachment"=>0]);
    }
    public function GRNVouchercheckstatus() {
		$this->db->trans_begin();

        $recordID=$this->input->post('grnvoucherid');
		$confirmnot=$this->input->post('confirmnot');
		$userID=$_SESSION['userid'];
		$updatedatetime=date('Y-m-d H:i:s');

        $data=array(
            'checkby'=> $userID
        );

        $this->db->where('idtbl_grn_vouchar_import_cost', $recordID);
        $this->db->update('tbl_grn_vouchar_import_cost', $data);


        $this->db->trans_complete();

        if ($this->db->trans_status()===TRUE) {
            $this->db->trans_commit();

            $actionObj=new stdClass();
            $actionObj->icon='fas fa-check';
            $actionObj->title='';
            if($confirmnot==1){$actionObj->message='Record Checked Successfully';}
            else{$actionObj->message='Record Rejected Successfully';}
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='success';

            $actionJSON=json_encode($actionObj);

            $obj=new stdClass();
            $obj->status=1;
            $obj->action=$actionJSON;

            echo json_encode($obj);
        }

        else {
            $this->db->trans_rollback();

            $actionObj=new stdClass();
            $actionObj->icon='fas fa-warning';
            $actionObj->title='';
            $actionObj->message='Record Error';
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='danger';

            $actionJSON=json_encode($actionObj);

            $obj=new stdClass();
            $obj->status=2;
            $obj->action=$actionJSON;

            echo json_encode($obj);
        }
	}
}