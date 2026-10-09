<?php
class Newpurchaserequestinfo extends CI_Model {

	private function respond($status, $icon, $message, $type) {
		$actionObj = new stdClass();
		$actionObj->icon    = $icon;
		$actionObj->title   = '';
		$actionObj->message = $message;
		$actionObj->url     = '';
		$actionObj->target  = '_blank';
		$actionObj->type    = $type;

		$obj = new stdClass();
		$obj->status = $status;
		$obj->action = json_encode($actionObj);

		echo json_encode($obj);
	}

	public function searchProducts($query) {
		$this->db->select('p.idtbl_product, p.product_code, p.product_name, p.model_no, u.unit, u.unit_short');
		$this->db->from('tbl_product AS p');
		$this->db->join('tbl_unit AS u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
		$this->db->where('p.status', 1);

		if ($query !== '') {
			$this->db->group_start();
			$this->db->like('p.product_code', $query);
			$this->db->or_like('p.product_name', $query);
			$this->db->or_like('p.model_no', $query);
			$this->db->or_like('p.barcode', $query);
			$this->db->group_end();
		}

		$this->db->order_by('p.product_name', 'ASC');
		$this->db->limit(20);
		$rows = $this->db->get()->result();

		$data = array();
		foreach ($rows as $row) {
			$text = $row->product_code . ' - ' . $row->product_name;
			if (!empty($row->model_no)) {
				$text .= ' (' . $row->model_no . ')';
			}
			$data[] = array(
				'id'   => $row->idtbl_product,
				'text' => $text,
				'unit' => ($row->unit_short ? $row->unit_short : $row->unit)
			);
		}
		return $data;
	}

	public function Getstockqty() {
		$productID = (int)$this->input->post('recordID');
		$companyID = (int)$_SESSION['company_id'];
		$branchID  = (int)$_SESSION['branch_id'];

		$sql = "SELECT IFNULL(SUM(`qty`), 0) AS qty
				FROM `tbl_stock`
				WHERE `status` = 1
				  AND `stock_status` = 1
				  AND `tbl_product_idtbl_product` = ?
				  AND `tbl_company_idtbl_company` = ?
				  AND `tbl_company_branch_idtbl_company_branch` = ?";
		$respond = $this->db->query($sql, array($productID, $companyID, $branchID));

		$qty = ($respond->num_rows() > 0) ? (float)$respond->row()->qty : 0;
		echo json_encode(array('qty' => $qty));
	}

	public function Newpurchaserequestinsertupdate() {
		$userID    = $_SESSION['userid'];
		$companyID = (int)$_SESSION['company_id'];
		$branchID  = (int)$_SESSION['branch_id'];

		$tableData = $this->input->post('tableData');
		$reqdate   = $this->input->post('date');
		$now       = date('Y-m-d H:i:s');

		if (empty($reqdate) || strtotime($reqdate) === false) {
			$reqdate = date('Y-m-d');
		}

		if (empty($tableData) || !is_array($tableData)) {
			$this->respond(0, 'fas fa-exclamation-triangle', 'No items to save', 'danger');
			return;
		}

		$lines = array();
		$productIDs = array();
		foreach ($tableData as $row) {
			$pid = isset($row['product_id']) ? (int)$row['product_id'] : 0;
			$qty = isset($row['qty']) ? (float)$row['qty'] : 0;
			if ($pid <= 0 || $qty <= 0) {
				$this->respond(0, 'fas fa-exclamation-triangle', 'Invalid product or quantity', 'danger');
				return;
			}
			$lines[] = array(
				'product_id' => $pid,
				'qty'        => $qty,
				'comment'    => isset($row['comment']) ? trim($row['comment']) : ''
			);
			$productIDs[] = $pid;
		}

		$productIDs = array_unique($productIDs);
		$this->db->from('tbl_product');
		$this->db->where_in('idtbl_product', $productIDs);
		$this->db->where('status', 1);
		if ($this->db->count_all_results() != count($productIDs)) {
			$this->respond(0, 'fas fa-exclamation-triangle', 'One or more products are invalid or inactive', 'danger');
			return;
		}

		$this->db->trans_begin();

		$header = array(
			'date'                                   => $reqdate,
			'porder_req_no'                          => '',
			'confirmstatus'                          => 0,
			'status'                                 => 1,
			'check_by'                               => 0,
			'porderconfirm'                          => 0,
			'insertdatetime'                         => $now,
			'tbl_user_idtbl_user'                    => $userID,
			'tbl_company_idtbl_company'              => $companyID,
			'tbl_company_branch_idtbl_company_branch'=> $branchID
		);
		$this->db->insert('tbl_porder_req', $header);
		$porderID = $this->db->insert_id();

		foreach ($lines as $line) {
			$detail = array(
				'qty'                          => $line['qty'],
				'comment'                      => $line['comment'],
				'status'                       => 1,
				'insertdatetime'               => $now,
				'tbl_porder_req_idtbl_porder_req' => $porderID,
				'tbl_product_idtbl_product'    => $line['product_id'],
				'tbl_user_idtbl_user'          => $userID
			);
			$this->db->insert('tbl_porder_req_detail', $detail);
		}

		$currentYear  = date('Y', strtotime($reqdate));
		$currentMonth = date('m', strtotime($reqdate));

		if ($currentMonth < 4) {
			$fromyear = date('Y-m-d', strtotime(($currentYear - 1) . '-04-01'));
			$toyear   = $currentYear . '-03-31';
		} else {
			$fromyear = $currentYear . '-04-01';
			$toyear   = ($currentYear + 1) . '-03-31';
		}

		$this->db->select('porder_req_no');
		$this->db->from('tbl_porder_req');
		$this->db->where('tbl_company_idtbl_company', $companyID);
		$this->db->where('date >=', $fromyear);
		$this->db->where('date <=', $toyear);
		$this->db->where('porder_req_no !=', '');
		$this->db->order_by('porder_req_no', 'DESC');
		$this->db->limit(1);
		$last = $this->db->get();

		$count = ($last->num_rows() > 0) ? intval(substr($last->row()->porder_req_no, -4)) : 0;
		$count++;

		$reqno = 'POR' . substr($fromyear, 2, 2) . sprintf('%04d', $count);

		$this->db->where('idtbl_porder_req', $porderID);
		$this->db->update('tbl_porder_req', array(
			'porder_req_no'  => $reqno,
			'updatedatetime' => $now
		));

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();
			$this->respond(1, 'fas fa-save', 'Record Added Successfully (' . $reqno . ')', 'success');
		} else {
			$this->db->trans_rollback();
			$this->respond(0, 'fas fa-exclamation-triangle', 'Record Error', 'danger');
		}
	}

