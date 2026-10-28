<?php
class Productserialinfo extends CI_Model{
    public function Getproductlist(){
        $this->db->select('idtbl_product, product_code, product_name, model_no');
        $this->db->from('tbl_product');
        $this->db->where('status', 1);
        $this->db->where('has_serial', 1);
        $this->db->order_by('product_name', 'ASC');

        $respond=$this->db->get();
        return $respond->result();
    }
    public function Getproductdetail(){
        $productID=$this->input->post('productID');

        // Product with category, brand and unit names
        // (change c.category / b.brand / u.unit if your name columns differ)
        $this->db->select('p.*, c.category AS category_name, b.brand AS brand_name, u.unit AS unit_name');
        $this->db->from('tbl_product p');
        $this->db->join('tbl_category c', 'c.idtbl_category = p.tbl_category_idtbl_category', 'left');
        $this->db->join('tbl_brand b', 'b.idtbl_brand = p.tbl_brand_idtbl_brand', 'left');
        $this->db->join('tbl_unit u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
        $this->db->where('p.idtbl_product', $productID);
        $product=$this->db->get()->row();

        if(empty($product)){
            $obj=new stdClass();
            $obj->status=false;
            echo json_encode($obj);
            return;
        }

        // Counts for the summary cards
        $this->db->select("COUNT(*) AS total,
            SUM(CASE WHEN sold_invoice_id IS NOT NULL AND sold_invoice_id <> 0 THEN 1 ELSE 0 END) AS sold,
            SUM(CASE WHEN warranty_end IS NOT NULL AND warranty_end <> '0000-00-00' AND warranty_end >= CURDATE() THEN 1 ELSE 0 END) AS warrantyactive,
            SUM(CASE WHEN warranty_end IS NOT NULL AND warranty_end <> '0000-00-00' AND warranty_end < CURDATE() THEN 1 ELSE 0 END) AS warrantyexpired", false);
        $this->db->from('tbl_product_serial');
        $this->db->where('tbl_product_idtbl_product', $productID);
        $this->db->where_in('status', array(1, 2));
        $count=$this->db->get()->row();

        $summary=new stdClass();
        $summary->total=(int)$count->total;
        $summary->sold=(int)$count->sold;
        $summary->instock=(int)$count->total-(int)$count->sold;
        $summary->warrantyactive=(int)$count->warrantyactive;
        $summary->warrantyexpired=(int)$count->warrantyexpired;

        $obj=new stdClass();
        $obj->status=true;
        $obj->product=$product;
        $obj->summary=$summary;
        echo json_encode($obj);
    }

    // Generic: moves an IN-STOCK serial to a new stock_status
    private function Markserial($newstatus, $successmsg, $failmsg){
        $userID=$_SESSION['userid'];
        $serialID=$this->input->post('serialID');
        $updatedatetime=date('Y-m-d H:i:s');

        if(empty($serialID)){
            echo json_encode(array('success'=>false, 'message'=>'Invalid serial'));
            return;
        }

        $this->db->trans_begin();

        $this->db->where('idtbl_product_serial', $serialID);
        $this->db->where('status', 1);
        $this->db->where('stock_status', 1);
        $this->db->update('tbl_product_serial', array(
            'stock_status'=>$newstatus,
            'updateuser'=>$userID,
            'updatedatetime'=>$updatedatetime
        ));
        $affected=$this->db->affected_rows();

        if($this->db->trans_status()===FALSE || $affected==0){
            $this->db->trans_rollback();
            echo json_encode(array('success'=>false, 'message'=>$failmsg));
        } else {
            $this->db->trans_commit();
            echo json_encode(array('success'=>true, 'message'=>$successmsg));
        }
    }

    public function Serialdisplay(){
        $this->Markserial('6', 'Serial number marked as display item', 'Only in-stock serial numbers can be marked as display items');
    }

    public function Serialdamaged(){
        $this->Markserial('4', 'Serial number marked as damaged', 'Only in-stock serial numbers can be marked as damaged');
    }

    public function Serialdefective(){
        $this->Markserial('7', 'Serial number marked as defective', 'Only in-stock serial numbers can be marked as defective');
    }
}