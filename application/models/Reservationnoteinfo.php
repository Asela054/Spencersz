<?php
class Reservationnoteinfo extends CI_Model{

    // returns all data needed for the printed reservation note / advance receipt, or null
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


    /* =====================================================================
       Advance Receipt on pre-printed continuous stationery (LQ-310)
       Artwork = 1600 x 927 px  =  9.5in x 5.5in  =  684 x 396 pt
       Every x / y / w / h below is in ARTWORK PIXELS measured from the
       top-left corner of the artwork (converted to pt by px()).
       Only DATA is printed - the form design is already on the paper.
       ===================================================================== */
    private $paperW = 684;     // 9.5in in pt
    private $paperH = 396;     // 5.5in in pt
    private $artW   = 1600;    // artwork width in px

    private $shiftX = 0;       // printer calibration in mm (+ moves right)
    private $shiftY = 0;       // printer calibration in mm (+ moves down)
    private $guide  = false;   // true = red outline around every field (alignment test)

    private $fontSize   = 10;  // pt
    private $lineHeight = 12;  // pt

    private $wordsWidth = 75;  // characters per line for the amount in words

    // text-line tops for the dotted lines of the form
    private $yReceived = 329;
    private $yOf       = 384;
    private $ySum1     = 439;
    private $ySum2     = 495;
    private $ySum3     = 550;
    private $yAgainst1 = 605;
    private $yAgainst2 = 661;

    // $data = result of Reservationnoteinfo::Getnotedata($reservationID)
    public function Buildreceipthtml($data){
        $r     = $data['r'];
        $items = $data['items'];

        $amount = (float)$r->advance_amount;

        // customer address (skip empty and "-" parts)
        $cusaddr = array_filter(array($r->cus_address1, $r->cus_address2, $r->cus_city), function($v){
            return trim((string)$v) != '' && trim((string)$v) != '-';
        });

        // amount in words, wrapped over the three dotted lines
        $wordLines = explode("\n", wordwrap($this->amount_words($amount), $this->wordsWidth, "\n", true));

        // being payment against
        $against1 = 'Advance payment for Reservation No ' . $r->reservation_no;
        $names = array();
        foreach ($items as $it) {
            $names[] = $it->product_name . ' x' . (float)$it->qty;
        }
        $against2 = mb_strimwidth(implode(', ', $names), 0, 95, '...');

        $p  = $this->at(1240, 161, 270, 30, $this->h($r->reservation_no), 'left', true);                        // No.
        $p .= $this->at(1285, 248, 190, 30, $this->h(date('d M Y', strtotime($r->reservation_date))));          // Date

        $p .= $this->at(490, $this->yReceived, 985, 30, $this->h($r->customer), 'left', true);                  // RECEIVED with thanks from
        $p .= $this->at(150, $this->yOf, 1325, 30, $this->h(implode(', ', $cusaddr)));                          // of

        if (isset($wordLines[0])) { $p .= $this->at(316, $this->ySum1, 1160, 30, $this->h($wordLines[0])); }   // the sum of Rupees
        if (isset($wordLines[1])) { $p .= $this->at(125, $this->ySum2, 1355, 30, $this->h($wordLines[1])); }
        if (isset($wordLines[2])) { $p .= $this->at(125, $this->ySum3, 1355, 30, $this->h($wordLines[2])); }

        $p .= $this->at(357, $this->yAgainst1, 1120, 30, $this->h($against1));                                  // being payment against
        $p .= $this->at(125, $this->yAgainst2, 1355, 30, $this->h($against2));

        $p .= $this->at(188, 730, 244, 36, number_format($amount, 2), 'center', true, 12);                      // Rs.

        // Cash/*Cheque No. and the signature are left empty for hand filling

        $body = '<div style="position:relative;width:' . $this->paperW . 'pt;height:' . ($this->paperH - 4) . 'pt;">' . $p . '</div>';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Advance Receipt ' . $this->h($r->reservation_no) . '</title><style>'
            . '@page { margin: 0; } html, body { margin: 0; padding: 0; } '
            . 'body { font-family: "Courier", monospace; font-size: ' . $this->fontSize . 'pt; line-height: ' . $this->lineHeight . 'pt; color: #000; }'
            . '</style></head><body>' . $body . '</body></html>';
    }

    public function Streampdf($html, $reservationno){
        $this->load->library('pdf');
        $this->pdf->setPaper(array(0, 0, $this->paperW, $this->paperH));
        $this->pdf->loadHtml($html);
        $this->pdf->render();

        while (ob_get_level()) { ob_end_clean(); }

        $this->pdf->stream('Advance Receipt - ' . $reservationno . '.pdf', array('Attachment' => 0));
        exit;
    }

    /* ================= helpers ================= */

    // artwork px -> pt
    private function px($v){
        return round($v * $this->paperW / $this->artW, 2);
    }

    // one absolutely positioned data field; $html must already be escaped
    private function at($x, $y, $w, $h, $html, $align = 'left', $bold = false, $size = null){
        $left = $this->px($x) + $this->shiftX * 2.8346;
        $top  = $this->px($y) + $this->shiftY * 2.8346;
        $st = 'position:absolute;left:' . $left . 'pt;top:' . $top . 'pt;width:' . $this->px($w) . 'pt;height:' . $this->px($h) . 'pt;'
            . 'overflow:hidden;text-align:' . $align . ';';
        if ($bold) { $st .= 'font-weight:bold;'; }
        if ($size) { $st .= 'font-size:' . $size . 'pt;line-height:' . round($size * 1.2, 1) . 'pt;'; }
        if ($this->guide) { $st .= 'outline:0.4pt solid #f00;'; }
        return '<div style="' . $st . '">' . $html . '</div>';
    }

    private function h($v){
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    // the form already prints "the sum of Rupees", so no "Rupees" in the words
    private function amount_words($amt){
        $amt    = round((float)$amt, 2);
        $rupees = (int)floor($amt);
        $cents  = (int)round(($amt - $rupees) * 100);

        $t = $this->words($rupees);
        if ($cents > 0) {
            $t .= ' and ' . $this->words($cents) . ' Cents';
        }
        return $t . ' Only';
    }

    private function words($n){
        $n = (int)$n;
        if ($n == 0) { return 'Zero'; }

        $scales = array(1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand');
        $out = '';
        foreach ($scales as $v => $name) {
            if ($n >= $v) {
                $out .= $this->words_below((int)floor($n / $v)) . ' ' . $name . ' ';
                $n = $n % $v;
            }
        }
        if ($n > 0) { $out .= $this->words_below($n); }
        return trim($out);
    }

    // 1 - 999
    private function words_below($n){
        $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
                      'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
        $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');

        $s = '';
        if ($n >= 100) {
            $s .= $ones[(int)floor($n / 100)] . ' Hundred ';
            $n = $n % 100;
        }
        if ($n >= 20) {
            $s .= $tens[(int)floor($n / 10)];
            if ($n % 10) { $s .= ' ' . $ones[$n % 10]; }
        } else if ($n > 0) {
            $s .= $ones[$n];
        }
        return trim($s);
    }
}