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

		$this->db->select('grntype, batchno, vat, vat_type');
		$this->db->from('tbl_grn');
		$this->db->where('status', 1);
		$this->db->where('idtbl_grn', $recordID);

		$respond = $this->db->get();

		$obj = new stdClass();
		$obj->grnType  = $respond->num_rows() > 0 ? $respond->row(0)->grntype  : '';
		$obj->batchNo  = $respond->num_rows() > 0 ? $respond->row(0)->batchno  : '';
		$obj->vat      = $respond->num_rows() > 0 ? floatval($respond->row(0)->vat) : 0;
		$obj->vatType  = $respond->num_rows() > 0 ? $respond->row(0)->vat_type : 2;

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
		$error = '';

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

		foreach ($tableData as $rowtabledata) {
			$product           = $rowtabledata['col_2'];
			$orderedQty        = $rowtabledata['col_3'];
			$availableStockQty = $rowtabledata['col_4'];
			$returnQty         = $rowtabledata['col_5'];
			$unitPrice         = $rowtabledata['col_6'];
			$uom               = $rowtabledata['col_8'];
			$unitDiscount      = $rowtabledata['col_9'];
			$comment           = $rowtabledata['col_10'];
			$total             = $rowtabledata['col_12'];
			$serialIDs         = array_filter(array_map('trim', explode(',', $rowtabledata['col_13'])));

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
			$detailID = $this->db->insert_id();

			if (!empty($serialIDs)) {
				if (count($serialIDs) != floatval($returnQty)) {
					$error = 'Selected serial numbers must match the return qty';
					break;
				}

				foreach ($serialIDs as $serialID) {
					$this->db->from('tbl_product_serial s');
					$this->db->where('s.idtbl_product_serial', $serialID);
					$this->db->where('s.tbl_grn_idtbl_grn', $grnNo);
					$this->db->where('s.tbl_product_idtbl_product', $product);
					$this->db->where('s.stock_status', 1);
					$this->db->where('s.status', 1);
					$this->db->where('s.idtbl_product_serial NOT IN (
						SELECT rs.tbl_product_serial_idtbl_product_serial
						FROM tbl_grn_return_serial rs
						JOIN tbl_grn_return r ON r.idtbl_grn_return = rs.tbl_grn_return_idtbl_grn_return
						WHERE rs.status = 1 AND r.status = 1 AND r.approvestatus = 0)', NULL, FALSE);

					if ($this->db->count_all_results() == 0) {
						$error = 'One or more serial numbers are not available for return';
						break 2;
					}

					$this->db->insert('tbl_grn_return_serial', array(
						'tbl_grn_return_idtbl_grn_return'               => $grnReturnID,
						'tbl_grn_return_detail_idtbl_grn_return_detail' => $detailID,
						'tbl_product_serial_idtbl_product_serial'       => $serialID,
						'status'         => 1,
						'insertdatetime' => $updatedatetime
					));
				}
			}
		}

		if ($error == '' && $this->db->trans_status() === TRUE) {
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
			$actionObj->message = ($error != '') ? $error : 'Record Error';
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

		$row = $respond->row(0);

		$date   = isset($row->updatedatetime) ? date('Y-m-d', strtotime($row->updatedatetime)) : date('Y-m-d');
		$remark = isset($row->remark) ? $row->remark : '';

		$html = '
		<!-- Print-only company header -->
		<div class="doc-header print-only">
			<div>
				<div class="company">Spencersz (Pvt) Ltd</div>
			</div>
			<div class="doc-title">Goods Receive Return Note</div>
		</div>

		<!-- Info grid -->
		<table class="info-grid" style="width:100%; font-size:13px;">
			<tr>
				<td><b>Date:</b> '.$date.'</td>
				<td><b>Company:</b> Spencersz (Pvt) Ltd</td>
			</tr>
			<tr>
				<td><b>Supplier:</b> '.$row->suppliername.'</td>
				<td><b>Batch No:</b> '.$row->batchno.'</td>
			</tr>
		</table>
		<hr style="border-top:1px solid #000;">

		<table class="table table-bordered table-sm items">
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

		foreach ($responddetails->result() as $d) {
			$label = $d->product_name;
			if (!empty($d->product_code)) { $label .= ' / '.$d->product_code; }

			$this->db->select('ps.serialno');
			$this->db->from('tbl_grn_return_serial rs');
			$this->db->join('tbl_product_serial ps', 'ps.idtbl_product_serial = rs.tbl_product_serial_idtbl_product_serial', 'left');
			$this->db->where('rs.tbl_grn_return_detail_idtbl_grn_return_detail', $d->idtbl_grn_return_detail);
			$this->db->where('rs.status', 1);
			$sn = array_column($this->db->get()->result_array(), 'serialno');

			if (!empty($sn)) {
				$label .= '<br><small>SN: ' . implode(', ', $sn) . '</small>';
			}

			$html .= '<tr>
				<td>'.$label.'</td>
				<td class="text-right">'.number_format($d->unit_price, 2).'</td>
				<td class="text-right">'.$d->return_qty.'</td>
				<td class="text-center">'.$d->unit.'</td>
				<td class="text-right">'.number_format($d->unit_discount, 2).'</td>
				<td>'.$d->comment.'</td>
				<td class="text-right">'.number_format($d->total, 2).'</td>
			</tr>';
		}

		$html .= '</tbody></table>

		<table class="totals" style="width:100%; margin-top:10px;">
			<tr>
				<td style="text-align:right; font-weight:bold; padding:5px; width:80%;">Discount</td>
				<td style="text-align:right; font-weight:bold; padding:5px; width:20%;">Rs. '.number_format($row->discount, 2).'</td>
			</tr>
			<tr>
				<td style="text-align:right; font-weight:bold; padding:5px;">Sub Total</td>
				<td style="text-align:right; font-weight:bold; padding:5px;">Rs. '.number_format($row->subtotal, 2).'</td>
			</tr>
			<tr>
				<td style="text-align:right; font-weight:bold; padding:5px;">Vat ('.floatval($row->vat).'%)</td>
				<td style="text-align:right; font-weight:bold; padding:5px;">Rs. '.number_format(($row->subtotal * $row->vat) / 100, 2).'</td>
			</tr>
			<tr class="final">
				<td style="text-align:right; font-weight:bold; padding:5px; font-size:18px;">Final Price</td>
				<td style="text-align:right; font-weight:bold; padding:5px; font-size:18px;">Rs. '.number_format($row->totalpayment, 2).'</td>
			</tr>
		</table>';

		if (!empty($remark)) {
			$html .= '<p style="margin-top:10px;"><b>Remark:</b> '.$remark.'</p>';
		}

		$html .= '
		<div class="signatures print-only">
			<div>Prepared By</div>
			<div>Checked By</div>
			<div>Approved By</div>
		</div>
		<div class="footer-note print-only">This is a system generated document.</div>';

		echo $html;
	}

	public function Goodreceivereturnstatus($x, $y) {
		$this->db->trans_begin();

		$userID  = $_SESSION['userid'];
		$branchID=$_SESSION['branch_id'];
		$recordID = $x;
		$type    = $y;
		$updatedatetime = date('Y-m-d H:i:s');

		if ($type == 1) {
			$error = '';

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

			$this->db->select('idtbl_grn_return_detail, return_qty, tbl_product_idtbl_product');
			$this->db->from('tbl_grn_return_detail');
			$this->db->where('tbl_grn_return_idtbl_grn_return', $recordID);
			$this->db->where('status', 1);
			$responddetails = $this->db->get();

			foreach ($responddetails->result() as $rowdetail) {
				$return_qty = $rowdetail->return_qty;
				$product_id = $rowdetail->tbl_product_idtbl_product;
				$detailID   = $rowdetail->idtbl_grn_return_detail;

				// existing stock deduction
				$this->db->set('qty', 'qty-'.$return_qty, FALSE);
				$this->db->where('tbl_product_idtbl_product', $product_id);
				$this->db->where('batchno', $batchno);
				$this->db->update('tbl_stock');

				// serials selected for this line
				$this->db->select('rs.tbl_product_serial_idtbl_product_serial AS serial_id, ps.serialno');
				$this->db->from('tbl_grn_return_serial rs');
				$this->db->join('tbl_product_serial ps', 'ps.idtbl_product_serial = rs.tbl_product_serial_idtbl_product_serial', 'left');
				$this->db->where('rs.tbl_grn_return_detail_idtbl_grn_return_detail', $detailID);
				$this->db->where('rs.status', 1);
				$serials = $this->db->get()->result();

				foreach ($serials as $s) {
					// 1 = in stock  ->  4 = returned to supplier
					$this->db->where('idtbl_product_serial', $s->serial_id);
					$this->db->where('stock_status', 1);
					$this->db->where('status', 1);
					$this->db->update('tbl_product_serial', array(
						'stock_status'   => 4,
						'updatedatetime' => $updatedatetime,
						'updateuser'     => $userID
					));

					if ($this->db->affected_rows() != 1) {
						$error = 'Serial number ' . $s->serialno . ' is not in stock';
						break 2;
					}

					$this->db->insert('tbl_serial_movement', array(
						'movement_type' => 4,
						'from_status'   => 1,
						'to_status'     => 4,
						'doc_type'      => 'GRN_RETURN',
						'doc_id'        => $recordID,
						'doc_detail_id' => $detailID,
						'branch_id' => $branchID,
						'remark'        => 'Returned to supplier via GRN Return #' . $recordID,
						'insertdatetime'=> $updatedatetime,
						'tbl_user_idtbl_user' => $userID,
						'tbl_product_serial_idtbl_product_serial' => $s->serial_id
					));
				}
			}

			if ($error == '') {
				$this->db->trans_complete();
			}

			if ($error == '' && $this->db->trans_status() === TRUE) {
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
				$actionObj->message = ($error != '') ? $error : 'Record Error';
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

	public function Getvatpresentage() {
		$date = $this->input->post('currentDate');
		if (empty($date) || !strtotime($date)) {
			$date = date('Y-m-d');
		}
		$date = date('Y-m-d', strtotime($date));

		echo json_encode($this->Fetchvatpercentage($date));
	}

	public function Fetchvatpercentage($date) {
		$this->db->select('percentage');
		$this->db->from('tbl_tax_control');
		$this->db->where('status', 1);
		$this->db->where('effective_from <=', $date);
		$this->db->group_start();
			$this->db->where('effective_to IS NULL', null, false);
			$this->db->or_where('effective_to', '0000-00-00');
			$this->db->or_where('effective_to >=', $date);
		$this->db->group_end();
		$this->db->order_by('effective_from', 'DESC');
		$this->db->limit(1);

		$query = $this->db->get();

		return $query->num_rows() > 0 ? floatval($query->row()->percentage) : 0;
	}

	public function Getserialsaccoproduct() {
		$productID = $this->input->post('productID');
		$grnNo     = $this->input->post('grnNo');

		$this->db->select('s.idtbl_product_serial, s.serialno');
		$this->db->from('tbl_product_serial s');
		$this->db->where('s.tbl_grn_idtbl_grn', $grnNo);
		$this->db->where('s.tbl_product_idtbl_product', $productID);
		$this->db->where('s.stock_status', 1);
		$this->db->where('s.status', 1);
		$this->db->where('s.idtbl_product_serial NOT IN (
			SELECT rs.tbl_product_serial_idtbl_product_serial
			FROM tbl_grn_return_serial rs
			JOIN tbl_grn_return r ON r.idtbl_grn_return = rs.tbl_grn_return_idtbl_grn_return
			WHERE rs.status = 1 AND r.status = 1 AND r.approvestatus = 0)', NULL, FALSE);
		$this->db->order_by('s.serialno', 'ASC');

		echo json_encode($this->db->get()->result());
	}
}