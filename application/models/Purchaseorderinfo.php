<?php class Purchaseorderinfo extends CI_Model {

	public function Getcompany() {
		$this->db->select('`idtbl_company`, `company`');
		$this->db->from('tbl_company');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getporder() {
		$comapnyID=$_SESSION['company_id'];

		$this->db->select('`idtbl_porder_req`,`porder_req_no`');
		$this->db->from('tbl_porder_req');
		$this->db->where('status', 1);
		$this->db->where('confirmstatus', 1);
		$this->db->where('porderconfirm', 0);
		$this->db->where('tbl_porder_req.tbl_company_idtbl_company', $comapnyID);
		$this->db->order_by('idtbl_porder_req', 'DESC');

		return $respond=$this->db->get();
	}

	public function Getsupplier() {
		$companyID=$_SESSION['company_id'];

		$this->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername');
		$this->db->from('tbl_supplier');
		$this->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type', 'left');
		$this->db->where('tbl_supplier.status', 1);
		$this->db->where('tbl_supplier.tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);

		return $respond=$this->db->get();
	}

	public function searchSuppliers($term) {
		$companyID=$_SESSION['company_id'];

		$this->db->select('tbl_supplier.idtbl_supplier AS id, tbl_supplier.suppliername AS name');
		$this->db->from('tbl_supplier');
		$this->db->join('tbl_supplier_type', 'tbl_supplier_type.idtbl_supplier_type = tbl_supplier.tbl_supplier_type_idtbl_supplier_type', 'left');
		$this->db->where('tbl_supplier.status', 1);
		$this->db->where('tbl_supplier.tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_supplier_type.idtbl_supplier_type !=', 5);
		if ($term !== '') {
			$this->db->like('tbl_supplier.suppliername', $term, 'both');
		}
		$this->db->order_by('tbl_supplier.suppliername', 'ASC');
		$this->db->limit(50);
		$query = $this->db->get();

		$data = array();
		foreach ($query->result() as $row) {
			$data[] = array("id" => $row->id, "text" => $row->name);
		}
		return $data;
	}

	public function searchProducts($term) {
		$this->db->select('tbl_product.idtbl_product AS id, tbl_product.product_code, tbl_product.product_name');
		$this->db->from('tbl_product');
		$this->db->where('tbl_product.status', 1);
		if ($term !== '') {
			$this->db->group_start();
			$this->db->like('tbl_product.product_name', $term, 'both');
			$this->db->or_like('tbl_product.product_code', $term, 'both');
			$this->db->or_like('tbl_product.model_no', $term, 'both');
			$this->db->or_like('tbl_product.barcode', $term, 'both');
			$this->db->group_end();
		}
		$this->db->order_by('tbl_product.product_name', 'ASC');
		$this->db->limit(20);
		$query = $this->db->get();

		$data = array();
		foreach ($query->result() as $row) {
			$data[] = array("id" => $row->id, "text" => $row->product_name.' ('.$row->product_code.')');
		}
		return $data;
	}

	public function Getproductprice() {
		$recordID = (int)$this->input->post('recordID');
		$supplier = (int)$this->input->post('supplier');

		$this->db->select('tbl_product.cost_price, COALESCE(NULLIF(tbl_unit.unit_short, ""), tbl_unit.unit) AS unit', FALSE);
		$this->db->from('tbl_product');
		$this->db->join('tbl_unit', 'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');
		$this->db->where('tbl_product.idtbl_product', $recordID);
		$respond = $this->db->get();

		$obj = new stdClass();
		if ($respond->num_rows() > 0) {
			$unitprice = $supplier > 0 ? $this->getLatestGRNUnitPrice($supplier, $recordID) : null;
			$obj->unitprice = ($unitprice !== null) ? $unitprice : $respond->row(0)->cost_price;
			$obj->unit = $respond->row(0)->unit;
		} else {
			$obj->unitprice = 0;
			$obj->unit = '';
		}

		echo json_encode($obj);
	}

	private function getLatestGRNUnitPrice($supplier, $productID) {
		$this->db->select('tbl_grndetail.unitprice');
		$this->db->from('tbl_grndetail');
		$this->db->join('tbl_grn', 'tbl_grn.idtbl_grn = tbl_grndetail.tbl_grn_idtbl_grn');
		$this->db->where('tbl_grn.tbl_supplier_idtbl_supplier', $supplier);
		$this->db->where('tbl_grndetail.tbl_product_idtbl_product', $productID);
		$this->db->where('tbl_grn.status', 1);
		$this->db->where('tbl_grndetail.status', 1);
		$this->db->order_by('tbl_grn.insertdatetime', 'desc');
		$this->db->limit(1);
		$result = $this->db->get();
		return ($result->num_rows() > 0) ? $result->row(0)->unitprice : null;
	}

	private function getLastGRNHistory($productID) {
		$sql = "SELECT grn.grndate, grnd.qty, grnd.unitprice
				FROM tbl_grndetail grnd
				INNER JOIN tbl_grn grn ON grn.idtbl_grn = grnd.tbl_grn_idtbl_grn
				WHERE grnd.tbl_product_idtbl_product = ?
				AND grn.status = 1
				AND grnd.status = 1
				ORDER BY grn.grndate DESC, grn.idtbl_grn DESC
				LIMIT 2";

		$query = $this->db->query($sql, array($productID));

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

	public function Purchaseorderinsertupdate() {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];
		$companyID=$_SESSION['company_id'];

		$tableData=$this->input->post('tableData');
		$orderdate=$this->input->post('orderdate');
		$grosstotal=(float)$this->input->post('grosstotal');
		$remark=$this->input->post('remark');
		$supplier=$this->input->post('supplier');
		$company_id=$this->input->post('company_id');
		$branch_id=$this->input->post('branch_id');
		$porderrequest=(int)$this->input->post('porderrequest');

		$updatedatetime=date('Y-m-d H:i:s');

		$data=array(
			'porder_no'=> '',
			'orderdate'=> $orderdate,
			'duedate'=> NULL,
			'subtotal'=> $grosstotal,
			'vattotamount'=> 0,
			'discountamount'=> 0,
			'nettotal'=> $grosstotal,
			'confirmstatus'=> 0,
			'servicepo_confirm'=> 0,
			'grnconfirm'=> 0,
			'remark'=> (string)$remark,
			'status'=> 1,
			'check_by'=> 0,
			'approve_by'=> 0,
			'cp_status'=> 0,
			'insertdatetime'=> $updatedatetime,
			'tbl_user_idtbl_user'=> $userID,
			'tbl_supplier_idtbl_supplier'=> $supplier,
			'tbl_company_idtbl_company'=> $company_id,
			'tbl_company_branch_idtbl_company_branch'=> $branch_id,
			'tbl_porder_req_idtbl_porder_req'=> $porderrequest,
			'idtbl_po_contact_person'=> 0
		);

		$this->db->insert('tbl_porder', $data);

		$porderID=$this->db->insert_id();

		// col_1 name, col_2 productID, col_3 qty, col_4 unit, col_5 unitprice, col_6 comment, col_7 total
		foreach ($tableData as $rowtabledata) {
			$productID=$rowtabledata['col_2'];
			$qty=$rowtabledata['col_3'];
			$unit=$rowtabledata['col_5'];
			$comment=$rowtabledata['col_6'];
			$nettotal=$rowtabledata['col_7'];

			$dataone=array(
				'qty'=> $qty,
				'unitprice'=> $unit,
				'discount'=> 0,
				'vat'=> 0,
				'vatamount'=> 0,
				'grossprice'=> $nettotal,
				'netprice'=> $nettotal,
				'comment'=> (string)$comment,
				'status'=> 1,
				'insertdatetime'=> $updatedatetime,
				'tbl_porder_idtbl_porder'=> $porderID,
				'tbl_product_idtbl_product'=> $productID,
				'tbl_user_idtbl_user'=> $userID
			);

			$this->db->insert('tbl_porder_detail', $dataone);
		}

		// Generate the PO NO
		$currentYear = date("Y", strtotime($orderdate));
		$currentMonth = date("m", strtotime($orderdate));

		if ($currentMonth < 4) {
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
		$this->db->from('tbl_porder');
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

		$this->db->where('idtbl_porder', $porderID);
		$this->db->update('tbl_porder', $datadetail);

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
				FROM `tbl_porder` AS `u`
				LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`)
				LEFT JOIN `tbl_company_branch` AS `ub` ON (`ub`.`idtbl_company_branch` = `u`.`tbl_company_branch_idtbl_company_branch`)
				LEFT JOIN `tbl_company` AS `uc` ON (`uc`.`idtbl_company` = `u`.`tbl_company_idtbl_company`)
				LEFT JOIN `tbl_user` AS `ud` ON (`ud`.`idtbl_user` = `u`.`check_by`)
				WHERE `u`.`status` IN (1,2) AND `u`.`idtbl_porder`=?";

		$respond = $this->db->query($sql, array($recordID));

		$this->db->select('tbl_porder_detail.*, tbl_product.product_code, tbl_product.product_name,
			COALESCE(NULLIF(tbl_unit.unit_short, ""), tbl_unit.unit) AS unit_name', FALSE);
		$this->db->from('tbl_porder_detail');
		$this->db->join('tbl_product', 'tbl_product.idtbl_product = tbl_porder_detail.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit', 'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');
		$this->db->where('tbl_porder_detail.tbl_porder_idtbl_porder', $recordID);
		$this->db->where('tbl_porder_detail.status', 1);

		$responddetail = $this->db->get();

		$html = '';

		$html .= '
		<div class="row">
			<div class="col-6 small">
				<label class="small font-weight-bold text-dark mb-1">Date:</label> '.$respond->row(0)->orderdate.'<br>
				<label class="small font-weight-bold text-dark mb-1">PO No:</label> '.$respond->row(0)->porder_no.'<br>
				<label class="small font-weight-bold text-dark mb-1">Supplier:</label> '.$respond->row(0)->suppliername.'
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
		<tr>
			<th>Product Info</th>
			<th>Comment</th>
			<th class="text-right">Unit Price</th>
			<th class="text-right">Last GRN Price</th>
			<th class="text-right">Qty</th>
			<th class="text-center">Unit</th>
			<th class="text-right">Total</th>
		</tr>
		</thead>
		<tbody>';

		foreach ($responddetail->result() as $roworderinfo) {

			$product = $roworderinfo->product_name;
			if (!empty($roworderinfo->product_code)) {
				$product .= ' / ' . $roworderinfo->product_code;
			}

			$lastGrnPriceDisplay = '&nbsp;';
			$grnhistory = $this->getLastGRNHistory($roworderinfo->tbl_product_idtbl_product);
			if (!empty($grnhistory)) {
				$lastGrnPriceDisplay = number_format($grnhistory[0]['unitprice'], 2)
					. ' <small class="text-muted">(' . $grnhistory[0]['grndate'] . ')</small>';
			}

			$html .= '<tr>
				<td>' . $product . '</td>
				<td>' . $roworderinfo->comment . '</td>
				<td class="text-right">' . number_format($roworderinfo->unitprice, 2) . '</td>
				<td class="text-right">' . $lastGrnPriceDisplay . '</td>
				<td class="text-right">' . $roworderinfo->qty . '</td>
				<td class="text-center">' . $roworderinfo->unit_name . '</td>
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

		$this->db->select('tbl_porder.*,tbl_supplier.suppliername AS suppliername,tbl_supplier.telephone_no AS suppliercontact,tbl_supplier.address_line1 AS address1,tbl_supplier.address_line2 AS address2,tbl_supplier.city AS city,tbl_supplier.state AS supplierstate,
								tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
								tbl_company.phone companyphone,tbl_company.email AS companyemail,
								tbl_company_branch.branch AS branchname');
		$this->db->from('tbl_porder');
		$this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_porder.tbl_supplier_idtbl_supplier', 'left');
		$this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_porder.tbl_company_idtbl_company', 'left');
		$this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_porder.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->where('tbl_porder.idtbl_porder', $recordID);
		$this->db->where_in('tbl_porder.status', array(1, 2));

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

		$data=array(
			'confirmstatus'=> $confirmnot,
			'approve_by'=> $userID,
			'cp_status'=> $cpstatus ? 1 : 0,
			'updatedatetime'=> $updatedatetime);

		$this->db->where('idtbl_porder', $recordID);
		$this->db->update('tbl_porder', $data);

		$data1 = array(
			'porderconfirm' => '1',
			'updateuser'=> $userID,
			'updatedatetime'=> $updatedatetime
		);

		$this->db->where('idtbl_porder_req', $reqid);
		$this->db->update('tbl_porder_req', $data1);

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
			'updateuser'=> $userID,
			'updatedatetime'=> $updatedatetime
		);

		$this->db->where('idtbl_porder', $recordID);
		$this->db->update('tbl_porder', $data);

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

	public function Getporderreqdetails() {
		$recordID = $this->input->post('recordID');

		$this->db->select('tbl_product.product_name AS requestname, tbl_porder_req_detail.qty, tbl_porder_req_detail.comment,
			tbl_porder_req_detail.tbl_product_idtbl_product AS product_id,
			COALESCE(NULLIF(tbl_unit.unit_short, ""), tbl_unit.unit) AS measure_type', FALSE);
		$this->db->from('tbl_porder_req_detail');
		$this->db->join('tbl_product', 'tbl_product.idtbl_product = tbl_porder_req_detail.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit', 'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');
		$this->db->where('tbl_porder_req_detail.status', 1);
		$this->db->where('tbl_porder_req_detail.tbl_porder_req_idtbl_porder_req', $recordID);

		$response = $this->db->get();

		$result = [];
		foreach ($response->result() as $row) {
			$result[] = [
				'requestname'  => $row->requestname,
				'qty'          => $row->qty,
				'measure_type' => $row->measure_type,
				'comment'      => $row->comment,
				'grnhistory'   => $this->getLastGRNHistory($row->product_id)
			];
		}
		echo json_encode($result);
	}

	public function Purchaseorderedit()
	{
		$recordID  = $this->input->post('recordID');
		$companyID = $_SESSION['company_id'];

		$this->db->select([
			'tbl_porder.idtbl_porder AS porder_id',
			'tbl_porder.tbl_porder_req_idtbl_porder_req AS request_id',
			'tbl_porder.orderdate',
			'tbl_porder.remark',
			'tbl_porder.tbl_supplier_idtbl_supplier AS supplier_id',

			'tbl_porder_detail.unitprice AS detail_unitprice',
			'tbl_porder_detail.comment',
			'tbl_porder_detail.netprice',
			'tbl_porder_detail.qty',

			'tbl_product.idtbl_product AS product_id',
			'tbl_product.product_name',
			'COALESCE(NULLIF(tbl_unit.unit_short, ""), tbl_unit.unit) AS unit_name'
		], FALSE);

		$this->db->from('tbl_porder');
		$this->db->join('tbl_porder_detail',
			'tbl_porder.idtbl_porder = tbl_porder_detail.tbl_porder_idtbl_porder AND tbl_porder_detail.status = 1',
			'left'
		);
		$this->db->join('tbl_product',
			'tbl_product.idtbl_product = tbl_porder_detail.tbl_product_idtbl_product',
			'left'
		);
		$this->db->join('tbl_unit',
			'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit',
			'left'
		);

		$this->db->where('tbl_porder.idtbl_porder', $recordID);
		$this->db->where('tbl_porder.tbl_company_idtbl_company', $companyID);
		$this->db->where_in('tbl_porder.status', array(1, 2));

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
		$obj->remark    = $row0->remark;

		$items = [];
		foreach ($respond->result() as $row) {
			if (empty($row->product_id)) { continue; }
			$item = new stdClass();
			$item->unitprice   = $row->detail_unitprice;
			$item->comment     = $row->comment;
			$item->unit        = $row->unit_name;
			$item->materialID  = $row->product_id;
			$item->material    = $row->product_name;
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

		if(is_array($tableData) && !empty($tableData)){
			$orderdate=$this->input->post('orderdate');
			$grosstotal=(float)$this->input->post('grosstotal');
			$remark=$this->input->post('remark');
			$supplier=$this->input->post('supplier');
			$company_id=$this->input->post('company_id');
			$branch_id=$this->input->post('branch_id');
			$porderID=$this->input->post('porderID');
			$porderreqID=(int)$this->input->post('porderreqID');
			$updatedatetime=date('Y-m-d H:i:s');

			$data=array(
				'orderdate'=> $orderdate,
				'subtotal'=> $grosstotal,
				'vattotamount'=> 0,
				'discountamount'=> 0,
				'nettotal'=> $grosstotal,
				'confirmstatus'=> 0,
				'grnconfirm'=> 0,
				'remark'=> (string)$remark,
				'status'=> 1,
				'updatedatetime'=> $updatedatetime,
				'updateuser'=> $userID,
				'tbl_supplier_idtbl_supplier'=> $supplier,
				'tbl_company_idtbl_company'=> $company_id,
				'tbl_company_branch_idtbl_company_branch'=> $branch_id,
				'tbl_porder_req_idtbl_porder_req'=> $porderreqID
			);

			$this->db->where('idtbl_porder', $porderID);
			$this->db->update('tbl_porder', $data);

			$this->db->where('tbl_porder_idtbl_porder', $porderID);
			$this->db->delete('tbl_porder_detail');

			foreach ($tableData as $rowtabledata) {
				$productID=$rowtabledata['col_2'];
				$qty=$rowtabledata['col_3'];
				$unit=$rowtabledata['col_5'];
				$comment=$rowtabledata['col_6'];
				$nettotal=$rowtabledata['col_7'];

				$dataone=array(
					'qty'=> $qty,
					'unitprice'=> $unit,
					'discount'=> 0,
					'vat'=> 0,
					'vatamount'=> 0,
					'grossprice'=> $nettotal,
					'netprice'=> $nettotal,
					'comment'=> (string)$comment,
					'status'=> 1,
					'insertdatetime'=> $updatedatetime,
					'updatedatetime'=> $updatedatetime,
					'updateuser'=> $userID,
					'tbl_porder_idtbl_porder'=> $porderID,
					'tbl_product_idtbl_product'=> $productID,
					'tbl_user_idtbl_user'=> $userID
				);

				$this->db->insert('tbl_porder_detail', $dataone);
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

		$data=array(
			'check_by'=> $userID);

		$this->db->where('idtbl_porder', $recordID);
		$this->db->update('tbl_porder', $data);

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