<?php class Goodreceivereturninfo extends CI_Model {

	public function Getsupplier() {
		$companyID = $_SESSION['company_id'];

		$this->db->select('`idtbl_supplier`, `suppliername`');
		$this->db->from('tbl_supplier');
		$this->db->where('status', 1);
		$this->db->where('tbl_company_idtbl_company', $companyID);

		return $this->db->get();
	}

	public function Getgrnaccsupllier() {
		$recordID  = $this->input->post('recordID');
		$companyID = $_SESSION['company_id'];
		$branchID  = $_SESSION['branch_id'];

		$this->db->select('idtbl_grn, grn_no');
		$this->db->from('tbl_grn');
		$this->db->where('status', 1);
		$this->db->where('approvestatus', 1);
		$this->db->where('tbl_supplier_idtbl_supplier', $recordID);
		$this->db->where('tbl_company_idtbl_company', $companyID);
		$this->db->where('tbl_company_branch_idtbl_company_branch', $branchID);

		$respond = $this->db->get();

		echo json_encode($respond->result());
	}

	public function Getmeasuretype() {
		$this->db->select('`idtbl_unit`, `unit`');
		$this->db->from('tbl_unit');
		$this->db->where('status', 1);

		return $this->db->get();
	}

	public function Getordertypesetgrn(){
		$recordID = $this->input->post('recordID');

		$this->db->select('grntype, batchno');
		$this->db->from('tbl_grn');
		$this->db->where('status', 1);
		$this->db->where('idtbl_grn', $recordID);

		$respond = $this->db->get();

		$obj = new stdClass();
		$obj->grnType = $respond->num_rows() > 0 ? $respond->row(0)->grntype : '';
		$obj->batchNo = $respond->num_rows() > 0 ? $respond->row(0)->batchno : '';

		echo json_encode($obj);
	}

	public function Getproducts(){
		$grnNo = $this->input->post('grnNo');

		$this->db->select('ua.idtbl_product AS id, ua.product_name AS name');
		$this->db->from('tbl_grndetail AS u');
		$this->db->join('tbl_product AS ua', 'ua.idtbl_product = u.tbl_product_idtbl_product', 'left');
		$this->db->where('u.tbl_grn_idtbl_grn', $grnNo);
		$this->db->where('u.status', 1);

		$respond = $this->db->get()->result();

		echo json_encode($respond);
	}

	public function Getproductdetails(){
		$productID = $this->input->post('productID');
		$batchNo   = $this->input->post('batchNo');
		$grnNo     = $this->input->post('grnNo');

		$this->db->select('qty');
		$this->db->from('tbl_grndetail');
		$this->db->where('tbl_product_idtbl_product', $productID);
		$this->db->where('tbl_grn_idtbl_grn', $grnNo);
		$this->db->where('status', 1);
		$respondgrn = $this->db->get();

		$this->db->select('qty, costunitprice');
		$this->db->from('tbl_stock');
		$this->db->where('tbl_product_idtbl_product', $productID);
		$this->db->where('batchno', $batchNo);
		$this->db->where('status', 1);
		$respondstock = $this->db->get();

		// get UOM from tbl_product -> tbl_unit
		$this->db->select('tbl_unit_idtbl_unit');
		$this->db->from('tbl_product');
		$this->db->where('idtbl_product', $productID);
		$productQuery = $this->db->get();
		$uom = $productQuery->num_rows() > 0 ? $productQuery->row()->tbl_unit_idtbl_unit : '';

		$obj = new stdClass();
		$obj->orderedQty  = $respondgrn->num_rows()   > 0 ? $respondgrn->row(0)->qty           : 0;
		$obj->stockQty    = $respondstock->num_rows() > 0 ? $respondstock->row(0)->qty         : 0;
		$obj->measureType = $uom;
		$obj->unitPrice   = $respondstock->num_rows() > 0 ? $respondstock->row(0)->costunitprice : 0;

		echo json_encode($obj);
	}

	public function Goodreceivereturninsertupdate() {
		$this->db->trans_begin();

		$userID    = $_SESSION['userid'];
		$companyID = $_SESSION['company_id'];
		$branchID  = $_SESSION['branch_id'];

		$tableData = $this->input->post('tableData');

		$supplier     = $this->input->post('supplier');
		$grnNo        = $this->input->post('grnNo');
		$grnType      = $this->input->post('grnType');
		$batchNo      = $this->input->post('batchNo');
		$discount     = $this->input->post('discount');
		$subTotal     = $this->input->post('subTotal');
		$vat          = $this->input->post('vat');
		$totalPayment = $this->input->post('totalPayment');
		$remark       = $this->input->post('remark');

		$updatedatetime = date('Y-m-d H:i:s');

		$data = array(
			'batchno'      => $batchNo,
			'grn_no'       => $grnNo,
			'grn_type'     => $grnType,
			'discount'     => $discount,
			'subtotal'     => $subTotal,
			'vat'          => $vat,
			'tbl_company_idtbl_company'               => $companyID,
			'tbl_company_branch_idtbl_company_branch' => $branchID,
			'totalpayment' => $totalPayment,
			'remark'       => $remark,
			'approvestatus'=> '0',
			'status'       => '1',
			'insertdatetime'=> $updatedatetime,
			'tbl_user_idtbl_user'         => $userID,
			'tbl_supplier_idtbl_supplier' => $supplier
		);

		$this->db->insert('tbl_grn_return', $data);

		$grnReturnID = $this->db->insert_id();

		foreach($tableData as $rowtabledata) {
			$product           = $rowtabledata['col_2'];
			$orderedQty        = $rowtabledata['col_3'];
			$availableStockQty = $rowtabledata['col_4'];
			$returnQty         = $rowtabledata['col_5'];
			$unitPrice         = $rowtabledata['col_6'];
			$uom               = $rowtabledata['col_8'];
			$unitDiscount      = $rowtabledata['col_9'];
			$comment           = $rowtabledata['col_10'];
			$total             = $rowtabledata['col_12'];

			$datadetail = array(
				'ordered_qty'        => $orderedQty,
				'avalible_stock_qty' => $availableStockQty,
				'return_qty'         => $returnQty,
				'unit_price'         => $unitPrice,
				'unit_discount'      => $unitDiscount,
				'comment'            => $comment,
				'total'              => $total,
				'status'             => '1',
				'insertdatetime'     => $updatedatetime,
				'tbl_product_idtbl_product' => $product,
				'tbl_user_idtbl_user'       => $userID,
				'tbl_grn_idtbl_grn'         => $grnNo,
				'tbl_grn_return_idtbl_grn_return' => $grnReturnID
			);

			$this->db->insert('tbl_grn_return_detail', $datadetail);
		}

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			$actionObj = new stdClass();
			$actionObj->icon    = 'fas fa-save';
			$actionObj->title   = '';
			$actionObj->message = 'Record Added Successfully';
			$actionObj->url     = '';
			$actionObj->target  = '_blank';
			$actionObj->type    = 'success';

			$obj = new stdClass();
			$obj->status = 1;
			$obj->action = json_encode($actionObj);

			echo json_encode($obj);
		} else {
			$this->db->trans_rollback();

			$actionObj = new stdClass();
			$actionObj->icon    = 'fas fa-exclamation-triangle';
			$actionObj->title   = '';
			$actionObj->message = 'Record Error';
			$actionObj->url     = '';
			$actionObj->target  = '_blank';
			$actionObj->type    = 'danger';

			$obj = new stdClass();
			$obj->status = 0;
			$obj->action = json_encode($actionObj);

			echo json_encode($obj);
		}
	}

	public function Goodreceivereturnview() {
		$recordID = $this->input->post('recordID');

		$this->db->select('u.*, ua.suppliername AS suppliername');
		$this->db->from('tbl_grn_return AS u');
		$this->db->join('tbl_supplier AS ua', 'ua.idtbl_supplier = u.tbl_supplier_idtbl_supplier', 'left');
		$this->db->where('u.idtbl_grn_return', $recordID);
		$this->db->where('u.status', 1);

		$respond = $this->db->get();

		$this->db->select('u.*, ua.product_name, ua.product_code, ud.unit');
		$this->db->from('tbl_grn_return_detail AS u');
		$this->db->join('tbl_product AS ua', 'ua.idtbl_product = u.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit AS ud', 'ud.idtbl_unit = ua.tbl_unit_idtbl_unit', 'left');
		$this->db->where('u.tbl_grn_return_idtbl_grn_return', $recordID);
		$this->db->where('u.status', 1);

		$responddetails = $this->db->get();

		$html = '
			<div class="row">
				<div class="col-12 text-right" style="font-family: cursive;font-size:15px; font-weight: bold;">'.$respond->row(0)->suppliername.'</div>
				<div class="col-12"><hr>
					<h6>Batch No: '.$respond->row(0)->batchno.'</h6>
				</div>
			</div>
			<div class="row">
				<div class="col-12"><hr>
				<table class="table table-striped table-bordered table-sm">
					<thead>
						<tr>
							<th>Product</th>
							<th class="text-right">Unit Price</th>
							<th class="text-right">Return Qty</th>
							<th class="text-center">Uom</th>
							<th class="text-right">Discount</th>
							<th>Comment</th>
							<th class="text-right">Total</th>
						</tr>
					</thead>
					<tbody>';

		foreach($responddetails->result() as $rowdetails) {
			$materialLabel = $rowdetails->product_name;
			if (!empty($rowdetails->product_code)) {
				$materialLabel .= ' / ' . $rowdetails->product_code;
			}

			$html .= '<tr>
						<td>'.$materialLabel.'</td>
						<td class="text-right">'.number_format(($rowdetails->unit_price), 2).'</td>
						<td class="text-right">'.$rowdetails->return_qty.'</td>
						<td class="text-center">'.$rowdetails->unit.'</td>
						<td class="text-right">'.number_format(($rowdetails->unit_discount), 2).'</td>
						<td>'.$rowdetails->comment.'</td>
						<td class="text-right">'.number_format(($rowdetails->total), 2).'</td>
					</tr>';
		}

		$html .= '</tbody>
				</table>
				<table border="0" width="100%" style="border-collapse: collapse;">
					<tbody>
						<tr>
							<td width="80%" style="text-align: right; font-weight: bold; padding: 5px;">Discount</td>
							<td width="20%" style="text-align: right; font-weight: bold; padding: 5px;">Rs. ' . number_format(($respond->row(0)->discount), 2) . '</td>
						</tr>
						<tr>
							<td width="80%" style="text-align: right; font-weight: bold; padding: 5px;">Sub Total</td>
							<td width="20%" style="text-align: right; font-weight: bold; padding: 5px;">Rs. ' . number_format(($respond->row(0)->subtotal), 2) . '</td>
						</tr>
						<tr>
							<td width="80%" style="text-align: right; font-weight: bold; padding: 5px;">Vat(%)</td>
							<td width="20%" style="text-align: right; font-weight: bold; padding: 5px;">' . $respond->row(0)->vat . '%</td>
						</tr>
						<tr>
							<td width="80%" style="text-align: right; font-weight: bold; padding: 5px;"><strong><span style="color: black; font-size: 18px;">Final Price</span></strong></td>
							<td width="20%" style="text-align: right; font-weight: bold; padding: 5px;"><span style="color: black; font-size: 18px;">Rs. ' . number_format(($respond->row(0)->totalpayment), 2) . '</span></td>
						</tr>
					</tbody>
				</table>
			</div>';

		echo $html;
	}

	public function Goodreceivereturnstatus($x, $y) {
		$this->db->trans_begin();

		$userID  = $_SESSION['userid'];
		$recordID = $x;
		$type    = $y;
		$updatedatetime = date('Y-m-d H:i:s');

		if ($type == 1) {
			$data = array(
				'approvestatus' => '1',
				'updateuser'    => $userID,
				'updatedatetime'=> $updatedatetime
			);

			$this->db->where('idtbl_grn_return', $recordID);
			$this->db->update('tbl_grn_return', $data);

			$this->db->select('batchno, tbl_supplier_idtbl_supplier');
			$this->db->from('tbl_grn_return');
			$this->db->where('idtbl_grn_return', $recordID);
			$this->db->where('status', 1);

			$respond = $this->db->get();

			$batchno  = $respond->row(0)->batchno;
			$supplier = $respond->row(0)->tbl_supplier_idtbl_supplier;

			$this->db->select('return_qty, tbl_product_idtbl_product');
			$this->db->from('tbl_grn_return_detail');
			$this->db->where('tbl_grn_return_idtbl_grn_return', $recordID);
			$this->db->where('status', 1);

			$responddetails = $this->db->get();

			foreach($responddetails->result() as $rowdetail) {
				$return_qty  = $rowdetail->return_qty;
				$product_id  = $rowdetail->tbl_product_idtbl_product;

				$this->db->set('qty', 'qty-'.$return_qty, FALSE);
				$this->db->where('tbl_product_idtbl_product', $product_id);
				$this->db->where('batchno', $batchno);
				$this->db->update('tbl_stock');
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === TRUE) {
				$this->db->trans_commit();

				$actionObj = new stdClass();
				$actionObj->icon    = 'fas fa-check';
				$actionObj->title   = '';
				$actionObj->message = 'Approved Successfully';
				$actionObj->url     = '';
				$actionObj->target  = '_blank';
				$actionObj->type    = 'success';

				$this->session->set_flashdata('msg', json_encode($actionObj));
				redirect('Goodreceivereturn');
			} else {
				$this->db->trans_rollback();

				$actionObj = new stdClass();
				$actionObj->icon    = 'fas fa-warning';
				$actionObj->title   = '';
				$actionObj->message = 'Record Error';
				$actionObj->url     = '';
				$actionObj->target  = '_blank';
				$actionObj->type    = 'danger';

				$this->session->set_flashdata('msg', json_encode($actionObj));
				redirect('Goodreceivereturn');
			}
		}
		else if ($type == 3) {
			$data = array(
				'status'         => '3',
				'updateuser'     => $userID,
				'updatedatetime' => $updatedatetime
			);

			$this->db->where('idtbl_grn_return', $recordID);
			$this->db->update('tbl_grn_return', $data);

			$this->db->trans_complete();

			if ($this->db->trans_status() === TRUE) {
				$this->db->trans_commit();

				$actionObj = new stdClass();
				$actionObj->icon    = 'fas fa-trash-alt';
				$actionObj->title   = '';
				$actionObj->message = 'Reject Successfully';
				$actionObj->url     = '';
				$actionObj->target  = '_blank';
				$actionObj->type    = 'danger';

				$this->session->set_flashdata('msg', json_encode($actionObj));
				redirect('Goodreceivereturn');
			} else {
				$this->db->trans_rollback();

				$actionObj = new stdClass();
				$actionObj->icon    = 'fas fa-warning';
				$actionObj->title   = '';
				$actionObj->message = 'Record Error';
				$actionObj->url     = '';
				$actionObj->target  = '_blank';
				$actionObj->type    = 'danger';

				$this->session->set_flashdata('msg', json_encode($actionObj));
				redirect('Goodreceivereturn');
			}
		}
	}
}