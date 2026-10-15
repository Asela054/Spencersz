<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Invoice extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model("Commeninfo");
        $this->load->model("Invoiceinfo");
    }
    public function index(){
		$result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
		$this->load->view('invoice', $result);
	}
    public function SubmitNewCustomer(){
        $result=$this->Invoiceinfo->SubmitNewCustomer();
    }
    public function SearchCustomer(){
        $result=$this->Invoiceinfo->SearchCustomer();
    }
    public function Getproductdetails(){
       $result=$this->Invoiceinfo->Getproductdetails();
    }
    public function CheckGiftVoucher(){
       $result=$this->Invoiceinfo->CheckGiftVoucher();
    }
    public function Getpaymenttypelist(){
       $result=$this->Invoiceinfo->Getpaymenttypelist();
    }
    public function Insertinvoice(){
       $result=$this->Invoiceinfo->Insertinvoice();
    }
    public function Printinvoice($x){
       $result=$this->Invoiceinfo->Printinvoice($x);
    }
    public function ajax_credit_notes() {
        $cid = $this->input->post('customer_id');
        echo json_encode($this->Invoiceinfo->get_open_credit_notes($cid));
    }
    public function Holdinvoice(){
       $result=$this->Invoiceinfo->Holdinvoice();
    }
    public function Getholdlist(){
       $result=$this->Invoiceinfo->Getholdlist();
    }
    public function Resumehold(){
       $result=$this->Invoiceinfo->Resumehold();
    }
    public function Deletehold(){
       $result=$this->Invoiceinfo->Deletehold();
    }
    public function Getquotationlist(){
        $branch_id=$_SESSION['branch_id'];
        $rows = $this->db->select('idtbl_invoice AS id, invoice_no, invoice_date, customer_name, net_total')
            ->where('invoice_type', 'Corporate Quotation')->where('status', 1)
            ->where('converted_invoice_id IS NULL', null, false)
            ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
            ->order_by('idtbl_invoice', 'DESC')->limit(50)->get('tbl_invoice')->result();
        echo json_encode(['status' => 1, 'rows' => $rows]);
    }

    public function Loadquotation(){
        $branch_id=$_SESSION['branch_id'];
        $id = (int)$this->input->post('id');
        $q = $this->db->where('idtbl_invoice', $id)->where('invoice_type', 'Corporate Quotation')->where('status', 1)
                    ->where('converted_invoice_id IS NULL', null, false)
                    ->where('tbl_company_branch_idtbl_company_branch', $branch_id)
                    ->get('tbl_invoice')->row();
        if (!$q) return $this->_jsonResponse(0, 'fas fa-exclamation-triangle', 'Quotation not found or already converted', 'danger');

        $items = $this->db->select('tbl_product_idtbl_product AS id, tbl_product_serial_idtbl_product_serial AS serialId, qty')
            ->where('tbl_invoice_idtbl_invoice', $id)->where('status', 1)->get('tbl_invoice_detail')->result();

        echo json_encode(['status' => 1, 'bill' => [
            'id' => (int)$q->idtbl_invoice, 'customerId' => $q->tbl_customer_idtbl_customer,
            'customerNic' => $q->customer_nic, 'customerName' => $q->customer_name,
            'invoiceType' => 'Retail Tax Invoice (VAT)',          // converting to a normal invoice
            'fulfillment' => $q->fulfillment, 'discount' => (float)$q->discount, 'items' => $items,
        ]]);
    }
}