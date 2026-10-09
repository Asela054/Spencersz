<?php class Goodreceiveinfo extends CI_Model {

	public function Getlocation() {
		$this->db->select('`idtbl_location`, `location`');
		$this->db->from('tbl_location');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getcompany() {
		$this->db->select('`idtbl_company`, `company`');
		$this->db->from('tbl_company');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getcompanybranch() {
		$this->db->select('`idtbl_company_branch`, `branch`');
		$this->db->from('tbl_company_branch');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}

	public function Getmeasuretype() {
		$this->db->select('`idtbl_unit`, `unit`, `unit_short`');
		$this->db->from('tbl_unit');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}
	
	public function Getporder($searchTerm = null) {
		$companyID = $_SESSION['company_id'];

		$this->db->select('idtbl_porder, porder_no');
		$this->db->from('tbl_porder');
		$this->db->where('status', 1);
		$this->db->where('confirmstatus', 1);
		$this->db->where_in('grnconfirm', [0, 2]);
		$this->db->where('tbl_company_idtbl_company', $companyID);
		$this->db->order_by('idtbl_porder', 'DESC');
		$this->db->limit(5);

		if (!empty($searchTerm)) {
			$this->db->like('porder_no', $searchTerm, 'both');
		}

		return $this->db->get();
	}

	public function Getproductaccosupplier() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`idtbl_product`, `product_code`, `product_name`');
		$this->db->from('tbl_product');
		$this->db->where('status', 1);

		$respond=$this->db->get();

		echo json_encode($respond->result());
	}

	public function Getgoodreceiveid() {
		$recordID=$this->input->post('recordID');

		$sql="SELECT `idtbl_grn` FROM `tbl_grn` WHERE `idtbl_grn`=? AND `status`=1";
		$respond=$this->db->query($sql, array($recordID));

		echo json_encode($respond->result());
	}

	public function Goodreceiveinsertupdate() {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];
		$companyID=$_SESSION['company_id'];

		$tableData=$this->input->post('tableData');
		$grndate=$this->input->post('grndate');
		$total=$this->input->post('total');
		$vatamount=$this->input->post('vatamount');
		$remark=$this->input->post('remark');
		$supplier=$this->input->post('supplier');
		$location=$this->input->post('location');
		$warehouse=$this->input->post('warehouse');
		$company_id=$this->input->post('company_id');
		$branch_id=$this->input->post('branch_id');
		$porder=$this->input->post('porder');
		$batchno=$this->input->post('batchno');
		$invoice=$this->input->post('invoice');
		$discount=$this->input->post('discount');
		$vat_type=$this->input->post('vat_type');
		$subtotal=$this->input->post('subtotal');
		$vat=$this->input->post('vat');

		$updatedatetime=date('Y-m-d H:i:s');

		$data=array(
			'batchno'=> $batchno,
			'grndate'=> $grndate,
			'total'=> $total,
			'invoicenum'=> $invoice,
			'discount'=> $discount,
			'approvestatus'=> '0',
			'subtotal'=> $subtotal, 
			'vatamount'=> $vatamount, 
			'remark'=> $remark, 
			'vat'=> $vat, 
			'vat_type'=> $vat_type, 
			'tbl_company_idtbl_company'=> $company_id, 
			'tbl_company_branch_idtbl_company_branch'=> $branch_id, 
			'subtotalcost'=> $subtotal, 
			'discountcost'=> $discount, 
			'vatamountcost'=> $vatamount, 
			'totalcost'=> $total, 
			'status'=> '1',
			'insertdatetime'=> $updatedatetime,
			'tbl_user_idtbl_user'=> $userID,
			'tbl_supplier_idtbl_supplier'=> $supplier,
			'tbl_location_idtbl_location'=> $location,
			'tbl_porder_idtbl_porder'=> $porder);

		$this->db->insert('tbl_grn', $data);

		$grnID=$this->db->insert_id();

			foreach($tableData as $rowtabledata) {
				$materialname=$rowtabledata['col_1'];
				$comment=$rowtabledata['col_2'];
				$materialID=$rowtabledata['col_3'];
				$unit=$rowtabledata['col_4'];
				$qty=$rowtabledata['col_5'];
				$uom=$rowtabledata['col_6'];
				$packetprice=$rowtabledata['col_7'];
				$unit_discount=$rowtabledata['col_8'];
				$uomID=$rowtabledata['col_9'];
				$nettotal=$rowtabledata['col_10'];
				$porderdetailsid=$rowtabledata['col_11'];

				$dataone=array('date'=> $grndate,
					'qty'=> $qty,
					'unitprice'=> $unit,
					'costunitprice'=> $unit,
					'total'=> $nettotal,
					'comment'=> $comment,
					'unit_discount'=> $unit_discount,
					'status'=> '1',
					'insertdatetime'=> $updatedatetime,
					'tbl_user_idtbl_user'=> $userID,
					'tbl_grn_idtbl_grn'=> $grnID,
					'tbl_product_idtbl_product'=> $materialID);

				$this->db->insert('tbl_grndetail', $dataone);
			}

		// Generate the GRN NO
		$currentYear = date("Y", strtotime($grndate));
		$currentMonth = date("m", strtotime($grndate));
	
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

		$this->db->select('grn_no');
		$this->db->from('tbl_grn');
		$this->db->where('tbl_company_idtbl_company', $companyID);
        $this->db->where("DATE(grndate) >=", $fromyear);
        $this->db->where("DATE(grndate) <=", $toyear);
		$this->db->order_by('grn_no', 'DESC');
		$this->db->limit(1);
		$respond = $this->db->get();
		
		if ($respond->num_rows() > 0) {
			$last_grn_no = $respond->row()->grn_no;
			$grn_number = intval(substr($last_grn_no, -4));
			$count = $grn_number;
		} else {
			$count = 0;
		}

		$count++; 
		$countPrefix = sprintf('%04d', $count);

		$yearDigit = substr(date("Y", strtotime($fromyear)), -2);

		$reqno = 'GRN' . $yearDigit . $countPrefix;

		$datadetail = array(
			'grn_no'=> $reqno, 
			'updatedatetime'=> $updatedatetime
		);

		$this->db->where('idtbl_grn', $grnID);
		$this->db->update('tbl_grn', $datadetail);

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

	public function Goodreceiveview() {
		$recordID=$this->input->post('recordID');

		$sql="SELECT `u`.*, `ua`.`suppliername`, `ua`.`telephone_no`, `ua`.`address_line1`, `ub`.`branch`, `ub`.`phone`, `ub`.`address1`, `ub`.`address2`, `ub`.`mobile`, `ub`.`email` AS `locemail`, `uc`.`company` FROM `tbl_grn` AS `u` LEFT JOIN `tbl_supplier` AS `ua` ON (`ua`.`idtbl_supplier` = `u`.`tbl_supplier_idtbl_supplier`) LEFT JOIN `tbl_company_branch` AS `ub` ON (`ub`.`idtbl_company_branch` = `u`.`tbl_company_branch_idtbl_company_branch`) LEFT JOIN `tbl_company` AS `uc` ON (`uc`.`idtbl_company` = `u`.`tbl_company_idtbl_company`) WHERE `u`.`status`=? AND `u`.`idtbl_grn`=?";
		$respond=$this->db->query($sql, array(1, $recordID));

		$this->db->select('tbl_grndetail.*,tbl_grn.invoicenum,tbl_grn.grndate,tbl_grn.grn_no, tbl_product.product_name, tbl_product.product_code, tbl_unit.unit');
		$this->db->from('tbl_grndetail');
		$this->db->join('tbl_product', 'tbl_product.idtbl_product = tbl_grndetail.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_grn', 'tbl_grn.idtbl_grn = tbl_grndetail.tbl_grn_idtbl_grn', 'left');
		$this->db->join('tbl_unit', 'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');
		$this->db->where('tbl_grndetail.tbl_grn_idtbl_grn', $recordID);
		$this->db->where('tbl_grndetail.status', 1);

		$responddetail=$this->db->get();

		$html='';

		$html.='
		<div class="row">
            <div class="col-6 small"><label class="small font-weight-bold text-dark mb-1">Date:</label> '.$responddetail->row(0)->grndate.'<br><label class="small font-weight-bold text-dark mb-1">GRN No:</label> '.$responddetail->row(0)->grn_no.'<br><label class="small font-weight-bold text-dark mb-1">Customer:</label> '.$respond->row(0)->suppliername.'</div>
            <div class="col-6 small"><label class="small font-weight-bold text-dark mb-1">Company:</label> '.$respond->row(0)->company.'<br><label class="small font-weight-bold text-dark mb-1">Branch:</label> '.$respond->row(0)->branch.'<br><label class="small font-weight-bold text-dark mb-1">Invoice No:</label> '.$respond->row(0)->invoicenum.'</div>
        </div>
        <hr class="border-dark"> <table class="table table-striped table-bordered table-sm"> <thead> <tr> <th>Product</th> <th>Unit Price</th> <th class="text-center">Qty</th><th class="text-center">Uom</th> <th class="text-center">Discount</th> <th class="text-right">Total</th> </tr> </thead> <tbody>';

		foreach($responddetail->result() as $roworderinfo) {
			$productInfo = $roworderinfo->product_name;
			if (!empty($roworderinfo->product_code)) {
				$productInfo .= ' / ' . $roworderinfo->product_code;
			}

			$html .= '<tr>
				<td>' . $productInfo . '</td>
				<td>' . $roworderinfo->costunitprice . '</td>
				<td class="text-center">' . $roworderinfo->qty . '</td>
				<td class="text-center">' . $roworderinfo->unit . '</td>
				<td class="text-center">' . $roworderinfo->unit_discount . '</td>
				<td class="text-right">' . number_format($roworderinfo->total, 2, '.', ',') . '</td>
			</tr>';					
		}

		$html .= '</tbody>
					</table>
				</div>
			</div>
			<!DOCTYPE html>
			<html lang="en">
			<head>
			<style>
				table { border-collapse: collapse; }
				td { padding: 5px; }
			</style>
			</head>
			<body>
			<table border="0" width="100%">
				<tbody>';

		$html .= '
			<tr>
				<td width="80%" style="text-align: right; font-weight: bold;">Discount</td>
				<td width="20%" style="text-align: right; font-weight: bold;">Rs. ' . number_format(($respond->row(0)->discountcost), 2) . '</td>
			</tr>
			<tr>
				<td width="80%" style="text-align: right; font-weight: bold;">Sub Total</td>
				<td width="20%" style="text-align: right; font-weight: bold;">Rs. ' . number_format(($respond->row(0)->subtotalcost), 2) . '</td>
			</tr>
			<tr>
				<td width="80%" style="text-align: right; font-weight: bold;">Vat (' . floatval($respond->row(0)->vat) . '%)</td>
				<td width="20%" style="text-align: right; font-weight: bold;">Rs. ' . number_format(($respond->row(0)->vatamount), 2) . '</td>
			</tr>
			<tr>
				<td width="80%" style="text-align: right; font-weight: bold;"><strong><span style="color: black; font-size: 18px;">Final Price</span></strong></td>
				<td width="20%" style="text-align: right; font-weight: bold;"><span style="color: black; font-size: 18px;">Rs. ' . number_format(($respond->row(0)->totalcost), 2) . '</span></td>
			</tr>
			</tbody>
			</table>
			<p><b>Remark:</b> ' . (!empty($respond->row(0)->remark) ? nl2br($respond->row(0)->remark) : '') . '</p>
			</body>
			</html>';

		$this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
			tbl_company.phone companyphone,tbl_company.email AS companyemail,
			tbl_company_branch.branch AS branchname');
		$this->db->from('tbl_grn');
		$this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn.tbl_company_idtbl_company', 'left');
		$this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->where('tbl_grn.idtbl_grn', $recordID);
		$companydetails = $this->db->get();

		$obj=new stdClass();
		$obj->companyname=$companydetails->row(0)->companyname;
		$obj->companyaddress=$companydetails->row(0)->companyaddress;
		$obj->companymobile=$companydetails->row(0)->companymobile;
		$obj->companyphone=$companydetails->row(0)->companyphone;
		$obj->companyemail=$companydetails->row(0)->companyemail;
		$obj->branchname=$companydetails->row(0)->branchname;
		
		$response = [
			'html' => $html,
			'details' => $obj
		];
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function Approvestatus() {
		$this->db->trans_begin();
		$userID = $_SESSION['userid'];
		$updatedatetime = date('Y-m-d H:i:s');
		$approveID = $this->input->post('grnid');
		$confirmnot = $this->input->post('confirmnot');

		$data = array(
			'approvestatus' => $confirmnot,
			'approve_by' => $userID,
			'updatedatetime' => $updatedatetime
		);
		$this->db->where('idtbl_grn', $approveID);
		$this->db->update('tbl_grn', $data);

		$this->db->select('tbl_porder_idtbl_porder');
		$this->db->where('idtbl_grn', $approveID);
		$grn = $this->db->get('tbl_grn')->row();
		$porderID = $grn->tbl_porder_idtbl_porder;

		if ($confirmnot == 1) {
			// Since there's no actual_qty column, we'll just mark the porder as confirmed
			// when all quantities have been received. We check based on GRN details only.

			$this->db->select('SUM(d.qty) as grn_total_qty');
			$this->db->from('tbl_grndetail d');
			$this->db->join('tbl_grn g', 'g.idtbl_grn = d.tbl_grn_idtbl_grn', 'left');
			$this->db->where('g.tbl_porder_idtbl_porder', $porderID);
			$this->db->where('g.approvestatus', 1);
			$this->db->where('g.status', 1);
			$receivedResult = $this->db->get()->row();
			$total_received = $receivedResult ? $receivedResult->grn_total_qty : 0;

			$this->db->select('SUM(qty) as po_total_qty');
			$this->db->from('tbl_porder_detail');
			$this->db->where('tbl_porder_idtbl_porder', $porderID);
			$this->db->where('status', 1);
			$poResult = $this->db->get()->row();
			$total_ordered = $poResult ? $poResult->po_total_qty : 0;

			if ($total_received >= $total_ordered) {
				$dataporder = array(
					'grnconfirm' => '1', 
					'updateuser' => $userID,
					'updatedatetime' => $updatedatetime
				);
				$this->db->where('idtbl_porder', $porderID);
				$this->db->update('tbl_porder', $dataporder);
			}

			$this->db->select('tbl_grn.batchno, tbl_grn.tbl_company_idtbl_company, tbl_grn.tbl_company_branch_idtbl_company_branch, tbl_grn.tbl_location_idtbl_location, tbl_grn.tbl_supplier_idtbl_supplier, tbl_grn.grndate, tbl_grndetail.qty, tbl_grndetail.total, tbl_grndetail.unitprice, tbl_grndetail.tbl_product_idtbl_product, tbl_grn.tbl_porder_idtbl_porder');
			$this->db->from('tbl_grn');
			$this->db->join('tbl_grndetail', 'tbl_grn.idtbl_grn = tbl_grndetail.tbl_grn_idtbl_grn', 'left');
			$this->db->where('tbl_grn.status', 1);
			$this->db->where('tbl_grn.idtbl_grn', $approveID);
			$respond = $this->db->get();

			if ($respond->num_rows() > 0) {
				foreach ($respond->result() as $row) {
					$batchno = $row->batchno;
					$location = $row->tbl_location_idtbl_location;
					$qty = $row->qty;
					$unitprice = $row->unitprice;
					$materialID = $row->tbl_product_idtbl_product;
					$companyid = $row->tbl_company_idtbl_company;
					$branchid = $row->tbl_company_branch_idtbl_company_branch;

					if (!empty($materialID)) {
						$stockData = array(
							'batchno' => $batchno,
							'grnqty' => $qty,
							'qty' => $qty,
							'costunitprice' => $unitprice,
							'stock_status' => 1,
							'status' => '1',
							'insertdatetime' => $updatedatetime,
							'tbl_user_idtbl_user' => $userID,
							'tbl_product_idtbl_product' => $materialID,
							'tbl_grn_idtbl_grn' => $approveID,
							'tbl_grndetail_idtbl_grndetail' => 0,
							'tbl_location_idtbl_location' => $location,
							'tbl_company_idtbl_company' => $companyid,
							'tbl_company_branch_idtbl_company_branch' => $branchid
						);

						$this->db->insert('tbl_stock', $stockData);
					}
				}
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-check';
			$actionObj->title = '';

			if ($confirmnot == 1) {
				$actionObj->message = 'Record Approved Successfully';
			} else {
				$actionObj->message = 'Record Rejected Successfully';
			}

			$actionObj->url = '';
			$actionObj->target = '_blank';
			$actionObj->type = 'success';

			$response = [
				'status' => 1,
				'action' => json_encode($actionObj)
			];

			echo json_encode($response);
		} else {
			$this->db->trans_rollback();
			echo json_encode([
				'status' => 0,
				'message' => 'Transaction failed. Please try again.'
			]);
		}
	}

	public function Getsupplier() {
		$recordID = $this->input->post('recordID');

		$this->db->select('tbl_supplier.idtbl_supplier, tbl_supplier.suppliername');
		$this->db->from('tbl_porder');
		$this->db->join('tbl_supplier', 'tbl_supplier.idtbl_supplier = tbl_porder.tbl_supplier_idtbl_supplier');
		$this->db->where('tbl_porder.status', 1);
		$this->db->where('tbl_porder.idtbl_porder', $recordID);
	
		$response = $this->db->get();
	
		if ($response->num_rows() > 0) {
			$supplier = $response->row();
			echo json_encode([
				'id' => $supplier->idtbl_supplier,
				'name' => $supplier->suppliername
			]);
		} else {
			echo json_encode([]);
		}
	}

	public function Getvattype() {
		$recordID = $this->input->post('recordID');

		$this->db->select('idtbl_grn, vat_type');
		$this->db->from('tbl_grn');
		$this->db->where('tbl_grn.status', 1);
		$this->db->where('tbl_grn.idtbl_grn', $recordID);

		$response = $this->db->get();

		if ($response->num_rows() > 0) {
			$row = $response->row();
			echo json_encode([
				'id' => $row->idtbl_grn,
				'vat_type' => $row->vat_type
			]);
		} else {
			echo json_encode([]);
		}
	}

	public function Goodreceivevattype() {
		$this->db->trans_begin();

		$vattype = $this->input->post('vattype');
		$hiddenID = $this->input->post('hiddenID');

		$data = array('vat_type' => $vattype);

		$this->db->where('idtbl_grn', $hiddenID);
		$this->db->update('tbl_grn', $data);

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-check';
			$actionObj->title = 'Success';
			$actionObj->message = 'VAT Type Updated Successfully';
			$actionObj->type = 'success';

			echo json_encode([
				'status' => 1,
				'action' => json_encode($actionObj)
			]);
		} else {
			$this->db->trans_rollback();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-warning';
			$actionObj->title = 'Error';
			$actionObj->message = 'Record Error';
			$actionObj->type = 'error';

			echo json_encode([
				'status' => 2,
				'action' => json_encode($actionObj)
			]);
		}
	}

	public function Getporderdetails() {
		$recordID = $this->input->post('recordID');

		$this->db->select('p.product_name, d.qty, u.unit, d.unitprice, d.discount, d.vat, d.vatamount, d.grossprice, d.netprice, d.comment, po.remark');
		$this->db->from('tbl_porder_detail d');
		$this->db->join('tbl_porder po', 'po.idtbl_porder = d.tbl_porder_idtbl_porder', 'left');
		$this->db->join('tbl_product p', 'p.idtbl_product = d.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
		$this->db->where('d.status', 1);
		$this->db->where('d.tbl_porder_idtbl_porder', $recordID);

		$response = $this->db->get();

		if ($response->num_rows() > 0) {
			echo json_encode($response->result());
		} else {
			echo json_encode([]);
		}
	}

	public function Getsupplieraccoporder() {
		$recordID = $this->input->post('recordID');
	
		$this->db->select('tbl_supplier_idtbl_supplier');
		$this->db->from('tbl_porder');
		$this->db->where('status', 1);
		$this->db->where('idtbl_porder', $recordID);
	
		$response = $this->db->get();
	
		if ($response->num_rows() > 0) {
			echo $response->row(0)->tbl_supplier_idtbl_supplier;
		} else {
			echo '';
		}
	}

	public function Getcompanyaccoporder() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_company_idtbl_company`');
		$this->db->from('tbl_porder');
		$this->db->where('status', 1);
		$this->db->where('idtbl_porder', $recordID);

		$respond=$this->db->get();

		echo $respond->row(0)->tbl_company_idtbl_company;
	}

	public function Getbranchaccoporder() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_company_branch_idtbl_company_branch`');
		$this->db->from('tbl_porder');
		$this->db->where('status', 1);
		$this->db->where('idtbl_porder', $recordID);

		$respond=$this->db->get();

		echo $respond->row(0)->tbl_company_branch_idtbl_company_branch;
	}

	public function Getproductaccoporder() {
		$recordID=$this->input->post('recordID');

		$this->db->select('`tbl_product`.`idtbl_product`, `tbl_product`.`product_code`, `tbl_product`.`product_name`');
		$this->db->from('tbl_porder_detail');
		$this->db->join('tbl_product', 'tbl_product.idtbl_product = tbl_porder_detail.tbl_product_idtbl_product', 'left');
		$this->db->where('tbl_product.status', 1);
		$this->db->where('tbl_porder_detail.tbl_porder_idtbl_porder', $recordID);
		$this->db->group_by('tbl_product.idtbl_product');

		$respond=$this->db->get();

		echo json_encode($respond->result());
	}

	public function Getproductinfoaccoproduct() {
		$recordID = $this->input->post('recordID');
		$grn_id = $this->input->post('grn_id');

		$this->db->select('qty, unitprice, comment, tbl_product_idtbl_product, idtbl_porder_detail');
		$this->db->from('tbl_porder_detail');
		$this->db->where('status', 1);
		$this->db->where('tbl_porder_idtbl_porder', $grn_id);
		$this->db->where('tbl_product_idtbl_product', $recordID);
		$respond = $this->db->get();

		if($respond->num_rows() > 0) {
			$row = $respond->row(0);

			$this->db->select('tbl_unit_idtbl_unit');
			$this->db->from('tbl_product');
			$this->db->where('idtbl_product', $recordID);
			$productQuery = $this->db->get();
			$uom = $productQuery->num_rows() > 0 ? $productQuery->row()->tbl_unit_idtbl_unit : '';

			$obj = new stdClass();
			$obj->qty = $row->qty;
			$obj->uom = $uom;
			$obj->unitprice = $row->unitprice;
			$obj->comment = $row->comment;
			$obj->detailsid = $row->idtbl_porder_detail;
		}
		else {
			$obj = new stdClass();
			$obj->qty = 0;
			$obj->uom = '';
			$obj->unitprice = 0;
			$obj->comment = '';
			$obj->detailsid = 0;
		}

		echo json_encode($obj);
	}

	public function Getbatchnoaccosupplier() {
		$recordID=$this->input->post('recordID');

		if( !empty($recordID)) {
			$this->db->select('tbl_supplier.`idtbl_supplier`');
			$this->db->from('tbl_supplier');
			$this->db->where('tbl_supplier.idtbl_supplier', $recordID);
			$this->db->where('tbl_supplier.status', 1);

			$responddetail=$this->db->get();

			$supplierid=$responddetail->row(0)->idtbl_supplier;

			$sql="SELECT COUNT(*) AS `count` FROM `tbl_grn`";
			$respond=$this->db->query($sql);

			if($respond->row(0)->count==0) {
				$batchno=date('dmY').'001';
			}
			else {
				$count='000'.($respond->row(0)->count+1);
				$count=substr($count, -3);
				$batchno=date('dmY').$count;
			}

			echo $supplierid.$batchno;
		}
		else {
			echo '';
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

	public function Goodreceivecheckstatus() {
		$this->db->trans_begin();

        $recordID=$this->input->post('requestid');
		$confirmnot=$this->input->post('confirmnot');
		$userID=$_SESSION['userid'];
		$updatedatetime=date('Y-m-d H:i:s');

		$data=array('check_by'=> $userID);

		$this->db->where('idtbl_grn', $recordID);
		$this->db->update('tbl_grn', $data);

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

	public function Geteditgrn() {
		$recordID = $this->input->post('recordID');

		$this->db->select('idtbl_grn, grn_no, grndate, batchno, invoicenum, vat_type, vat, discount, subtotal, vatamount, total, remark, tbl_supplier_idtbl_supplier, approvestatus');
		$this->db->from('tbl_grn');
		$this->db->where('idtbl_grn', $recordID);
		$this->db->where('status', 1);
		$header = $this->db->get()->row();

		$suppliername = '';
		if ($header) {
			$this->db->select('suppliername');
			$this->db->from('tbl_supplier');
			$this->db->where('idtbl_supplier', $header->tbl_supplier_idtbl_supplier);
			$sup = $this->db->get()->row();
			$suppliername = $sup ? $sup->suppliername : '';
		}

		$this->db->select('
			tbl_grndetail.idtbl_grndetail,
			tbl_grndetail.qty,
			tbl_grndetail.unitprice,
			tbl_grndetail.unit_discount,
			tbl_grndetail.total,
			tbl_grndetail.comment,
			tbl_product.product_name,
			tbl_product.product_code,
			tbl_unit.unit
		');
		$this->db->from('tbl_grndetail');
		$this->db->join('tbl_product', 'tbl_product.idtbl_product = tbl_grndetail.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit', 'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');
		$this->db->where('tbl_grndetail.tbl_grn_idtbl_grn', $recordID);
		$this->db->where('tbl_grndetail.status', 1);
		$details = $this->db->get()->result();

		$response = array(
			'header'       => $header,
			'suppliername' => $suppliername,
			'details'      => $details
		);

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function Goodreceiveeditupdate() {
		$this->db->trans_begin();

		$userID        = $_SESSION['userid'];
		$updatedatetime = date('Y-m-d H:i:s');

		$grnID     = $this->input->post('grnID');
		$tableData = $this->input->post('tableData');
		$discount  = floatval($this->input->post('discount'));
		$vat       = floatval($this->input->post('vat'));
		$vat_type  = $this->input->post('vat_type');

		$sum = 0;

		if (!empty($tableData)) {
			foreach ($tableData as $row) {
				$detailID      = $row['detailid'];
				$unitprice     = floatval($row['unitprice']);
				$qty           = floatval($row['qty']);
				$unit_discount = floatval($row['discount']);

				$total = ($unitprice * $qty) - $unit_discount;

				$datadetail = array(
					'unitprice'      => $unitprice,
					'costunitprice'  => $unitprice,
					'total'          => $total,
					'updatedatetime' => $updatedatetime
				);

				$this->db->where('idtbl_grndetail', $detailID);
				$this->db->update('tbl_grndetail', $datadetail);

				$sum += $total;
			}
		}

		$subTotal = $sum - $discount;

		if ($vat_type == 1) {
			$vatAmount = ($subTotal * $vat) / 100;
			$finalTotal = $subTotal + $vatAmount;
		} else {
			$vatAmount  = 0;
			$finalTotal = $subTotal;
		}

		$dataheader = array(
			'subtotal'       => $subTotal,
			'vatamount'      => $vatAmount,
			'total'          => $finalTotal,
			'subtotalcost'   => $subTotal,
			'vatamountcost'  => $vatAmount,
			'totalcost'      => $finalTotal,
			'updatedatetime' => $updatedatetime,
			'updateuser'     => $userID
		);

		$this->db->where('idtbl_grn', $grnID);
		$this->db->update('tbl_grn', $dataheader);

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-check';
			$actionObj->title = '';
			$actionObj->message = 'GRN Prices Updated Successfully';
			$actionObj->url = '';
			$actionObj->target = '_blank';
			$actionObj->type = 'success';

			echo json_encode(array('status' => 1, 'action' => json_encode($actionObj)));
		} else {
			$this->db->trans_rollback();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-warning';
			$actionObj->title = '';
			$actionObj->message = 'Record Error';
			$actionObj->url = '';
			$actionObj->target = '_blank';
			$actionObj->type = 'danger';

			echo json_encode(array('status' => 2, 'action' => json_encode($actionObj)));
		}
	}

	public function Getgrnserials() {
		$recordID = $this->input->post('recordID');

		$this->db->select('d.idtbl_grndetail, d.qty, d.costunitprice, d.tbl_product_idtbl_product, p.product_name, p.product_code');
		$this->db->from('tbl_grndetail d');
		$this->db->join('tbl_product p', 'p.idtbl_product = d.tbl_product_idtbl_product', 'left');
		$this->db->where('d.tbl_grn_idtbl_grn', $recordID);
		$this->db->where('d.status', 1);
		$details = $this->db->get()->result();

		$this->db->select('serialno, tbl_grndetail_idtbl_grndetail');
		$this->db->from('tbl_product_serial');
		$this->db->where('tbl_grn_idtbl_grn', $recordID);
		$this->db->where('status', 1);
		$this->db->order_by('idtbl_product_serial', 'ASC');
		$serialrows = $this->db->get()->result();

		$map = array();
		foreach ($serialrows as $s) {
			$map[$s->tbl_grndetail_idtbl_grndetail][] = $s->serialno;
		}

		foreach ($details as $d) {
			$d->serials = isset($map[$d->idtbl_grndetail]) ? $map[$d->idtbl_grndetail] : array();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode(array('details' => $details)));
	}

	public function Goodreceiveserialinsert() {
		$this->db->trans_begin();

		$userID = $_SESSION['userid'];
		$updatedatetime = date('Y-m-d H:i:s');

		$grnID = $this->input->post('grnID');
		$tableData = $this->input->post('tableData');

		$error = '';
		$inserts = array();
		$seen = array();

		$this->db->select('idtbl_grn, grn_no, approvestatus');
		$this->db->from('tbl_grn');
		$this->db->where('idtbl_grn', $grnID);
		$this->db->where('status', 1);
		$grn = $this->db->get()->row();

		if (!$grn) {
			$error = 'GRN not found';
		} elseif ($grn->approvestatus == 2) {
			$error = 'Cannot add serial numbers to a rejected GRN';
		} elseif (empty($tableData)) {
			$error = 'No serial numbers to save';
		}

		if ($error == '') {
			foreach ($tableData as $row) {
				$detailID = $row['detailid'];
				$serials = isset($row['serials']) ? $row['serials'] : array();

				// detail must belong to this GRN
				$this->db->select('idtbl_grndetail, qty, costunitprice, tbl_product_idtbl_product');
				$this->db->from('tbl_grndetail');
				$this->db->where('idtbl_grndetail', $detailID);
				$this->db->where('tbl_grn_idtbl_grn', $grnID);
				$this->db->where('status', 1);
				$detail = $this->db->get()->row();

				if (!$detail) {
					$error = 'Invalid GRN detail';
					break;
				}

				// do not exceed the GRN qty
				$this->db->where('tbl_grndetail_idtbl_grndetail', $detailID);
				$this->db->where('status', 1);
				$existing = $this->db->count_all_results('tbl_product_serial');

				$remaining = floor($detail->qty) - $existing;
				if (count($serials) > $remaining) {
					$error = 'Serial numbers exceed the GRN qty';
					break;
				}

				foreach ($serials as $serial) {
					$serial = trim($serial);
					if ($serial === '') { continue; }

					$key = strtolower($serial);
					if (isset($seen[$key])) {
						$error = 'Duplicate serial number: ' . htmlspecialchars($serial);
						break 2;
					}
					$seen[$key] = true;

					// serial must be unique across all stock
					$this->db->where('serialno', $serial);
					$this->db->where('status', 1);
					if ($this->db->count_all_results('tbl_product_serial') > 0) {
						$error = 'Serial number already exists: ' . htmlspecialchars($serial);
						break 2;
					}

					$inserts[] = array(
						'serialno' => $serial,
						'tbl_grn_idtbl_grn' => $grnID,
						'tbl_grndetail_idtbl_grndetail' => $detailID,
						'costunitprice' => $detail->costunitprice,
						'tbl_product_idtbl_product' => $detail->tbl_product_idtbl_product,
						'stock_status' => 1,
						'status' => 1,
						'insertdatetime' => $updatedatetime,
						'tbl_user_idtbl_user' => $userID
					);
				}
			}
		}

		if ($error == '' && !empty($inserts)) {
			foreach ($inserts as $serialRow) {

				$this->db->insert('tbl_product_serial', $serialRow);
				$serialID = $this->db->insert_id();
				$branchID=$_SESSION['branch_id'];

				$movement = array(
					'movement_type' => 1,                    
					'from_status' => NULL,                     
					'to_status' => 1,                           
					'doc_type' => 'GRN',
					'doc_id' => $grnID,
					'doc_detail_id' => $serialRow['tbl_grndetail_idtbl_grndetail'],
					'branch_id' => $branchID,
					'remark' => 'Serial added from ' . $grn->grn_no,
					'insertdatetime' => $updatedatetime,
					'tbl_user_idtbl_user' => $userID,
					'tbl_product_serial_idtbl_product_serial' => $serialID
				);
				$this->db->insert('tbl_serial_movement', $movement);
			}
		}

		if ($error == '' && $this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-save';
			$actionObj->title = '';
			$actionObj->message = count($inserts) . ' Serial Number(s) Added Successfully';
			$actionObj->url = '';
			$actionObj->target = '_blank';
			$actionObj->type = 'success';

			echo json_encode(array('status' => 1, 'action' => json_encode($actionObj)));
		} else {
			$this->db->trans_rollback();

			$actionObj = new stdClass();
			$actionObj->icon = 'fas fa-exclamation-triangle';
			$actionObj->title = '';
			$actionObj->message = ($error != '') ? $error : 'Record Error';
			$actionObj->url = '';
			$actionObj->target = '_blank';
			$actionObj->type = 'danger';

			echo json_encode(array('status' => 2, 'action' => json_encode($actionObj)));
		}
	}
}