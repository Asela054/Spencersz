<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Stockadjustment extends CI_Controller {
    public function index(){
        $this->load->model('Stockadjustmentinfo');
        $this->load->model('Commeninfo');
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
        $result['branchlist']=$this->Stockadjustmentinfo->Getbranches();
        $this->load->view('stockadjustment',$result);
    }
    public function Getproducts(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Getproducts();
    }
    public function Getbatches(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Getbatches();
    }
    public function Stockadjustmentinsert(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Stockadjustmentinsert();
    }
    public function Stockadjustmentview(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Stockadjustmentview();
    }
    public function Stockadjustmentapprove(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Stockadjustmentapprove();
    }
    public function Stockadjustmentstatus($x, $y){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Stockadjustmentstatus($x, $y);
    }
    public function Getserials(){
        $this->load->model('Stockadjustmentinfo');
        $this->Stockadjustmentinfo->Getserials();
    }
}