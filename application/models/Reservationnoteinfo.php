<?php
use Dompdf\Dompdf;
use Dompdf\Options;
class Reservationnoteinfo extends CI_Model{

    // returns all data needed for the printed reservation note, or null
    public function Getnotedata($reservationID){

        $this->db->select('r.*, 
            c.customer, c.nic, c.telephone_no AS customer_phone, c.address_line1 AS cus_address1, c.address_line2 AS cus_address2, c.city AS cus_city, c.vat_no AS cus_vat_no,
            co.company, co.address1 AS co_address1, co.address2 AS co_address2, co.mobile AS co_mobile, co.phone AS co_phone, co.email AS co_email, co.vat_reg_no AS co_vat_reg_no,
            b.branch, b.address1 AS br_address1, b.address2 AS br_address2, b.mobile AS br_mobile, b.phone AS br_phone,
            u.name AS prepared_by,
            i.invoice_no');
        $this->db->from('tbl_reservation AS r');
        $this->db->join('tbl_customer AS c', 'c.idtbl_customer = r.tbl_customer_idtbl_customer', 'left');
        $this->db->join('tbl_company AS co', 'co.idtbl_company = r.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch AS b', 'b.idtbl_company_branch = r.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS u', 'u.idtbl_user = r.tbl_user_idtbl_user', 'left');
        $this->db->join('tbl_invoice AS i', 'i.idtbl_invoice = r.tbl_invoice_idtbl_invoice', 'left');
        $this->db->where('r.idtbl_reservation', $reservationID);
        $this->db->where('r.status !=', 3);
        $header=$this->db->get();

        if($header->num_rows()==0){
            return null;
        }

        $this->db->select('d.qty, d.unitprice, d.line_total, d.vat_amount, d.line_net_total, d.comment, p.product_code, p.product_name, p.model_no');
        $this->db->from('tbl_reservation_detail AS d');
        $this->db->join('tbl_product AS p', 'p.idtbl_product = d.tbl_product_idtbl_product');
        $this->db->where('d.tbl_reservation_idtbl_reservation', $reservationID);
        $this->db->where('d.status', 1);
        $this->db->order_by('d.idtbl_reservation_detail', 'ASC');
        $items=$this->db->get();

        $row=$header->row(0);

        if($row->reserve_status==2){ $statustext='Invoice Completed'; }
        else if($row->reserve_status==3){ $statustext='Cancelled'; }
        else if($row->expiry_date < date('Y-m-d')){ $statustext='Expired'; }
        else{ $statustext='Open'; }

        return array(
            'r'=>$row,
            'items'=>$items->result(),
            'statustext'=>$statustext,
        );
    }

    private function rs($n){
        return number_format((float)$n, 2);
    }

    private function h($v){
        return htmlspecialchars((string)$v);
    }

    // builds the full A4 HTML layout of the reservation note
    public function Buildnotehtml($data){
        $r=$data['r'];
        $items=$data['items'];
        $statustext=$data['statustext'];

        if($statustext=='Invoice Completed'){ $statuscolor='#198754'; }
        else if($statustext=='Cancelled'){ $statuscolor='#6c757d'; }
        else if($statustext=='Expired'){ $statuscolor='#dc3545'; }
        else{ $statuscolor='#0d6efd'; }

        $address=trim($r->br_address1.' '.$r->br_address2);
        if($address==''){ $address=trim($r->co_address1.' '.$r->co_address2); }
        $tel=trim(($r->br_phone ?: $r->co_phone).' '.($r->br_mobile ?: $r->co_mobile));

        $cusaddr=array_filter(array($r->cus_address1, $r->cus_address2, $r->cus_city), function($v){
            return trim($v)!='' && trim($v)!='-';
        });

        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reservation Note <?php echo $r->reservation_no ?></title>
<style>
    @page { margin: 28px 30px 60px 30px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; }
    table { border-collapse: collapse; width: 100%; }

    .head { background: #a50f2d; color: #fff; }
    .head td { padding: 14px 16px; vertical-align: middle; }
    .company { font-size: 20px; font-weight: bold; }
    .small { font-size: 10px; }
    .doc-title { font-size: 18px; font-weight: bold; text-align: right; letter-spacing: 1px; }
    .accent { height: 4px; background: #6e0b1f; }

    .box { border: 1px solid #ddd; }
    .box-title { background: #f6e9ec; color: #a50f2d; font-weight: bold; font-size: 11px; padding: 6px 10px; border-bottom: 1px solid #ddd; }
    .box-body { padding: 8px 10px; line-height: 1.6; }
    .lbl { color: #777; }

    .items th { background: #a50f2d; color: #fff; padding: 7px 6px; font-size: 10.5px; border: 1px solid #a50f2d; }
    .items td { padding: 7px 6px; border: 1px solid #ddd; vertical-align: top; }
    .items tr.alt td { background: #fbf4f5; }
    .r { text-align: right; }
    .c { text-align: center; }

    .totals td { padding: 5px 8px; }
    .totals .grand td { background: #a50f2d; color: #fff; font-weight: bold; font-size: 13px; }

    .badge { display: inline-block; color: #fff; padding: 3px 10px; font-size: 10px; font-weight: bold; }
    .terms { font-size: 10px; color: #555; line-height: 1.6; }
    .sign { border-top: 1px solid #555; padding-top: 4px; text-align: center; font-size: 10px; }

    .footer { position: fixed; bottom: -40px; left: 0; right: 0; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; }
</style>
</head>
<body>

<!-- Header -->
<table class="head">
    <tr>
        <td style="width:60%;">
            <div class="company"><?php echo $this->h($r->company) ?></div>
            <div class="small">
                <?php echo $this->h($address) ?><br>
                Tel: <?php echo $this->h($tel) ?>
                <?php if(!empty($r->co_email)){ echo ' | '.$this->h($r->co_email); } ?>
                <?php if(!empty($r->co_vat_reg_no)){ echo '<br>VAT Reg No: '.$this->h($r->co_vat_reg_no); } ?>
            </div>
        </td>
        <td style="width:40%;">
            <div class="doc-title">RESERVATION NOTE</div>
            <div class="small" style="text-align:right; margin-top:4px;">Branch: <?php echo $this->h($r->branch) ?></div>
        </td>
    </tr>
</table>
<div class="accent"></div>

<!-- Customer + reservation details -->
<table style="margin-top:12px;">
    <tr>
        <td style="width:50%; padding-right:6px; vertical-align:top;">
            <div class="box">
                <div class="box-title">CUSTOMER DETAILS</div>
                <div class="box-body">
                    <b><?php echo $this->h($r->customer) ?></b><br>
                    <?php echo $this->h(implode(', ', $cusaddr)) ?><br>
                    <span class="lbl">Tel:</span> <?php echo $this->h($r->customer_phone) ?>
                    <?php if(!empty($r->cus_vat_no) && $r->cus_vat_no!='-'){ ?>
                    <br><span class="lbl">VAT No:</span> <?php echo $this->h($r->cus_vat_no) ?>
                    <?php } ?>
                </div>
            </div>
        </td>
        <td style="width:50%; padding-left:6px; vertical-align:top;">
            <div class="box">
                <div class="box-title">RESERVATION DETAILS</div>
                <div class="box-body">
                    <span class="lbl">Reservation No:</span> <b><?php echo $r->reservation_no ?></b><br>
                    <span class="lbl">Date:</span> <?php echo date('d M Y', strtotime($r->reservation_date)) ?><br>
                    <span class="lbl">Hold Until:</span> <b><?php echo date('d M Y', strtotime($r->expiry_date)) ?></b><br>
                    <?php if(!empty($r->invoice_no)){ ?><br><span class="lbl">Invoice No:</span> <?php echo $r->invoice_no ?><?php } ?>
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- Items -->
<table class="items" style="margin-top:12px;">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:13%;">Code</th>
            <th>Description</th>
            <th style="width:7%;">Qty</th>
            <th style="width:14%;">Unit Price</th>
            <th style="width:15%;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $n=0; foreach($items as $item){ $n++; ?>
        <tr class="<?php echo $n%2==0 ? 'alt' : '' ?>">
            <td class="c"><?php echo $n ?></td>
            <td><?php echo $this->h($item->product_code) ?></td>
            <td>
                <b><?php echo $this->h($item->product_name) ?></b>
                <?php if(!empty($item->model_no)){ ?><br><span class="small lbl">Model: <?php echo $this->h($item->model_no) ?></span><?php } ?>
                <?php if(!empty($item->comment)){ ?><br><span class="small lbl"><?php echo $this->h($item->comment) ?></span><?php } ?>
            </td>
            <td class="c"><?php echo (float)$item->qty ?></td>
            <td class="r"><?php echo $this->rs($item->unitprice) ?></td>
            <td class="r"><?php echo $this->rs($item->line_total) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Remark + totals -->
<table style="margin-top:10px;">
    <tr>
        <td style="width:55%; vertical-align:top; padding-right:12px;">
            <?php if(!empty($r->remark)){ ?>
            <div class="box">
                <div class="box-title">REMARK</div>
                <div class="box-body"><?php echo nl2br($this->h($r->remark)) ?></div>
            </div>
            <?php } ?>
        </td>
        <td style="width:45%; vertical-align:top;">
            <table class="totals box">
                <tr><td>Total Qty</td><td class="r"><?php echo (float)$r->total_qty ?></td></tr>
                <tr><td>Subtotal</td><td class="r"><?php echo $this->rs($r->subtotal) ?></td></tr>
                <tr>
                    <td>VAT <?php echo $r->vat_applicable==1 ? '('.(float)$r->vat_rate.'%)' : '(Not applicable)' ?></td>
                    <td class="r"><?php echo $this->rs($r->vat_amount) ?></td>
                </tr>
                <tr class="grand"><td>TOTAL (Rs.)</td><td class="r"><?php echo $this->rs($r->total_amount) ?></td></tr>
                <tr><td>Advance Paid</td><td class="r"><?php echo $this->rs($r->advance_amount) ?></td></tr>
                <tr><td><b>Balance to Pay</b></td><td class="r"><b><?php echo $this->rs($r->total_amount-$r->advance_amount) ?></b></td></tr>
            </table>
        </td>
    </tr>
</table>

<!-- Terms -->
<div class="box" style="margin-top:12px;">
    <div class="box-title">TERMS &amp; CONDITIONS</div>
    <div class="box-body terms">
        1. The items are held for the customer until <b><?php echo date('d M Y', strtotime($r->expiry_date)) ?></b>. After this date the reservation may be released.<br>
        2. Stock is allocated and serial numbers are assigned only at the time of invoicing.<br>
        3. Prices and VAT shown are as at the reservation date and may change if the reservation is renewed.<br>
        4. Please bring this note when collecting the items.
    </div>
</div>

<!-- Signatures -->
<table style="margin-top:38px;">
    <tr>
        <td style="width:30%;"><div class="sign">Prepared By<br><b><?php echo $this->h($r->prepared_by) ?></b></div></td>
        <td style="width:5%;"></td>
        <td style="width:30%;"><div class="sign">Authorized By</div></td>
        <td style="width:5%;"></td>
        <td style="width:30%;"><div class="sign">Customer Signature</div></td>
    </tr>
</table>

<div class="footer">
    <?php echo $this->h($r->company) ?> &nbsp;|&nbsp; Reservation Note <?php echo $r->reservation_no ?> &nbsp;|&nbsp; Printed on <?php echo date('d M Y, h:i A') ?>
</div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
    public function Streampdf($html, $reservationno){
        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();

        while (ob_get_level()) { ob_end_clean(); }

        $this->pdf->stream('Reservation Note - '.$reservationno.'.pdf', array('Attachment' => 0));
        exit;
    }
}