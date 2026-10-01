<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Goodreceivereturn extends CI_Controller {
    public function index(){
        $this->load->model('Commeninfo');
        $this->load->model('Goodreceivereturninfo');
        $result['menuaccess']  = $this->Commeninfo->Getmenuprivilege();
        $result['supplierlist']= $this->Goodreceivereturninfo->Getsupplier();
        $result['ordertypelist']=$this->Goodreceivereturninfo->Getordertype();
        $result['measurelist'] = $this->Goodreceivereturninfo->Getmeasuretype();
        $this->load->view('goodreceivereturn', $result);
    }

    public function Getgrnaccsupllier(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Getgrnaccsupllier();
    }

    public function Getordertypesetgrn(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Getordertypesetgrn();
    }

    public function Getproducts(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Getproducts();
    }

    public function Getproductdetails(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Getproductdetails();
    }

    public function Goodreceivereturninsertupdate(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Goodreceivereturninsertupdate();
    }

    public function Goodreceivereturnstatus($x, $y){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Goodreceivereturnstatus($x, $y);
    }

    public function Goodreceivereturnview(){
        $this->load->model('Goodreceivereturninfo');
        $this->Goodreceivereturninfo->Goodreceivereturnview();
    }
}