<?php class Purchaseorderinfo extends CI_Model {
	public function Getcompany() {
		$this->db->select('`idtbl_company`, `company`');
		$this->db->from('tbl_company');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getmeasuretype() {
		$this->db->select('`idtbl_mesurements`, `measure_type`');
		$this->db->from('tbl_measurements');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getcontactperson() {
		$this->db->select('`idtbl_po_contact_person`, `contact_person`, `designation`');
		$this->db->from('tbl_po_contact_person');
		$this->db->where('status', 1);
		$this->db->order_by('contact_person', 'ASC');

		return $respond=$this->db->get();
	}

	public function Getporder() {

		$comapnyID=$_SESSION['company_id'];

		$this->db->select('`idtbl_print_porder_req`,`porder_req_no`');
		$this->db->from('tbl_print_porder_req');
		$this->db->where('status', 1);
		$this->db->where('confirmstatus', 1);
        $this->db->where('porderconfirm', 0);
		$this->db->where('tbl_print_porder_req.tbl_company_idtbl_company', $comapnyID);
		$this->db->order_by('idtbl_print_porder_req', 'DESC');


		return $respond=$this->db->get();
	}

	public function Getservicetype() {
		$this->db->select('`idtbl_service_type`, `service_name`');
		$this->db->from('tbl_service_type');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getsupplier() {

		$companyID=$_SESSION['company_id'];

		$this->db->select('`idtbl_supplier`, `suppliername`');
		$this->db->from('tbl_supplier');
		$this->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type', 'left');
		$this->db->where('tbl_supplier.status', 1);
		$this->db->where('tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);

		return $respond=$this->db->get();
	}

	public function Getordertype() {
		$this->db->select('`idtbl_material_group`, `group`');
		$this->db->from('tbl_material_group');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

    public function Getproductaccoporder(){
        $recordID=$this->input->post('recordID');

        $sql="SELECT `tbl_print_material_info`.`idtbl_print_material_info`, `tbl_print_material_info`.`materialinfocode`, `tbl_print_material_info`.`materialname` FROM `tbl_print_porder_req_detail` LEFT JOIN `tbl_print_material_info` ON `tbl_print_material_info`.`idtbl_print_material_info`=`tbl_print_porder_req_detail`.`tbl_material_id` WHERE `tbl_print_material_info`.`status`=? AND `tbl_print_porder_req_detail`.`tbl_print_porder_idtbl_print_porder`=?";
        $respond=$this->db->query($sql, array(1, $recordID));

        echo json_encode($respond->result());
    }

	public function Getproductformachine() {
		$recordID=$this->input->post('recordID');

		$sql="SELECT `tbl_machine`.`idtbl_machine`, `tbl_machine`.`machine` FROM `tbl_print_porder_req_detail` LEFT JOIN `tbl_machine` ON `tbl_machine`.`idtbl_machine` = `tbl_print_porder_req_detail`.`tbl_machine_id` WHERE `tbl_machine`.`status` = ? AND `tbl_print_porder_req_detail`.`tbl_print_porder_idtbl_print_porder` = ?";
		$respond=$this->db->query($sql, array(1, $recordID));

		echo json_encode($respond->result());
	}


    public function Getservicetyperequest() {
		$recordID=$this->input->post('recordID');

		$sql="SELECT `tbl_service_type`.`idtbl_service_type`, `tbl_service_type`.`service_name` FROM `tbl_print_porder_req_detail` LEFT JOIN `tbl_service_type` ON `tbl_service_type`.`idtbl_service_type` = `tbl_print_porder_req_detail`.`tbl_service_type_id` WHERE `tbl_service_type`.`status` = ? AND `tbl_print_porder_req_detail`.`tbl_print_porder_idtbl_print_porder` = ?";
		$respond=$this->db->query($sql, array(1, $recordID));

		echo json_encode($respond->result());
	}


    public function Getproductforsparepart() {
		$recordID=$this->input->post('recordID');

		$sql="SELECT `tbl_spareparts`.`idtbl_spareparts`, `tbl_spareparts`.`spare_part_name` FROM `tbl_print_porder_req_detail` LEFT JOIN `tbl_spareparts` ON `tbl_spareparts`.`idtbl_spareparts` = `tbl_print_porder_req_detail`.`tbl_sparepart_id` WHERE `tbl_spareparts`.`status` = ? AND `tbl_print_porder_req_detail`.`tbl_print_porder_idtbl_print_porder` = ?";
		$respond=$this->db->query($sql, array(1, $recordID));

		echo json_encode($respond->result());
	}

	public function Getproductinfoaccoproduct() {
		$recordID = $this->input->post('recordID');
		$supplier = $this->input->post('supplier');
	
		$this->db->select('unitprice');
		$this->db->from('tbl_print_material_info');
		$this->db->where('status', 1);
		$this->db->where('idtbl_print_material_info', $recordID);
		$respond = $this->db->get();
	
		$unitprice = $this->getLatestGRNUnitPrice($supplier, $recordID);
	
		$obj = new stdClass();
		if ($respond->num_rows() > 0) {
			$obj->unitprice = ($unitprice !== null) ? $unitprice : $respond->row(0)->unitprice;
		} else {
			$obj->unitprice = 0;
		}
	
		echo json_encode($obj);
	}

	public function Getproductinfosparepart() {
		$recordID = $this->input->post('recordID');
		$supplier = $this->input->post('supplier');
	
		$this->db->select('unit_price');
		$this->db->from('tbl_spareparts');
		$this->db->where('status', 1);
		$this->db->where('idtbl_spareparts', $recordID);
		$respond = $this->db->get();
	
		$unitprice = $this->getLatestGRNUnitPrice($supplier, $recordID);
	
		$obj = new stdClass();
		if ($respond->num_rows() > 0) {
			$obj->unitprice = ($unitprice !== null) ? $unitprice : $respond->row(0)->unit_price;
		} else {
			$obj->unitprice = 0;
		}
	
		echo json_encode($obj);
	}
	
	private function getLatestGRNUnitPrice($supplier, $recordID) {
		$this->db->select('grnd.unitprice');
		$this->db->from('tbl_print_grndetail grnd');
		$this->db->join('tbl_print_grn grn', 'grn.idtbl_print_grn = grnd.tbl_print_grn_idtbl_print_grn');
		$this->db->where('grn.tbl_supplier_idtbl_supplier', $supplier);
		$this->db->where('grnd.tbl_print_material_info_idtbl_print_material_info', $recordID);
		$this->db->where('grn.status', 1);
		$this->db->order_by('grn.insertdatetime', 'desc');
		$this->db->limit(1);
		$result = $this->db->get();
		return ($result->num_rows() > 0) ? $result->row(0)->unitprice : null;
	}
	
	public function Getpiecesforqty() {
		$uomID = $this->input->post('recordID');
		$productId = $this->input->post('productId');
		$qty = $this->input->post('qty');
	
		$this->db->select('qty, measure_type');
		$this->db->from('tbl_material_uom_qty');
		$this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements=tbl_material_uom_qty.measurement');
		$this->db->join('tbl_material_uom_qty_has_tbl_print_material_info', 'tbl_material_uom_qty_has_tbl_print_material_info.tbl_material_uom_qty_idtbl_material_uom_qty =tbl_material_uom_qty.idtbl_material_uom_qty');
		$this->db->where('tbl_print_material_info_idtbl_print_material_info', $productId);
		$this->db->where('tbl_measurements_idtbl_mesurements', $uomID);
		$this->db->where('tbl_material_uom_qty.status', 1);
	
		$query = $this->db->get();
		$result = $query->row();
	
		$response = new stdClass();
		if ($result) {
			$response->piecesper_qty = $result->qty*$qty;  
			$response->measure_type = $result->measure_type;  
		} else {
			$response->piecesper_qty = 0;
			$response->measure_type = 0;
		}
	
		echo json_encode($response);
	}

    public function Getproductinfoamachine(){
        $recordID=$this->input->post('recordID');
		$purchaseorder_id=$this->input->post('purchaseorder_id');

        $this->db->select('`qty`, `unitprice`, `tbl_measurements_idtbl_measurements`');
        $this->db->from('tbl_print_porder_req_detail');
        $this->db->where('status', 1);
		$this->db->where('tbl_print_porder_idtbl_print_porder', $purchaseorder_id);
        $this->db->where('tbl_machine_id', $recordID);
        $respond=$this->db->get();

		$this->db->select('*');
        $this->db->from('tbl_print_stock');
        $this->db->where('status', 1);
		$this->db->where('tbl_machine_id ', $recordID);
		$this->db->order_by('idtbl_print_stock', 'desc');
		$this->db->limit(1);
        $respond2=$this->db->get();
		$count = $respond2->num_rows();

		$unitprice='';
		if($respond2->num_rows()>0){
            $obj=new stdClass();
            $unitprice=$respond2->row(0)->unitprice;
        }


        if($respond->num_rows()>0){
            $obj=new stdClass();
            $obj->qty=$respond->row(0)->qty;
			($count==0 ?  $obj->unitprice=$respond->row(0)->unitprice : $obj->unitprice=$unitprice);
            $obj->uom=$respond->row(0)->tbl_measurements_idtbl_measurements;
        }

        else{
            $obj=new stdClass();
            $obj->qty=0;
            $obj->unitprice=0;
            $obj->uom='';
        }
        echo json_encode($obj);
    }


    public function Getproductinfoservice(){
        $recordID=$this->input->post('recordID');
		$purchaseorder_id=$this->input->post('purchaseorder_id');

        $this->db->select('`qty`, `unitprice`, `tbl_measurements_idtbl_measurements`');
        $this->db->from('tbl_print_porder_req_detail');
        $this->db->where('status', 1);
		$this->db->where('tbl_print_porder_idtbl_print_porder', $purchaseorder_id);
        $this->db->where('tbl_service_type_id', $recordID);

        $respond=$this->db->get();

        if($respond->num_rows()>0){
            $obj=new stdClass();
            $obj->qty=$respond->row(0)->qty;
            $obj->unitprice=$respond->row(0)->unitprice;
            $obj->uom=$respond->row(0)->tbl_measurements_idtbl_measurements;
        }

        else{
            $obj=new stdClass();
            $obj->qty=0;
            $obj->unitprice=0;
            $obj->uom='';
        }
        echo json_encode($obj);
    }


	public function Purchaseorderinsertupdate() {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];

		$companyID=$_SESSION['company_id'];


		$tableData=$this->input->post('tableData');
		$orderdate=$this->input->post('orderdate');
		$discounttotal=$this->input->post('discounttotal');
		$vatamounttotal=$this->input->post('vatamounttotal');
		$grosstotal=$this->input->post('grosstotal');
		$total=$this->input->post('total');
		$remark=$this->input->post('remark');
		$supplier=$this->input->post('supplier');
		$location=$this->input->post('location');
		$ordertype=$this->input->post('ordertype');
		$company_id=$this->input->post('company_id');
		$branch_id=$this->input->post('branch_id');
		$porderrequest=$this->input->post('porderrequest');
		$contactperson=$this->input->post('contactperson');

		$updatedatetime=date('Y-m-d H:i:s');

		$data=array(
			'orderdate'=> $orderdate,
			'duedate'=> 'null',
			'subtotal'=>'0',
			'vattotamount'=> '0',
			'discountamount'=> '0',
			'nettotal'=>$grosstotal,
			'confirmstatus'=> '0',
			'grnconfirm'=>'0',
			'remark'=> $remark,
			'status'=> '1',
			'insertdatetime'=> $updatedatetime,
			'tbl_user_idtbl_user'=> $userID,
			'tbl_supplier_idtbl_supplier'=> $supplier,
			'tbl_material_group_idtbl_material_group'=> $ordertype,
			'tbl_company_idtbl_company'=> $company_id, 
			'tbl_company_branch_idtbl_company_branch'=> $branch_id, 
			'tbl_print_porder_req_idtbl_print_porder_req'=> $porderrequest,
			'idtbl_po_contact_person'=> $contactperson,

		);

		$this->db->insert('tbl_print_porder', $data);

		$porderID=$this->db->insert_id();

			foreach ($tableData as $rowtabledata) {
				$materialname=$rowtabledata['col_1'];
				$comment=$rowtabledata['col_2'];
				$materialID=$rowtabledata['col_3'];
				$qty=$rowtabledata['col_4'];
				$uom=$rowtabledata['col_5'];
				$uomID=$rowtabledata['col_6'];
				$unit=$rowtabledata['col_7'];
				$packetprice=$rowtabledata['col_8'];
				$nettotal=$rowtabledata['col_9'];
				$pieces=$rowtabledata['col_11'];
				

				$dataone=array(
					'qty'=> $qty,
					'pieces'=> $pieces,
					'tbl_measurements_idtbl_measurements'=> $uomID,
					'unitprice'=> $unit,
					'packetprice'=> $packetprice,
					'discount'=>'0',
					'vat'=>'0',
					'vatamount'=>'0',
					'grossprice'=>'0',
					'netprice'=>  $nettotal,
					'comment'=> $comment,
					'status'=> '1',
					'insertdatetime'=> $updatedatetime,
					'tbl_print_porder_idtbl_print_porder'=> $porderID,
					'tbl_material_id'=> $materialID,
					'tbl_user_idtbl_user'=> $userID
					
				);

				$this->db->insert('tbl_print_porder_detail', $dataone);
			}
		// Generate the PO NO
		
		$currentYear = date("Y", strtotime($orderdate));
		$currentMonth = date("m", strtotime($orderdate));
	
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

		$this->db->select('porder_no');
		$this->db->from('tbl_print_porder');
		$this->db->where('tbl_company_idtbl_company', $companyID);
        $this->db->where("DATE(orderdate) >=", $fromyear);
        $this->db->where("DATE(orderdate) <=", $toyear);
		$this->db->order_by('porder_no', 'DESC');
		$this->db->limit(1);
		$respond = $this->db->get();
		
		if ($respond->num_rows() > 0) {
			$last_po_no = $respond->row()->porder_no;
			$po_number = intval(substr($last_po_no, -4));
			$count = $po_number;
		} else {
			$count = 0;
		}

		$count++; 
		$countPrefix = sprintf('%04d', $count);

		$yearDigit = substr(date("Y", strtotime($fromyear)), -2);

		$reqno = 'PO' . $yearDigit . $countPrefix;

		$datadetail = array(
			'porder_no'=> $reqno, 
			'updatedatetime'=> $updatedatetime
		);

		$this->db->where('idtbl_print_porder', $porderID);
		$this->db->update('tbl_print_porder', $datadetail);


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

	public function Purchaseorderview() {

		$recordID = $this->input->post('recordID');

		$sql = "SELECT `u`.*, `ua`.`suppliername`, `ua`.`address_line1`, `ub`.`branch`, `ub`.`phone`, `ub`.`address1`, `ub`.`address2`, `ub`.`mobile`, `ub`.`email` AS `locemail`, `uc`.`company`, `ud`.`name` AS `checkby`
				FROM `tbl_print_porder` AS `u`
				LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`)
				LEFT JOIN `tbl_company_branch` AS `ub` ON (`ub`.`idtbl_company_branch` = `u`.`tbl_company_branch_idtbl_company_branch`)
				LEFT JOIN `tbl_company` AS `uc` ON (`uc`.`idtbl_company` = `u`.`tbl_company_idtbl_company`)
				LEFT JOIN `tbl_user` AS `ud` ON (`ud`.`idtbl_user` = `u`.`check_by`)
				WHERE `u`.`status`=? AND `u`.`idtbl_print_porder`=?";

		$respond = $this->db->query($sql, array(1, $recordID));

		$this->db->select('tbl_print_porder_detail.*, tbl_print_porder.porder_no, tbl_print_porder.orderdate,
			tbl_print_porder.tbl_material_group_idtbl_material_group,
			tbl_print_material_info.materialinfocode,
			tbl_print_material_info.materialname,
			tbl_measurements.measure_type,
			tbl_material_group.idtbl_material_group');

		$this->db->from('tbl_print_porder_detail');
		$this->db->join('tbl_print_material_info', 'tbl_print_material_info.idtbl_print_material_info = tbl_print_porder_detail.tbl_material_id', 'left');
		$this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_porder_detail.tbl_measurements_idtbl_measurements', 'left');
		$this->db->join('tbl_print_porder', 'tbl_print_porder.idtbl_print_porder = tbl_print_porder_detail.tbl_print_porder_idtbl_print_porder', 'left');
		$this->db->join('tbl_material_group', 'tbl_material_group.idtbl_material_group = tbl_print_porder.tbl_material_group_idtbl_material_group', 'left');
		$this->db->where('tbl_print_porder_detail.tbl_print_porder_idtbl_print_porder', $recordID);
		$this->db->where('tbl_print_porder_detail.status', 1);

		$responddetail = $this->db->get();

		$ordertype = $responddetail->row(0)->idtbl_material_group;

		$html = '';

		$html .= '
		<div class="row">
			<div class="col-6 small">
				<label class="small font-weight-bold text-dark mb-1">Date:</label> '.$responddetail->row(0)->orderdate.'<br>
				<label class="small font-weight-bold text-dark mb-1">PO No:</label> '.$responddetail->row(0)->porder_no.'<br>
				<label class="small font-weight-bold text-dark mb-1">Customer:</label> '.$respond->row(0)->suppliername.'
			</div>
			<div class="col-6 small">
				<label class="small font-weight-bold text-dark mb-1">Company:</label> '.$respond->row(0)->company.'<br>
				<label class="small font-weight-bold text-dark mb-1">Branch:</label> '.$respond->row(0)->branch.'<br>
				<label class="small font-weight-bold text-dark mb-1">Check By:</label> '.$respond->row(0)->checkby.'
			</div>
		</div>
		<hr class="border-dark">

		<div class="row">
		<div class="col-12">
		<table class="table table-striped table-bordered table-sm">
		<thead>
		<tr>';

		if ($ordertype == 4) {
			$html .= '<th>Service Item</th><th>Product</th>';
		} else {
			$html .= '<th>Product Info</th>';
		}

		$html .= '
			<th class="text-right">Unit Price</th>
			<th class="text-right">Last GRN Price</th>
			<th class="text-right">Qty</th>
			<th class="text-center">Uom</th>
			<th class="text-right">Total</th>
		</tr>
		</thead>
		<tbody>';

		foreach ($responddetail->result() as $roworderinfo) {

			$material = $roworderinfo->materialname;
			if (!empty($roworderinfo->materialinfocode)) {
				$material .= ' / ' . $roworderinfo->materialinfocode;
			}

			// Last GRN price lookup — only meaningful for actual materials (has a materialname),
			// service items (ordertype 4) won't match anything in tbl_print_grndetail by name.
			$lastGrnPriceDisplay = '&nbsp;';
			if (!empty($roworderinfo->materialname)) {
				$grnhistory = $this->getLastGRNHistory($roworderinfo->materialname);
				if (!empty($grnhistory)) {
					$lastGrnPriceDisplay = number_format($grnhistory[0]['unitprice'], 2)
						. ' <small class="text-muted">(' . $grnhistory[0]['grndate'] . ')</small>';
				}
			}

			$html .= '<tr>';

			if ($ordertype == 4) {
				$html .= '<td>' . $material . '</td>'; 
				$html .= '<td>' . $roworderinfo->comment . '</td>';
			} else {
				$html .= '<td>' . $material . '</td>';
			}

			$html .= '
				<td class="text-right">' . (!empty($roworderinfo->packetprice) ? $roworderinfo->packetprice : $roworderinfo->unitprice) . '</td>
				<td class="text-right">' . $lastGrnPriceDisplay . '</td>
				<td class="text-right">' . $roworderinfo->qty . '</td>
				<td class="text-center">' . $roworderinfo->measure_type . '</td>
				<td class="text-right">' . number_format(($roworderinfo->netprice), 2) . '</td>
			</tr>';
		}

		$html .= '
		</tbody>
		</table>
		</div>
		</div>

		<div class="row mt-3">
			<div class="col-6">
				<h6 class="font-weight-normal"><b>Remark :</b>
					&nbsp;&nbsp;' . ($respond->row(0)->remark) . '
				</h6>
			</div>
			<div class="col-6 text-right">
				<h3 class="font-weight-normal">
					<strong style="background-color: yellow;">Final Price</strong>
					&nbsp;&nbsp;<b>Rs. ' . number_format(($respond->row(0)->nettotal), 2) . '</b>
				</h3>
			</div>
		</div>';

		echo $html;
	}

	public function porderviewheader() {
		$recordID=$this->input->post('recordID');

		$this->db->select('tbl_print_porder.*,tbl_supplier.suppliername AS suppliername,tbl_supplier.telephone_no AS suppliercontact,tbl_supplier.address_line1 AS address1,tbl_supplier.address_line2 AS address2,tbl_supplier.city AS city,tbl_supplier.state AS supplierstate,
								tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
                                tbl_company.phone companyphone,tbl_company.email AS companyemail,
                                tbl_company_branch.branch AS branchname,
                                tbl_po_contact_person.contact_person AS contactpersonname,
                                tbl_po_contact_person.designation AS contactpersondesignation');
		$this->db->from('tbl_print_porder');
		$this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier  = tbl_print_porder.tbl_supplier_idtbl_supplier ', 'left');
		$this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_print_porder.tbl_company_idtbl_company', 'left');
		$this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_print_porder.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->join('tbl_po_contact_person', 'tbl_po_contact_person.idtbl_po_contact_person = tbl_print_porder.idtbl_po_contact_person', 'left');
		$this->db->where('idtbl_print_porder', $recordID);
		$this->db->where('tbl_print_porder.status', 1);

		$respond=$this->db->get();

		$obj=new stdClass();
		$obj->orderdate=$respond->row(0)->orderdate;
		$obj->suppliername=$respond->row(0)->suppliername;
		$obj->suppliercontact=$respond->row(0)->suppliercontact;
		$obj->address1=$respond->row(0)->address1;
		$obj->address2=$respond->row(0)->address2;
		$obj->city=$respond->row(0)->city;
		$obj->state=$respond->row(0)->supplierstate;
		$obj->companyname=$respond->row(0)->companyname;
		$obj->companyaddress=$respond->row(0)->companyaddress;
		$obj->companymobile=$respond->row(0)->companymobile;
		$obj->companyphone=$respond->row(0)->companyphone;
		$obj->companyemail=$respond->row(0)->companyemail;
		$obj->branchname=$respond->row(0)->branchname;
		$obj->contactpersonname=$respond->row(0)->contactpersonname;
		$obj->contactpersondesignation=$respond->row(0)->contactpersondesignation;

		echo json_encode($obj);
	}

	public function Purchaseorderstatus() {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];
		$recordID=$this->input->post('porderid');
        $reqid=$this->input->post('reqestid');
		$confirmnot=$this->input->post('confirmnot');
		$cpstatus=$this->input->post('cpstatus');
		$updatedatetime=date('Y-m-d H:i:s');

		$companyID=$_SESSION['company_id'];
		$branchID=$_SESSION['branch_id'];

		// if($type==1) {
			$data=array(
				'confirmstatus'=> $confirmnot,
				'approve_by'=> $userID,
				'cp_status'=> $cpstatus ? 1 : 0,
				'updatedatetime'=> $updatedatetime);

			$this->db->where('idtbl_print_porder', $recordID);
			$this->db->update('tbl_print_porder', $data);

            $data1 = array(
                'porderconfirm' => '1',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_print_porder_req', $reqid);
            $this->db->update('tbl_print_porder_req', $data1);


			// $this->db->select('tbl_print_porder.idtbl_print_porder,tbl_print_porder.orderdate, tbl_print_porder.nettotal,tbl_print_porder.tbl_material_group_idtbl_material_group,tbl_print_porder.tbl_supplier_idtbl_supplier');
			// $this->db->from('tbl_print_porder');
			// $this->db->where('tbl_print_porder.status', 1);
			// $this->db->where('tbl_print_porder.idtbl_print_porder', $recordID);

			// $respond=$this->db->get();

			// if ($respond->num_rows() > 0) {
			// 	foreach ($respond->result() as $row) {
			// 		$grnid=$row->idtbl_print_porder;
			// 		$totalamount=$row->nettotal;
			// 		$supplier=$row->tbl_supplier_idtbl_supplier;
			// 		$grndate=$row->orderdate;
			// 		$orderType=$row->tbl_material_group_idtbl_material_group;


			// 		if ($orderType == 4) {
			// 			$accountsData = array(
			// 				'grndate' => $grndate,
			// 				'tbl_supplier_idtbl_supplier' => $supplier,
			// 				'exptype' => '2',
			// 				'grnno' => $grnid,
			// 				'expcode' => 'SER',
			// 				'amount' => $totalamount,
			// 				'status' => '1',
			// 				'insertdatetime' => $updatedatetime,
			// 				'tbl_user_idtbl_user' => $userID,
			// 				'tbl_company_idtbl_company'=> $companyID, 
			// 				'tbl_company_branch_idtbl_company_branch'=> $branchID
			// 			);
			// 			$this->db->insert('tbl_expence_info', $accountsData);
			// 		}
			// 	}
			// }



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

    public function POmanualconfirm($x){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $updatedatetime=date('Y-m-d H:i:s');

            $data = array(
                'grnconfirm' => '1',
                'tbl_user_idtbl_user'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

			$this->db->where('idtbl_print_porder', $recordID);
            $this->db->update('tbl_print_porder', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                
                $actionObj=new stdClass();
                $actionObj->icon='fas fa-check';
                $actionObj->title='';
                $actionObj->message='Manually Completed';
                $actionObj->url='';
                $actionObj->target='_blank';
                $actionObj->type='success';

                $actionJSON=json_encode($actionObj);
                
                $this->session->set_flashdata('msg', $actionJSON);
                redirect('Purchaseorder');                
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
                redirect('Purchaseorder');
            }
        
    }

	public function Getsupplieraccoporderreq() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_supplier_idtbl_supplier`');
		$this->db->from('tbl_print_porder_req');
		$this->db->where('status', 1);
		$this->db->where('idtbl_print_porder_req', $recordID);

		$respond=$this->db->get();

		echo $respond->row(0)->tbl_supplier_idtbl_supplier;
	}

	public function Getporderreqdetails() {
		$recordID = $this->input->post('recordID');
		
		$this->db->select('requestname, qty, measure_type, comment, group, tbl_print_porder_req_detail.newexist_status');
		$this->db->from('tbl_print_porder_req_detail');
		$this->db->join('tbl_print_porder_req', 'tbl_print_porder_req.idtbl_print_porder_req = tbl_print_porder_req_detail.tbl_print_porder_req_idtbl_print_porder_req', 'left');
		$this->db->join('tbl_material_group', 'tbl_material_group.idtbl_material_group = tbl_print_porder_req.tbl_material_group_idtbl_material_group', 'left');
		$this->db->join('tbl_measurements', 'tbl_measurements.idtbl_mesurements = tbl_print_porder_req_detail.tbl_measurements_idtbl_measurements', 'left');
		$this->db->where('tbl_print_porder_req_detail.status', 1);
		$this->db->where('tbl_print_porder_req_idtbl_print_porder_req', $recordID);
		
		$response = $this->db->get();
		
		if ($response->num_rows() > 0) {
			$result = [];
			foreach ($response->result() as $row) {
				$grnhistory = [];

				if ($row->newexist_status == 0) {
					$grnhistory = $this->getLastGRNHistory($row->requestname);
				}

				$result[] = [
					'requestname'  => $row->requestname,
					'qty'          => $row->qty,
					'measure_type' => $row->measure_type,
					'comment'      => $row->comment,
					'order_type'   => $row->group,
					'grnhistory'   => $grnhistory
				];
			}
			echo json_encode($result);
		} else {
			echo json_encode([]);
		}
	}

	private function getLastGRNHistory($materialname) {
		$sql = "SELECT grn.grndate, grnd.qty, grnd.unitprice
				FROM tbl_print_grndetail grnd
				INNER JOIN tbl_print_grn grn ON grn.idtbl_print_grn = grnd.tbl_print_grn_idtbl_print_grn
				INNER JOIN tbl_print_material_info mi ON mi.idtbl_print_material_info = grnd.tbl_print_material_info_idtbl_print_material_info
				WHERE mi.materialname = ?
				AND grn.status = 1
				AND grnd.status = 1
				ORDER BY grn.grndate DESC, grn.idtbl_print_grn DESC
				LIMIT 2";

		$query = $this->db->query($sql, array($materialname));

		$history = [];
		foreach ($query->result() as $row) {
			$history[] = [
				'grndate'   => $row->grndate,
				'qty'       => $row->qty,
				'unitprice' => $row->unitprice
			];
		}

		return $history;
	}			

	public function getProductsByType() {

		$companyID = $_SESSION['company_id'];
		$branchID  = $_SESSION['branch_id'];
		$searchTerm = $this->input->post('searchTerm');
		$ordertype  = $this->input->post('ordertype');

		$this->db->select('idtbl_print_material_info as id, materialname as name');
		$this->db->from('tbl_print_material_info');
		$this->db->where('status', 1);
		$this->db->where('tbl_material_group_idtbl_material_group', $ordertype);
		$this->db->where('tbl_company_idtbl_company', $companyID);

		if(!empty($searchTerm)){
			$this->db->like('materialname', $searchTerm, 'both');
		} else {
			$this->db->limit(5);
		}

		$query = $this->db->get();

		$data = array();

		foreach ($query->result() as $row) {
			$data[] = array(
				"id" => $row->id,
				"text" => $row->name
			);
		}

		echo json_encode($data);
	}

	public function GetSemimateriallist(){
        $searchTerm=$this->input->post('searchTerm');

        if(!isset($searchTerm)){
            $sql="SELECT `tbl_material_info`.`idtbl_material_info`, `tbl_material_info`.`materialinfocode`, `tbl_material_code`.`materialname`, `tbl_unit`.`unitcode` FROM `tbl_material_info` LEFT JOIN `tbl_material_code` ON `tbl_material_code`.`idtbl_material_code`=`tbl_material_info`.`tbl_material_code_idtbl_material_code` LEFT JOIN `tbl_unit` ON `tbl_unit`.`idtbl_unit`=`tbl_material_info`.`tbl_unit_idtbl_unit` WHERE `tbl_material_info`.`status`=? AND `tbl_material_info`.`semistatus`=? LIMIT 5";
            $respond=$this->db->query($sql, array(1, 1));                       
        }
        else{            
            if(!empty($searchTerm)){
                $sql="SELECT `tbl_material_info`.`idtbl_material_info`, `tbl_material_info`.`materialinfocode`, `tbl_material_code`.`materialname`, `tbl_unit`.`unitcode` FROM `tbl_material_info` LEFT JOIN `tbl_material_code` ON `tbl_material_code`.`idtbl_material_code`=`tbl_material_info`.`tbl_material_code_idtbl_material_code` LEFT JOIN `tbl_unit` ON `tbl_unit`.`idtbl_unit`=`tbl_material_info`.`tbl_unit_idtbl_unit` WHERE `tbl_material_info`.`status`=? AND `tbl_material_info`.`semistatus`=? AND `tbl_material_code`.`materialname` LIKE '$searchTerm%'";
                $respond=$this->db->query($sql, array(1, 1));    
            }
            else{
                $sql="SELECT `tbl_material_info`.`idtbl_material_info`, `tbl_material_info`.`materialinfocode`, `tbl_material_code`.`materialname`, `tbl_unit`.`unitcode` FROM `tbl_material_info` LEFT JOIN `tbl_material_code` ON `tbl_material_code`.`idtbl_material_code`=`tbl_material_info`.`tbl_material_code_idtbl_material_code` LEFT JOIN `tbl_unit` ON `tbl_unit`.`idtbl_unit`=`tbl_material_info`.`tbl_unit_idtbl_unit` WHERE `tbl_material_info`.`status`=? AND `tbl_material_info`.`semistatus`=? LIMIT 5";
                $respond=$this->db->query($sql, array(1, 1));                
            }
        }
        
        $data=array();
        
        foreach ($respond->result() as $row) {
            $data[]=array("id"=>$row->idtbl_material_info, "text"=>$row->materialname.' - '.$row->materialinfocode.'/'.$row->unitcode);
        }
        
        echo json_encode($data);
    }

	public function Getpordertpeaccoporderrequest() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_material_group_idtbl_material_group`');
		$this->db->from('tbl_print_porder_req');
		$this->db->where('status', 1);
		$this->db->where('idtbl_print_porder_req', $recordID);

		$respond=$this->db->get();

		echo $respond->row(0)->tbl_material_group_idtbl_material_group;
	}

	public function Getmesuretpeaccorproduct() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_measurements_idtbl_measurements`');
		$this->db->from('tbl_print_porder_req_detail');
		$this->db->where('status', 1);
		$this->db->where('tbl_print_porder_idtbl_print_porder', $recordID);

		$respond=$this->db->get();

		echo $respond->row(0)->tbl_measurements_idtbl_measurements;
	}

	public function Purchaseorderedit()
	{
		$recordID  = $this->input->post('recordID');
		$companyID = $_SESSION['company_id'];

		$this->db->select([
			'tbl_print_porder.idtbl_print_porder AS porder_id',
			'tbl_print_porder.tbl_print_porder_req_idtbl_print_porder_req AS request_id',
			'tbl_print_porder.orderdate',
			'tbl_print_porder.remark',
			'tbl_print_porder.tbl_supplier_idtbl_supplier AS supplier_id',
			'tbl_print_porder.tbl_material_group_idtbl_material_group AS order_type',
			'tbl_print_porder.idtbl_po_contact_person AS contactperson_id',

			'tbl_print_porder_detail.pieces',
			'tbl_print_porder_detail.actual_qty',
			'tbl_print_porder_detail.unitprice AS detail_unitprice',
			'tbl_print_porder_detail.packetprice',
			'tbl_print_porder_detail.comment',
			'tbl_print_porder_detail.netprice',
			'tbl_print_porder_detail.qty',

			'tbl_measurements.idtbl_mesurements AS measure_id',
			'tbl_measurements.measure_type',

			'tbl_print_material_info.idtbl_print_material_info AS material_id',
			'tbl_print_material_info.materialname'
		]);

		$this->db->from('tbl_print_porder');
		$this->db->join('tbl_print_porder_detail',
			'tbl_print_porder.idtbl_print_porder = tbl_print_porder_detail.tbl_print_porder_idtbl_print_porder',
			'left'
		);
		$this->db->join('tbl_supplier',
			'tbl_supplier.idtbl_supplier = tbl_print_porder.tbl_supplier_idtbl_supplier',
			'left'
		);
		$this->db->join('tbl_measurements',
			'tbl_measurements.idtbl_mesurements = tbl_print_porder_detail.tbl_measurements_idtbl_measurements',
			'left'
		);
		$this->db->join('tbl_print_material_info',
			'tbl_print_material_info.idtbl_print_material_info = tbl_print_porder_detail.tbl_material_id',
			'left'
		);

		$this->db->join('tbl_material_group',
			'tbl_material_group.idtbl_material_group = tbl_print_porder.tbl_material_group_idtbl_material_group',
			'left'
		);

		$this->db->where('tbl_print_porder.idtbl_print_porder', $recordID);
		$this->db->where('tbl_print_porder.tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_print_porder.status', 1);

		$respond = $this->db->get();

		if ($respond->num_rows() === 0) {
			echo json_encode([]);
			return;
		}

		$row0 = $respond->row();

		$obj = new stdClass();
		$obj->id        = $row0->porder_id;
		$obj->requestid = $row0->request_id;
		$obj->orderdate = $row0->orderdate;
		$obj->supplier  = $row0->supplier_id;
		$obj->type      = $row0->order_type;
		$obj->remark      = $row0->remark;
		$obj->contactperson = $row0->contactperson_id;

		$items = [];
		foreach ($respond->result() as $row) {
			$item = new stdClass();
			$item->pieces      = $row->pieces;
			$item->actual_qty  = $row->actual_qty;
			$item->unitprice   = $row->detail_unitprice;
			$item->packetprice = $row->packetprice;
			$item->comment     = $row->comment;
			$item->measureID   = $row->measure_id;
			$item->measure     = $row->measure_type;
			$item->materialID  = $row->material_id;
			$item->material    = $row->materialname;
			$item->netprice    = $row->netprice;
			$item->qty         = $row->qty;
			$items[] = $item;
		}

		$obj->items = $items;

		echo json_encode($obj);
	}


	public function Purchaseorderupdate(){
        $this->db->trans_begin();
    
        $userID=$_SESSION['userid'];
    
        $tableData=$this->input->post('tableData');
    
        // Check if $tableData is an array and not empty
        if(is_array($tableData) && !empty($tableData)){
			$orderdate=$this->input->post('orderdate');
			$discounttotal=$this->input->post('discounttotal');
			$vatamounttotal=$this->input->post('vatamounttotal');
			$grosstotal=$this->input->post('grosstotal');
			$total=$this->input->post('total');
			$remark=$this->input->post('remark');
			$supplier=$this->input->post('supplier');
			$location=$this->input->post('location');
			$ordertype=$this->input->post('ordertype');
			$company_id=$this->input->post('company_id');
			$branch_id=$this->input->post('branch_id');
			$porderID=$this->input->post('porderID');
			$porderreqID=$this->input->post('porderreqID');
			$contactperson=$this->input->post('contactperson');
            $updatedatetime=date('Y-m-d H:i:s');
    
			$data=array(
			'orderdate'=> $orderdate,
			'duedate'=> 'null',
			'subtotal'=>'0',
			'vattotamount'=> '0',
			'discountamount'=> '0',
			'nettotal'=>$grosstotal,
			'confirmstatus'=> '0',
			'grnconfirm'=>'0',
			'remark'=> $remark,
			'status'=> '1',
			'updatedatetime'=> $updatedatetime,
			'updateuser'=> $userID,
			'tbl_supplier_idtbl_supplier'=> $supplier,
			'tbl_material_group_idtbl_material_group'=> $ordertype,
			'tbl_company_idtbl_company'=> $company_id, 
			'tbl_company_branch_idtbl_company_branch'=> $branch_id, 
			'tbl_print_porder_req_idtbl_print_porder_req '=> $porderreqID,
			'idtbl_po_contact_person'=> $contactperson,

		);
    
            $this->db->where('idtbl_print_porder', $porderID);
            $this->db->update('tbl_print_porder', $data);
    
    
            $this->db->where('tbl_print_porder_idtbl_print_porder', $porderID);
            $this->db->delete('tbl_print_porder_detail');

			foreach ($tableData as $rowtabledata) {
				$materialname=$rowtabledata['col_1'];
				$comment=$rowtabledata['col_2'];
				$materialID=$rowtabledata['col_3'];
				$qty=$rowtabledata['col_4'];
				$uom=$rowtabledata['col_5'];
				$uomID=$rowtabledata['col_6'];
				$unit=$rowtabledata['col_7'];
				$packetprice=$rowtabledata['col_8'];
				$nettotal=$rowtabledata['col_9'];
				$pieces=$rowtabledata['col_11'];

				$dataone=array(
					'qty'=> $qty,
					'pieces'=> $pieces,
					'tbl_measurements_idtbl_measurements'=> $uomID,
					'unitprice'=> $unit,
					'packetprice'=> $packetprice, 
					'discount'=>'0',
					'vat'=>'0',
					'vatamount'=>'0',
					'grossprice'=>'0',
					'netprice'=>  $nettotal,
					'comment'=> $comment,
					'status'=> '1',
					'updatedatetime'=> $updatedatetime,
					'tbl_print_porder_idtbl_print_porder'=> $porderID,
					'tbl_material_id'=> $materialID,
					'tbl_user_idtbl_user'=> $userID
				);

				$this->db->insert('tbl_print_porder_detail', $dataone);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === TRUE) {
				$this->db->trans_commit();
				
				$actionObj=new stdClass();
				$actionObj->icon='fas fa-save';
				$actionObj->title='';
				$actionObj->message='Record Update Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='success';

				$actionJSON=json_encode($actionObj);

				$obj=new stdClass();
				$obj->status=1;          
				$obj->action=$actionJSON;  
				
				echo json_encode($obj);
			} else {
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

	public function Purchaseordercheckstatus() {
		$this->db->trans_begin();

        $recordID=$this->input->post('requestid');
		$confirmnot=$this->input->post('confirmnot');
		$userID=$_SESSION['userid'];
		$updatedatetime=date('Y-m-d H:i:s');

			$data=array(
				'check_by'=> $userID);

			$this->db->where('idtbl_print_porder', $recordID);
			$this->db->update('tbl_print_porder', $data);


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