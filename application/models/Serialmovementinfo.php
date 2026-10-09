<?php
class Serialmovementinfo extends CI_Model{
    public function Getproducts(){
        $this->db->select('idtbl_product, product_code, product_name');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->order_by('product_name', 'ASC');

        $respond=$this->db->get();
        return $respond->result();
    }
    private function Applyfilters($withType){
        $from=$this->input->post('from');
        $to=$this->input->post('to');
        $type=$this->input->post('type');
        $product=$this->input->post('product');
        $serial=$this->input->post('serial');

        if(!empty($from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)){
            $this->db->where('DATE(m.insertdatetime) >=', $from);
        }
        if(!empty($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)){
            $this->db->where('DATE(m.insertdatetime) <=', $to);
        }
        if($withType && !empty($type)){
            $this->db->where('m.movement_type', (int)$type);
        }
        if(!empty($product)){
            $this->db->where('ps.tbl_product_idtbl_product', (int)$product);
        }
        if(!empty($serial)){
            $this->db->like('ps.serialno', trim($serial), 'both');
        }
    }
    public function Getsummary(){
        // totals (respect every filter, including type)
        $this->db->select('COUNT(*) AS total, COUNT(DISTINCT m.tbl_product_serial_idtbl_product_serial) AS serials', FALSE);
        $this->db->from('tbl_serial_movement m');
        $this->db->join('tbl_product_serial ps', 'ps.idtbl_product_serial = m.tbl_product_serial_idtbl_product_serial', 'left');
        $this->Applyfilters(true);
        $totals=$this->db->get()->row();

        // per movement type (ignore the type filter so the cards stay informative)
        $this->db->select('m.movement_type, COUNT(*) AS cnt', FALSE);
        $this->db->from('tbl_serial_movement m');
        $this->db->join('tbl_product_serial ps', 'ps.idtbl_product_serial = m.tbl_product_serial_idtbl_product_serial', 'left');
        $this->Applyfilters(false);
        $this->db->group_by('m.movement_type');
        $bytype=$this->db->get()->result();

        $types=array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
        foreach($bytype as $rowtype){
            $types[(int)$rowtype->movement_type]=(int)$rowtype->cnt;
        }

        $obj=new stdClass();
        $obj->total=(int)$totals->total;
        $obj->serials=(int)$totals->serials;
        $obj->types=$types;
        echo json_encode($obj);
    }
    public function Getserialtimeline(){
        $serialID=$this->input->post('serialID');

        $this->db->select('ps.idtbl_product_serial, ps.serialno, ps.stock_status, ps.costunitprice, ps.warranty_end, ps.sold_date, ps.insertdatetime,
            p.product_name, p.product_code, g.grn_no');
        $this->db->from('tbl_product_serial ps');
        $this->db->join('tbl_product p', 'p.idtbl_product = ps.tbl_product_idtbl_product', 'left');
        $this->db->join('tbl_grn g', 'g.idtbl_grn = ps.tbl_grn_idtbl_grn', 'left');
        $this->db->where('ps.idtbl_product_serial', $serialID);
        $serial=$this->db->get()->row();

        $this->db->select('m.idtbl_serial_movement, m.movement_type, m.from_status, m.to_status, m.doc_type, m.doc_id, m.doc_detail_id, m.remark, m.insertdatetime,
             u.name AS username, g.grn_no');
        $this->db->from('tbl_serial_movement m');
        $this->db->join('tbl_user u', 'u.idtbl_user = m.tbl_user_idtbl_user', 'left');
        $this->db->join('tbl_grn g', "g.idtbl_grn = m.doc_id AND m.doc_type = 'GRN'", 'left');
        $this->db->where('m.tbl_product_serial_idtbl_product_serial', $serialID);
        $this->db->order_by('m.idtbl_serial_movement', 'ASC');
        $events=$this->db->get()->result();

        $obj=new stdClass();
        $obj->serial=$serial;
        $obj->events=$events;
        echo json_encode($obj);
    }
}