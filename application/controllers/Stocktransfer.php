<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Stocktransfer extends CI_Controller {
    public function index(){
        $this->load->model('Stocktransferinfo');
        $this->load->model('Commeninfo');
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
        $this->load->view('stocktransfer',$result);
    }

    public function Getbootdata(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Getbootdata();
    }
    public function Getlist(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Getlist();
    }
    public function Gettransfer(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Gettransfer();
    }
    public function Getproducts(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Getproducts();
    }
    public function Getserials(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Getserials();
    }
    public function Stocktransferinsert(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Stocktransferinsert();
    }
    public function Stocktransferadvance(){
        $this->load->model('Stocktransferinfo');
        $result=$this->Stocktransferinfo->Stocktransferadvance();
    }
}