	public function Purchaseorderview() {
		$recordID = (int)$this->input->post('recordID');

		$this->db->select('d.qty, d.comment, p.product_code, p.product_name, p.model_no, u.unit_short, u.unit');
		$this->db->from('tbl_porder_req_detail AS d');
		$this->db->join('tbl_product AS p', 'p.idtbl_product = d.tbl_product_idtbl_product', 'left');
		$this->db->join('tbl_unit AS u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
		$this->db->where('d.tbl_porder_req_idtbl_porder_req', $recordID);
		$this->db->where('d.status', 1);
		$responddetail = $this->db->get();

		$html = '<table class="table table-striped table-bordered table-sm">
		<thead>
			<tr>
				<th>Product Info</th>
				<th>Model</th>
				<th>Comment</th>
				<th class="text-right">Qty</th>
				<th class="text-center">Unit</th>
			</tr>
		</thead>
		<tbody>';

		if ($responddetail->num_rows() == 0) {
			$html .= '<tr><td colspan="5" class="text-center text-muted">No items found</td></tr>';
		}

		foreach ($responddetail->result() as $row) {
			$unit = $row->unit_short ? $row->unit_short : $row->unit;

			$productInfo = html_escape($row->product_name);
			if (!empty($row->product_code)) {
				$productInfo .= ' / ' . html_escape($row->product_code);
			}

			$html .= '<tr>
				<td>' . $productInfo . '</td>
				<td>' . html_escape($row->model_no) . '</td>
				<td>' . html_escape($row->comment) . '</td>
				<td class="text-right">' . (float)$row->qty . '</td>
				<td class="text-center">' . html_escape($unit) . '</td>
			</tr>';
		}

		$html .= '</tbody></table>';
		echo $html;
	}

	public function porderviewheader() {
		$recordID = (int)$this->input->post('recordID');

		$this->db->select('r.porder_req_no, c.company AS companyname, c.address1 AS companyaddress,
						   c.mobile AS companymobile, c.phone AS companyphone, c.email AS companyemail,
						   b.branch AS branchname');
		$this->db->from('tbl_porder_req AS r');
		$this->db->join('tbl_company AS c', 'c.idtbl_company = r.tbl_company_idtbl_company', 'left');
		$this->db->join('tbl_company_branch AS b', 'b.idtbl_company_branch = r.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->where('r.idtbl_porder_req', $recordID);
		$this->db->where('r.status', 1);
		$respond = $this->db->get();

		$obj = new stdClass();
		if ($respond->num_rows() > 0) {
			$row = $respond->row();
			$obj->porder_no      = $row->porder_req_no;
			$obj->companyname    = $row->companyname;
			$obj->companyaddress = $row->companyaddress;
			$obj->companymobile  = $row->companymobile;
			$obj->companyphone   = $row->companyphone;
			$obj->companyemail   = $row->companyemail;
			$obj->branchname     = $row->branchname;
		} else {
			$obj->porder_no = $obj->companyname = $obj->companyaddress = '';
			$obj->companymobile = $obj->companyphone = $obj->companyemail = $obj->branchname = '';
		}
		echo json_encode($obj);
	}

	public function Newpurchaserequeststatus() {
		$recordID   = (int)$this->input->post('requestid');
		$confirmnot = (int)$this->input->post('confirmnot'); // 1 approve, 2 reject
		$userID     = $_SESSION['userid'];
		$now        = date('Y-m-d H:i:s');

		if (!in_array($confirmnot, array(1, 2))) {
			$this->respond(2, 'fas fa-exclamation-triangle', 'Invalid action', 'danger');
			return;
		}

		$this->db->select('confirmstatus, check_by');
		$this->db->from('tbl_porder_req');
		$this->db->where('idtbl_porder_req', $recordID);
		$this->db->where('status', 1);
		$cur = $this->db->get();

		if ($cur->num_rows() == 0 || $cur->row()->check_by == 0 || $cur->row()->confirmstatus != 0) {
			$this->respond(2, 'fas fa-exclamation-triangle', 'Request must be checked and still pending', 'danger');
			return;
		}

		$this->db->trans_begin();

		$this->db->where('idtbl_porder_req', $recordID);
		$this->db->update('tbl_porder_req', array(
			'confirmstatus'  => $confirmnot,
			'updateuser'     => $userID,
			'updatedatetime' => $now
		));

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();
			$msg = ($confirmnot == 1) ? 'Record Approved Successfully' : 'Record Rejected Successfully';
			$this->respond(1, 'fas fa-check', $msg, 'success');
		} else {
			$this->db->trans_rollback();
			$this->respond(2, 'fas fa-exclamation-triangle', 'Record Error', 'danger');
		}
	}

	public function Newpurchaserequestcheckstatus() {
		$recordID = (int)$this->input->post('requestid');
		$userID   = $_SESSION['userid'];
		$now      = date('Y-m-d H:i:s');

		$this->db->select('check_by, confirmstatus');
		$this->db->from('tbl_porder_req');
		$this->db->where('idtbl_porder_req', $recordID);
		$this->db->where('status', 1);
		$cur = $this->db->get();

		if ($cur->num_rows() == 0 || $cur->row()->check_by > 0 || $cur->row()->confirmstatus != 0) {
			$this->respond(2, 'fas fa-exclamation-triangle', 'Request already checked or processed', 'danger');
			return;
		}

		$this->db->trans_begin();

		$this->db->where('idtbl_porder_req', $recordID);
		$this->db->update('tbl_porder_req', array(
			'check_by'       => $userID,
			'updateuser'     => $userID,
			'updatedatetime' => $now
		));

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();
			$this->respond(1, 'fas fa-check', 'Record Checked Successfully', 'success');
		} else {
			$this->db->trans_rollback();
			$this->respond(2, 'fas fa-exclamation-triangle', 'Record Error', 'danger');
		}
	}
}