<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Combo extends CI_Controller {
    public function index(){
        $this->load->model('Comboinfo');
        $this->load->model('Commeninfo');
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
        $this->load->view('combo',$result);
    }

    // ajax save (insert / update) - returns JSON {status, msg}
    public function Comboinsertupdate(){
        $this->load->model('Comboinfo');
        $res=$this->Comboinfo->Combosave();

        if($res['status']==1){
            $actionObj=new stdClass();
            $actionObj->icon='fas fa-save';
            $actionObj->title='';
            $actionObj->message=$res['msg'];
            $actionObj->url='';
            $actionObj->target='_blank';
            $actionObj->type='success';
            $this->session->set_flashdata('msg', json_encode($actionObj));
        }
        echo json_encode($res);
    }

    // combo + components + parts warranty (for edit)
    public function Comboedit(){
        $this->load->model('Comboinfo');
        $res=$this->Comboinfo->Combodetail($this->input->post('recordID'), false);
        echo json_encode($res);
    }

    // same as edit but with stock availability (for view modal)
    public function Comboview(){
        $this->load->model('Comboinfo');
        $res=$this->Comboinfo->Combodetail($this->input->post('recordID'), true);
        echo json_encode($res);
    }

    public function Combostatus($x, $y){
        $this->load->model('Comboinfo');
        $this->Comboinfo->Combostatus($x, $y);
    }

    // products that can be put in a combo
    public function Getproducts(){
        $this->load->model('Comboinfo');
        echo json_encode($this->Comboinfo->Getproducts());
    }

    // default warranty of a product (main months + parts)
    public function Productwarranty(){
        $this->load->model('Comboinfo');
        echo json_encode($this->Comboinfo->Productwarranty($this->input->post('productID')));
    }

    // Used by Invoice: expand a combo product into its component lines
    // POST productID (the combo's product id), qty (number of packs), branchID (optional)
    public function Comboexpand(){
        $this->load->model('Comboinfo');
        $qty=(int)$this->input->post('qty'); if($qty<1){$qty=1;}
        echo json_encode($this->Comboinfo->Comboexpand($this->input->post('productID'), $qty, $this->input->post('branchID')));
    }

    public function Searchproducts(){
        $term  = trim($this->input->post('q'));
        $page  = max(1, (int)$this->input->post('page'));
        $limit = 20;
        $start = ($page - 1) * $limit;

        if($term !== ''){
            $like = '%'.$this->db->escape_like_str($term).'%';
            $sql = "SELECT p.idtbl_product, p.product_code, p.product_name, p.model_no, p.cost_price, p.selling_price,
                        GROUP_CONCAT(DISTINCT s.serialno ORDER BY s.serialno SEPARATOR ', ') AS serials
                    FROM tbl_product p
                    LEFT JOIN tbl_product_serial s
                        ON s.tbl_product_idtbl_product = p.idtbl_product
                        AND s.stock_status = 1 AND s.status = 1
                        AND s.serialno LIKE ?
                    WHERE p.status = 1 AND p.is_combo = 0
                    AND (p.product_code LIKE ? OR p.product_name LIKE ? OR p.model_no LIKE ? OR s.idtbl_product_serial IS NOT NULL)
                    GROUP BY p.idtbl_product
                    ORDER BY p.product_name ASC
                    LIMIT ?, ?";
            $rows = $this->db->query($sql, array($like, $like, $like, $like, $start, $limit + 1))->result();
        }else{
            $sql = "SELECT p.idtbl_product, p.product_code, p.product_name, p.model_no, p.cost_price, p.selling_price,
                        NULL AS serials
                    FROM tbl_product p
                    WHERE p.status = 1 AND p.is_combo = 0
                    ORDER BY p.product_name ASC
                    LIMIT ?, ?";
            $rows = $this->db->query($sql, array($start, $limit + 1))->result();
        }

        $more = count($rows) > $limit;
        if($more){ array_pop($rows); }

        $results = array();
        foreach($rows as $r){
            $results[] = array(
                'id'            => $r->idtbl_product,
                'text'          => $r->product_code.' - '.$r->product_name.($r->model_no ? ' ('.$r->model_no.')' : ''),
                'serials'       => $r->serials,
                'product_code'  => $r->product_code,
                'product_name'  => $r->product_name,
                'cost_price'    => $r->cost_price,
                'selling_price' => $r->selling_price
            );
        }

        echo json_encode(array('results' => $results, 'pagination' => array('more' => $more)));
    }
}