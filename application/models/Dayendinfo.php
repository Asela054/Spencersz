<?php
class Dayendinfo extends CI_Model{

    // Rs. notes and coins, largest first
    public $denoms=array(5000, 2000, 1000, 500, 100, 50, 20, 10, 5, 2, 1);

    /* ====================== helpers ====================== */

    // valid, not-in-the-future date or $default
    public function Cleandate($d, $default){
        $d=trim((string)$d);
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)){ return $default; }
        $t=strtotime($d);
        if(!$t || date('Y-m-d', $t)!==$d || $d>date('Y-m-d')){ return $default; }
        return $d;
    }

    public function Getbranches(){
        $this->db->select('idtbl_company_branch AS id, branch AS name');
        $this->db->from('tbl_company_branch');
        $this->db->where('status', 1);
        $this->db->order_by('branch');
        return $this->db->get()->result();
    }

    // cash taken by one cashier on one day (payments.amount is already net of the change given)
    private function cashsales($date, $branch, $user){
        $r=$this->db->query("SELECT COALESCE(SUM(p.amount),0) AS cash
                             FROM tbl_invoice_payment p
                             JOIN tbl_invoice i ON i.idtbl_invoice = p.tbl_invoice_idtbl_invoice
                             WHERE i.invoice_date = ? AND i.tbl_company_branch_idtbl_company_branch = ? AND i.tbl_user_idtbl_user = ?
                             AND i.status = 1 AND p.status = 1 AND p.group_type = 'cash'", array($date, $branch, $user))->row();
        return round((float)$r->cash, 2);
    }

    // [opening, editable]: yesterday's closing of this cashier; the very first time it can be typed
    private function openingfor($date, $branch, $user){
        $r=$this->db->query("SELECT closing_balance FROM tbl_cashier_close
                             WHERE status = 1 AND close_date < ? AND tbl_company_branch_idtbl_company_branch = ? AND tbl_user_idtbl_user = ?
                             ORDER BY close_date DESC LIMIT 1", array($date, $branch, $user))->row();
        return $r ? array(round((float)$r->closing_balance, 2), false) : array(0.0, true);
    }

    // everybody who made an invoice that day (quotations do not count)
    private function invoiceusers($date, $branch){
        return $this->db->query("SELECT DISTINCT u.idtbl_user AS uid, u.name
                                 FROM tbl_invoice i JOIN tbl_user u ON u.idtbl_user = i.tbl_user_idtbl_user
                                 WHERE i.invoice_date = ? AND i.tbl_company_branch_idtbl_company_branch = ?
                                 AND i.status = 1 AND i.invoice_type <> 'Corporate Quotation'", array($date, $branch))->result();
    }

    private function salessummary($date, $branch){
        $s=$this->db->query("SELECT COUNT(*) AS invoices, COALESCE(SUM(gross_total),0) AS gross, COALESCE(SUM(discount),0) AS discount,
                                    COALESCE(SUM(vatamount),0) AS vat, COALESCE(SUM(net_total),0) AS net
                             FROM tbl_invoice
                             WHERE invoice_date = ? AND tbl_company_branch_idtbl_company_branch = ?
                             AND status = 1 AND invoice_type <> 'Corporate Quotation'", array($date, $branch))->row();

        $pays=array();
        $rows=$this->db->query("SELECT p.group_type, COALESCE(SUM(p.amount),0) AS amt
                                FROM tbl_invoice_payment p JOIN tbl_invoice i ON i.idtbl_invoice = p.tbl_invoice_idtbl_invoice
                                WHERE i.invoice_date = ? AND i.tbl_company_branch_idtbl_company_branch = ?
                                AND i.status = 1 AND p.status = 1 GROUP BY p.group_type", array($date, $branch))->result();
        foreach($rows as $p){ $pays[$p->group_type]=round((float)$p->amt, 2); }
        $s->payments=$pays;
        return $s;
    }

    private function dayendrow($date, $branch){
        return $this->db->query("SELECT d.*, u.name AS done_by
                                 FROM tbl_day_end d LEFT JOIN tbl_user u ON u.idtbl_user = d.tbl_user_idtbl_user
                                 WHERE d.day_date = ? AND d.tbl_company_branch_idtbl_company_branch = ? AND d.status = 1", array($date, $branch))->row();
    }

    /* same flash + redirect as Brandinfo */
    private function flash($icon, $message, $type, $back){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$message;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;

        $this->session->set_flashdata('msg', json_encode($actionObj));
        redirect($back);
    }

    // roll back first (redirect() ends the request), then show the message
    private function fail($message, $back){
        $this->db->trans_rollback();
        $this->flash('fas fa-warning', $message, 'danger', $back);
    }

    private function money($n){ return number_format((float)$n, 2); }

    /* ====================== Day End page: data for the screen ====================== */
    public function Dayendpreview(){
        $branch=(int)$_SESSION['branch_id'];
        $userID=(int)$_SESSION['userid'];

        $date=$this->Cleandate($this->input->post('date'), null);
        if(!$date){ echo json_encode(array('ok'=>false, 'msg'=>'Invalid date')); return; }

        $out=new stdClass();
        $out->ok=true;
        $out->date=$date;

        $de=$this->dayendrow($date, $branch);
        $out->dayend_done=$de ? true : false;
        $out->dayend=$de ? array('done_by'=>$de->done_by, 'at'=>$de->insertdatetime) : null;

        // ----- all cashiers of the day -----
        $map=array();
        foreach($this->invoiceusers($date, $branch) as $u){
            $uid=(int)$u->uid;
            $map[$uid]=array('uid'=>$uid, 'name'=>$u->name, 'closed'=>false, 'stale'=>false,
                             'cash_sales'=>$this->cashsales($date, $branch, $uid),
                             'expected'=>null, 'counted'=>null, 'difference'=>null);
        }

        $closes=$this->db->query("SELECT c.*, u.name FROM tbl_cashier_close c JOIN tbl_user u ON u.idtbl_user = c.tbl_user_idtbl_user
                                  WHERE c.close_date = ? AND c.tbl_company_branch_idtbl_company_branch = ? AND c.status = 1", array($date, $branch))->result();
        $mine=null;
        foreach($closes as $c){
            $uid=(int)$c->tbl_user_idtbl_user;
            $live=$this->cashsales($date, $branch, $uid);
            $map[$uid]=array('uid'=>$uid, 'name'=>$c->name, 'closed'=>true,
                             'stale'=>(round($live, 2)!=round((float)$c->cash_sales, 2)),
                             'cash_sales'=>(float)$c->cash_sales, 'expected'=>(float)$c->expected_cash,
                             'counted'=>(float)$c->counted_cash, 'difference'=>(float)$c->difference);
            if($uid===$userID){ $mine=$c; }
        }

        $out->cashiers=array_values($map);
        $out->missing=array();
        $out->stale=array();
        foreach($map as $c){
            if(!$c['closed']){ $out->missing[]=$c['name']; }
            elseif($c['stale']){ $out->stale[]=$c['name']; }
        }

        // ----- my own shift -----
        list($carry, $editable)=$this->openingfor($date, $branch, $userID);
        $opening=($mine && $editable) ? (float)$mine->opening_balance : $carry;

        $qty=array();
        if($mine){
            $dn=$this->db->query("SELECT denomination, qty FROM tbl_cashier_close_denom WHERE tbl_cashier_close_idtbl_cashier_close = ?", array($mine->idtbl_cashier_close))->result();
            foreach($dn as $d){ $qty[(int)$d->denomination]=(int)$d->qty; }
        }
        $nm=$this->db->query("SELECT name FROM tbl_user WHERE idtbl_user = ?", array($userID))->row();

        $out->me=array(
            'name'=> $nm ? $nm->name : '',
            'opening'=> round($opening, 2),
            'opening_editable'=> $editable,
            'cash_sales'=> $this->cashsales($date, $branch, $userID),
            'closed'=> $mine ? true : false,
            'qty'=> (object)$qty,
            'handover'=> $mine ? (float)$mine->handover_amount : 0,
            'remark'=> $mine ? $mine->remark : ''
        );

        // ----- sales summary: the saved snapshot after day end, live numbers before -----
        if($de){
            $pays=json_decode((string)$de->payment_summary, true);
            $out->summary=array('invoices'=>(int)$de->invoice_count, 'gross'=>(float)$de->gross_total, 'discount'=>(float)$de->discount_total,
                                'vat'=>(float)$de->vat_total, 'net'=>(float)$de->net_total, 'payments'=>is_array($pays) ? (object)$pays : (object)array());
        }
        else{
            $s=$this->salessummary($date, $branch);
            $out->summary=array('invoices'=>(int)$s->invoices, 'gross'=>(float)$s->gross, 'discount'=>(float)$s->discount,
                                'vat'=>(float)$s->vat, 'net'=>(float)$s->net, 'payments'=>(object)$s->payments);
        }

        echo json_encode($out);
    }

    /* ====================== cashier close (my shift) ====================== */
    public function Cashierclose(){
        $userID=(int)$_SESSION['userid'];
        $companyID=(int)$_SESSION['company_id'];
        $branch=(int)$_SESSION['branch_id'];
        $now=date('Y-m-d H:i:s');

        $date=$this->Cleandate($this->input->post('date'), null);
        if(!$date){ $this->flash('fas fa-warning', 'Invalid date', 'danger', 'Dayend'); }
        $back='Dayend?date='.$date;

        if($this->dayendrow($date, $branch)){
            $this->flash('fas fa-warning', 'Day end is already completed for this date', 'danger', $back);
        }

        // ----- denominations -----
        $post=$this->input->post('qty');
        if(!is_array($post)){ $post=array(); }
        $counted=0; $rows=array();
        foreach($this->denoms as $d){
            $q=isset($post[$d]) ? trim((string)$post[$d]) : '0';
            if($q===''){ $q='0'; }
            if(!ctype_digit($q)){ $this->flash('fas fa-warning', 'Invalid quantity for Rs. '.$d, 'danger', $back); }
            $q=(int)$q;
            if($q>0){ $rows[]=array('denomination'=>$d, 'qty'=>$q, 'amount'=>$d*$q); }
            $counted+=$d*$q;
        }
        $counted=round($counted, 2);

        $handover=round((float)$this->input->post('handover_amount'), 2);
        if($handover<0 || $handover>$counted){ $this->flash('fas fa-warning', 'Handover amount cannot be more than the counted cash', 'danger', $back); }

        list($carry, $editable)=$this->openingfor($date, $branch, $userID);
        $opening=$editable ? max(0, round((float)$this->input->post('opening_balance'), 2)) : $carry;

        $cash=$this->cashsales($date, $branch, $userID);
        $expected=round($opening+$cash, 2);
        $diff=round($counted-$expected, 2);
        $closing=round($counted-$handover, 2);

        $data=array(
            'opening_balance'=> $opening,
            'cash_sales'=> $cash,
            'expected_cash'=> $expected,
            'counted_cash'=> $counted,
            'difference'=> $diff,
            'handover_amount'=> $handover,
            'closing_balance'=> $closing,
            'remark'=> trim((string)$this->input->post('remark')),
        );

        $this->db->trans_begin();

        $existing=$this->db->query("SELECT idtbl_cashier_close FROM tbl_cashier_close
                                    WHERE close_date = ? AND tbl_company_branch_idtbl_company_branch = ? AND tbl_user_idtbl_user = ? AND status = 1
                                    FOR UPDATE", array($date, $branch, $userID))->row();

        if($existing){
            $closeID=(int)$existing->idtbl_cashier_close;
            $data['updatedatetime']=$now;
            $data['updateuser']=$userID;
            $this->db->where('idtbl_cashier_close', $closeID)->update('tbl_cashier_close', $data);
            $this->db->where('tbl_cashier_close_idtbl_cashier_close', $closeID)->delete('tbl_cashier_close_denom');
        }
        else{
            $data['close_date']=$date;
            $data['status']=1;
            $data['insertdatetime']=$now;
            $data['tbl_user_idtbl_user']=$userID;
            $data['tbl_company_idtbl_company']=$companyID;
            $data['tbl_company_branch_idtbl_company_branch']=$branch;
            $this->db->insert('tbl_cashier_close', $data);
            $closeID=(int)$this->db->insert_id();
        }

        foreach($rows as $r){
            $r['tbl_cashier_close_idtbl_cashier_close']=$closeID;
            $this->db->insert('tbl_cashier_close_denom', $r);
        }

        if($this->db->trans_status()===FALSE){
            $this->fail('Record Error', $back);
        }
        $this->db->trans_commit();

        if($diff<0){ $this->flash('fas fa-save', 'Shift closed. Cash is SHORT by '.$this->money(-$diff), 'warning', $back); }
        elseif($diff>0){ $this->flash('fas fa-save', 'Shift closed. Cash is OVER by '.$this->money($diff), 'primary', $back); }
        $this->flash('fas fa-save', 'Shift closed. Cash is balanced', 'success', $back);
    }

    /* ====================== branch day end ====================== */
    public function Dayendsave(){
        $userID=(int)$_SESSION['userid'];
        $companyID=(int)$_SESSION['company_id'];
        $branch=(int)$_SESSION['branch_id'];
        $now=date('Y-m-d H:i:s');

        $date=$this->Cleandate($this->input->post('date'), null);
        if(!$date){ $this->flash('fas fa-warning', 'Invalid date', 'danger', 'Dayend'); }
        $back='Dayend?date='.$date;

        $this->db->trans_begin();

        $dup=$this->db->query("SELECT 1 FROM tbl_day_end WHERE day_date = ? AND tbl_company_branch_idtbl_company_branch = ? AND status = 1 FOR UPDATE", array($date, $branch))->row();
        if($dup){ $this->fail('Day end is already completed for this date', $back); }

        $closes=$this->db->query("SELECT c.*, u.name FROM tbl_cashier_close c JOIN tbl_user u ON u.idtbl_user = c.tbl_user_idtbl_user
                                  WHERE c.close_date = ? AND c.tbl_company_branch_idtbl_company_branch = ? AND c.status = 1 FOR UPDATE", array($date, $branch))->result();
        $closedUsers=array();
        foreach($closes as $c){ $closedUsers[(int)$c->tbl_user_idtbl_user]=true; }

        // every cashier who made an invoice must have closed
        $missing=array();
        foreach($this->invoiceusers($date, $branch) as $u){
            if(!isset($closedUsers[(int)$u->uid])){ $missing[]=$u->name; }
        }
        if($missing){ $this->fail('Cashier close is not done for: '.implode(', ', $missing), $back); }

        // new cash invoices after a cashier closed -> that close is out of date
        $stale=array();
        $t=array('opening'=>0, 'cash'=>0, 'expected'=>0, 'counted'=>0, 'diff'=>0, 'handover'=>0, 'closing'=>0);
        foreach($closes as $c){
            $live=$this->cashsales($date, $branch, (int)$c->tbl_user_idtbl_user);
            if(round($live, 2)!=round((float)$c->cash_sales, 2)){ $stale[]=$c->name; }
            $t['opening']+=(float)$c->opening_balance;
            $t['cash']+=(float)$c->cash_sales;
            $t['expected']+=(float)$c->expected_cash;
            $t['counted']+=(float)$c->counted_cash;
            $t['diff']+=(float)$c->difference;
            $t['handover']+=(float)$c->handover_amount;
            $t['closing']+=(float)$c->closing_balance;
        }
        if($stale){ $this->fail('New cash invoices were made after the cashier close of: '.implode(', ', $stale).'. Close again first.', $back); }

        $s=$this->salessummary($date, $branch);

        $this->db->insert('tbl_day_end', array(
            'day_date'=> $date,
            'invoice_count'=> (int)$s->invoices,
            'gross_total'=> round((float)$s->gross, 2),
            'discount_total'=> round((float)$s->discount, 2),
            'vat_total'=> round((float)$s->vat, 2),
            'net_total'=> round((float)$s->net, 2),
            'opening_total'=> round($t['opening'], 2),
            'cash_sales_total'=> round($t['cash'], 2),
            'expected_cash'=> round($t['expected'], 2),
            'counted_cash'=> round($t['counted'], 2),
            'difference_total'=> round($t['diff'], 2),
            'handover_total'=> round($t['handover'], 2),
            'closing_total'=> round($t['closing'], 2),
            'payment_summary'=> json_encode($s->payments),
            'status'=> 1,
            'insertdatetime'=> $now,
            'tbl_user_idtbl_user'=> $userID,
            'tbl_company_idtbl_company'=> $companyID,
            'tbl_company_branch_idtbl_company_branch'=> $branch,
        ));
        $dayEndID=(int)$this->db->insert_id();

        $this->db->where('close_date', $date)->where('tbl_company_branch_idtbl_company_branch', $branch)->where('status', 1)
                 ->update('tbl_cashier_close', array('day_end_id'=>$dayEndID));

        if($this->db->trans_status()===FALSE){
            $this->fail('Record Error', $back);
        }
        $this->db->trans_commit();

        $this->flash('fas fa-check-circle', 'Day end completed for '.$date, 'success', $back);
    }

    /* ====================== Cash Balance information ====================== */
    public function Cashbalancedata(){
        $from=$this->Cleandate($this->input->post('from'), date('Y-m-d'));
        $to=$this->Cleandate($this->input->post('to'), date('Y-m-d'));
        if($from>$to){ $tmp=$from; $from=$to; $to=$tmp; }
        $branch=(int)$this->input->post('branch');   // 0 = all branches

        $sql="SELECT c.close_date, c.opening_balance, c.cash_sales, c.expected_cash, c.counted_cash, c.difference,
                     c.handover_amount, c.closing_balance, c.remark, (c.day_end_id IS NOT NULL) AS dayend_done,
                     u.name AS cashier, b.branch AS branch_name
              FROM tbl_cashier_close c
              JOIN tbl_user u ON u.idtbl_user = c.tbl_user_idtbl_user
              LEFT JOIN tbl_company_branch b ON b.idtbl_company_branch = c.tbl_company_branch_idtbl_company_branch
              WHERE c.status = 1 AND c.close_date BETWEEN ? AND ?";
        $params=array($from, $to);
        if($branch>0){ $sql.=" AND c.tbl_company_branch_idtbl_company_branch = ?"; $params[]=$branch; }
        $sql.=" ORDER BY c.close_date DESC, b.branch, u.name";
        $rows=$this->db->query($sql, $params)->result();

        // cash balance in hand now = latest closing balance of every cashier
        $bsql="SELECT b.branch AS branch_name, u.name AS cashier, c.close_date, c.closing_balance
               FROM tbl_cashier_close c
               JOIN tbl_user u ON u.idtbl_user = c.tbl_user_idtbl_user
               LEFT JOIN tbl_company_branch b ON b.idtbl_company_branch = c.tbl_company_branch_idtbl_company_branch
               WHERE c.status = 1 AND c.close_date = (SELECT MAX(c2.close_date) FROM tbl_cashier_close c2
                                                      WHERE c2.status = 1
                                                      AND c2.tbl_company_branch_idtbl_company_branch = c.tbl_company_branch_idtbl_company_branch
                                                      AND c2.tbl_user_idtbl_user = c.tbl_user_idtbl_user)";
        $bparams=array();
        if($branch>0){ $bsql.=" AND c.tbl_company_branch_idtbl_company_branch = ?"; $bparams[]=$branch; }
        $bsql.=" ORDER BY b.branch, u.name";
        $balances=$this->db->query($bsql, $bparams)->result();

        $total=0;
        foreach($balances as $b){ $total+=(float)$b->closing_balance; }

        echo json_encode(array('rows'=>$rows, 'balances'=>$balances, 'balance_total'=>round($total, 2)));
    }
}