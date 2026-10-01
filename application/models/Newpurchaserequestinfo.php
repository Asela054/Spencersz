<?php class Newpurchaserequestinfo extends CI_Model {
	public function Getcompany() {
		$this->db->select('`idtbl_company`, `company`');
		$this->db->from('tbl_company');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getsupplier() {

		$comapnyID=$_SESSION['company_id'];

		$this->db->select('`idtbl_supplier`, `suppliername`');
		$this->db->from('tbl_supplier');
		$this->db->where('status', 1);
		$this->db->where('tbl_supplier.tbl_company_idtbl_company', $comapnyID);

		return $respond=$this->db->get();
	}

	public function Getordertype() {
		$this->db->select('`idtbl_material_group`, `group`');
		$this->db->from('tbl_material_group');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getmeasuretype() {
		$this->db->select('`idtbl_mesurements`, `measure_type`');
		$this->db->from('tbl_measurements');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getproductlisttoselectpicker(){
        $searchTerm=$this->input->post('searchTerm');

        if(!isset($searchTerm)){
            $sql="SELECT `idtbl_print_material_info`, `materialinfocode`, `materialname` FROM `tbl_print_material_info` WHERE `status`=? LIMIT 5";
            $respond=$this->db->query($sql, array(1));                       
        }
        else{            
            if(!empty($searchTerm)){
                $sql="SELECT `idtbl_print_material_info`, `materialinfocode`, `materialname` FROM `tbl_print_material_info` WHERE `status`=? AND `materialname` LIKE '$searchTerm%'";
                $respond=$this->db->query($sql, array(1));    
            }
            else{
				$sql="SELECT `idtbl_print_material_info`, `materialinfocode`, `materialname` FROM `tbl_print_material_info` WHERE `status`=? LIMIT 5";
                $respond=$this->db->query($sql, array(1));                
            }
        }
        
        $data=array();
        
        foreach ($respond->result() as $row) {
            $data[]=array("id"=>$row->idtbl_print_material_info, "text"=>$row->materialname);
        }
        
        echo json_encode($data);
    }

	public function Getstockqty() {
		$recordID=$this->input->post('recordID');
		$companyID=$this->input->post('companyID');
		$branchID=$this->input->post('branchID');
		$sql = "SELECT SUM(`qty`) AS qty FROM `tbl_print_stock` LEFT JOIN `tbl_print_material_info` ON `tbl_print_material_info`.`idtbl_print_material_info`=`tbl_print_stock`.`tbl_print_material_info_idtbl_print_material_info` WHERE `tbl_print_stock`.`status`=1 AND `materialname`=? AND `tbl_print_stock`.`tbl_company_idtbl_company`=? AND `tbl_print_stock`.`tbl_company_branch_idtbl_company_branch`=?";
		$respond=$this->db->query($sql, array($recordID, $companyID, $branchID));
		
		if ($respond->num_rows() > 0) {
			$result = $respond->row(); 
			echo json_encode(array('qty' => $result->qty));
		} else {
			echo json_encode(array('qty' => 0));
		}
	}

	public function Getproductaccosupplier() {
		$sql = "SELECT `idtbl_print_material_info`, `materialinfocode`, `materialname` , `suppliername` FROM `tbl_print_material_info` LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier`=`tbl_print_material_info`.`tbl_supplier_idtbl_supplier` WHERE `tbl_print_material_info`.`status`=1";
		$respond = $this->db->query($sql);
		
		echo json_encode($respond->result());
	}
	
	public function Getsparepartaccosupplier() {
		$sql = "SELECT `idtbl_spareparts`, `spare_part_name` FROM `tbl_spareparts` WHERE `status`=1";
		$respond = $this->db->query($sql);
		
		echo json_encode($respond->result());
	}

	public function Getproductforvehicle() {
		$recordID=$this->input->post('recordID');
		$sql="SELECT `idtbl_service_item_list`, `service_type` FROM `tbl_service_item_list` WHERE `status`=1";
		$respond=$this->db->query($sql, array($recordID));

		echo json_encode($respond->result());
	}

	public function Getproductformachine() {
		$recordID=$this->input->post('recordID');
		$sql="SELECT `idtbl_machine`, `machine` FROM `tbl_machine` WHERE `status`=1";
		$respond=$this->db->query($sql, array($recordID));

		echo json_encode($respond->result());
	}

	public function Getproductforsparepart() {
		$recordID=$this->input->post('recordID');
		$sql="SELECT `idtbl_spareparts`, `name` FROM `tbl_spareparts` WHERE `status`=1";
		$respond=$this->db->query($sql, array($recordID));

		echo json_encode($respond->result());
	}	

	public function fetchMaterials($query, $ordertype) {
		$companyID=$_SESSION['company_id'];
		$branchID=$_SESSION['branch_id'];

		$this->db->select('TRIM(materialname) as materialname');
		$this->db->from('tbl_print_material_info');
		$this->db->like('materialname', $query);
		$this->db->where('status', 1);
		$this->db->where('tbl_material_group_idtbl_material_group', $ordertype);
		$this->db->where('tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_company_branch_idtbl_company_branch', $branchID);
		
		$printMaterialsQuery = $this->db->get();
		$printMaterials = $printMaterialsQuery->result_array();
	
		$materials = array_merge($printMaterials);
	
		return $materials;
	}

	public function Getproductprice(){
        $recordID=$this->input->post('recordID');

		$this->db->select('*'); 
        $this->db->from('tbl_print_material_info');
        $this->db->where('status', 1);
        $this->db->where('idtbl_print_material_info', $recordID);

        $respond=$this->db->get();

        if($respond->num_rows()>0){
            $obj=new stdClass();
            $obj->unitprice=$respond->row(0)->unitprice;
        }

        else{
            $obj=new stdClass(); 
            $obj->unitprice=0;
          
        }
        echo json_encode($obj);
    }

	public function Getproductpricespare(){
        $recordID=$this->input->post('recordID');

		$this->db->select('*'); 
        $this->db->from('tbl_spareparts');
        $this->db->where('status', 1);
        $this->db->where('idtbl_spareparts', $recordID);

        $respond=$this->db->get();

        if($respond->num_rows()>0){
            $obj=new stdClass();
            $obj->unitprice=$respond->row(0)->unit_price;
        }

        else{
            $obj=new stdClass(); 
            $obj->unitprice=0;
          
        }
        echo json_encode($obj);
    }

	public function Newpurchaserequestinsertupdate() {
		$this->db->trans_begin();
	
		$userID = $_SESSION['userid'];
		$companyID = $_SESSION['company_id'];
		// $recordID = $this->input->post('recordID');
	
		$tableData = $this->input->post('tableData');
		$company_id = $this->input->post('company_id');
		$branch_id = $this->input->post('branch_id');
		$ordertype = $this->input->post('ordertype');
		$servicetype = $this->input->post('servicetype');
		$reqdate = $this->input->post('date');
		// newexist_status is no longer a header-level field — it now comes in per row via tableData (col_6)
	
		$recordOption = $this->input->post('recordOption');
		$updatedatetime = date('Y-m-d H:i:s');
	
		if ($recordOption == 1) {
			$data = array(
				'date' => $reqdate,
				'confirmstatus' => '0',
				'status' => '1',
				'porderconfirm' => '0',
				'insertdatetime' => $updatedatetime,
				'tbl_user_idtbl_user' => $userID,
				'tbl_material_group_idtbl_material_group' => $ordertype,
				'tbl_company_idtbl_company' => $company_id,
				'tbl_company_branch_idtbl_company_branch' => $branch_id
			);
	
			$this->db->insert('tbl_print_porder_req', $data);
			$porderID = $this->db->insert_id();
	
			foreach ($tableData as $rowtabledata) {
				$materialname = $rowtabledata['col_1'];
				$qty = $rowtabledata['col_2'];
				$uom = $rowtabledata['col_4'];
				$rownewexist = $rowtabledata['col_6']; // '1' New / '0' Exist, per line item
				$comment = $rowtabledata['col_7'];
	
				$dataone = array(
					'qty' => $qty,
					'comment' => $comment,
					'tbl_measurements_idtbl_measurements' => $uom,
					'newexist_status' => $rownewexist,
					'status' => '1',
					'insertdatetime' => $updatedatetime,
					'tbl_print_porder_req_idtbl_print_porder_req' => $porderID,
					'requestname' => $materialname,
					'tbl_user_idtbl_user' => $userID,
				);
	
				$this->db->insert('tbl_print_porder_req_detail', $dataone);
			}

			// Generate the PO REQ NO
		
			$currentYear = date("Y", strtotime($reqdate));
			$currentMonth = date("m", strtotime($reqdate));
		
			if ($currentMonth < 4) { //03
				$startDate = $currentYear."-04-01";
				$startDate = date('Y-m-d',  strtotime($startDate.'-1 year'));
				$endDate = $currentYear."-03-31";
			} else {
				$startDate = $currentYear."-04-01";
				$endDate = $currentYear."-03-31";
				$endDate = date('Y-m-d',  strtotime($endDate.'+1 year'));
			}
		
			$fromyear = date("Y-m-d", strtotime($startDate));
			$toyear = date("Y-m-d", strtotime($endDate));

			$this->db->select('porder_req_no');
			$this->db->from('tbl_print_porder_req');
			$this->db->where('tbl_company_idtbl_company', $companyID);
			$this->db->where("DATE(insertdatetime) >=", $fromyear);
			$this->db->where("DATE(insertdatetime) <=", $toyear);	
			$this->db->order_by('porder_req_no', 'DESC');
			$this->db->limit(1);
			$respond = $this->db->get();
			
			if ($respond->num_rows() > 0) {
				$last_req_no = $respond->row()->porder_req_no;
				$req_number = intval(substr($last_req_no, -4));
				$count = $req_number;
			} else {
				$count = 0;
			}

			$count++; 
            $countPrefix = sprintf('%04d', $count);

            $yearDigit = substr(date("Y", strtotime($fromyear)), -2);

			$reqno = 'POR' . $yearDigit . $countPrefix;

			$datadetail = array(
				'porder_req_no'=> $reqno, 
				'updatedatetime'=> $updatedatetime
			);
	
			$this->db->where('idtbl_print_porder_req', $porderID);
			$this->db->update('tbl_print_porder_req', $datadetail);
	
			if ($this->db->trans_status()===TRUE) {
				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-save';
				$actionObj->title='';
				$actionObj->message='Record Added Successfully';
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
				$actionObj->icon='fas fa-exclamation-triangle';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$obj=new stdClass();
				$obj->status=0;
				$obj->action=$actionJSON;

				echo json_encode($obj);
			}
		}
	}
	

	public function Purchaseorderview() {
		$recordID=$this->input->post('recordID');

		$this->db->select('tbl_print_porder_req_detail.*,tbl_print_porder_req.tbl_material_group_idtbl_material_group,tbl_measurements.measure_type ');
		$this->db->from('tbl_print_porder_req_detail');
		$this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_porder_req_detail.tbl_measurements_idtbl_measurements', 'left');
		$this->db->join('tbl_print_porder_req', 'tbl_print_porder_req.idtbl_print_porder_req = tbl_print_porder_req_detail.tbl_print_porder_req_idtbl_print_porder_req', 'left');
		$this->db->where('tbl_print_porder_req_detail.tbl_print_porder_req_idtbl_print_porder_req', $recordID);
		$this->db->where('tbl_print_porder_req_detail.status', 1);

		$responddetail=$this->db->get();

		$html='';

		$html.='
        <div class="row">
        </div>
		<div class="row">
		<div class="col-12">
		<hr>
		<table class="table table-striped table-bordered table-sm">
		<thead>
		<th class="bg-primary text-light">Request Item</th>
		<th class="bg-primary text-light">Qty</th>
		<th class="bg-primary text-light">Uom</th>
		<th class="bg-primary text-light text-center">Status</th>
		<th class="bg-primary text-light text-center">Comment</th>
		</thead>
		<tbody>';
        foreach($responddetail->result() as $roworderinfo) {
				$newexistLabel = ($roworderinfo->newexist_status == 1 || $roworderinfo->newexist_status == '1') ? 'New' : 'Exist';
				$html.='<tr>
        <td>'.$roworderinfo->requestname.'</td><td>'.$roworderinfo->qty.'</td><td>'.$roworderinfo->measure_type.'</td><td class="text-center">'.$newexistLabel.'</td><td class="text-center">'.$roworderinfo->comment.'</td></tr>';
		}

		$html.='</tbody>
        </table></div></div>';

		echo $html;
	}

	public function porderviewheader() {
		$recordID=$this->input->post('recordID');

		$this->db->select('tbl_print_porder_req.*,
								tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
                                tbl_company.phone companyphone,tbl_company.email AS companyemail,
                                tbl_company_branch.branch AS branchname');
		$this->db->from('tbl_print_porder_req');
		$this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_print_porder_req.tbl_company_idtbl_company', 'left');
		$this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_print_porder_req.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->where('idtbl_print_porder_req', $recordID);
		$this->db->where('tbl_print_porder_req.status', 1);

		$respond=$this->db->get();

		$obj=new stdClass();
		$obj->porder_no=$respond->row(0)->porder_req_no;
		$obj->companyname=$respond->row(0)->companyname;
		$obj->companyaddress=$respond->row(0)->companyaddress;
		$obj->companymobile=$respond->row(0)->companymobile;
		$obj->companyphone=$respond->row(0)->companyphone;
		$obj->companyemail=$respond->row(0)->companyemail;
		$obj->branchname=$respond->row(0)->branchname;
		// newexist_status removed — it's no longer stored on the header row, see Purchaseorderview() for per-item status

		echo json_encode($obj);
	}

	public function Newpurchaserequeststatus() {
		$this->db->trans_begin();

        $recordID=$this->input->post('requestid');
		$confirmnot=$this->input->post('confirmnot');
		$userID=$_SESSION['userid'];
		$updatedatetime=date('Y-m-d H:i:s');

			$data=array(
				'confirmstatus'=> $confirmnot,
				'updateuser'=> $userID,
				'updatedatetime'=> $updatedatetime);

			$this->db->where('idtbl_print_porder_req', $recordID);
			$this->db->update('tbl_print_porder_req', $data);


			$this->db->trans_complete();

			if ($this->db->trans_status()===TRUE) {
				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-check';
				$actionObj->title='';
				if($confirmnot==1){$actionObj->message='Record Approved Successfully';}
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

		public function Newpurchaserequestcheckstatus() {
		$this->db->trans_begin();

        $recordID=$this->input->post('requestid');
		$confirmnot=$this->input->post('confirmnot');
		$userID=$_SESSION['userid'];
		$updatedatetime=date('Y-m-d H:i:s');

			$data=array(
				'check_by'=> $userID);

			$this->db->where('idtbl_print_porder_req', $recordID);
			$this->db->update('tbl_print_porder_req', $data);


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