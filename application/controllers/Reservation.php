<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Reservation extends CI_Controller {
    public function index(){
        $this->load->model('Reservationinfo');
        $this->load->model('Commeninfo');
        $result['menuaccess']=$this->Commeninfo->Getmenuprivilege();
        $result['customerlist']=$this->Reservationinfo->Getcustomerlist();
        $result['productlist']=$this->Reservationinfo->Getproductlist();
        $result['vatrate']=$this->Reservationinfo->Getvatrate(date('Y-m-d'));
        $this->load->view('reservation',$result);
    }

    public function Reservationinsertupdate(){
        $this->load->model('Reservationinfo');
        $result=$this->Reservationinfo->Reservationinsertupdate();
    }
    public function Reservationedit(){
        $this->load->model('Reservationinfo');
        $result=$this->Reservationinfo->Reservationedit();
    }
    public function Reservationitems(){
        $this->load->model('Reservationinfo');
        $result=$this->Reservationinfo->Reservationitems();
    }
    public function Reservationvatrate(){
        $this->load->model('Reservationinfo');
        $date=$this->input->post('date');
        echo json_encode(array('rate'=>$this->Reservationinfo->Getvatrate($date)));
    }
    public function Reservationstatus($x, $y){
        $this->load->model('Reservationinfo');
        $result=$this->Reservationinfo->Reservationstatus($x, $y);
    }
    public function Reservationnote($id) {
        $this->load->model('Reservationnoteinfo');

        $data = $this->Reservationnoteinfo->Getnotedata($id);
        if ($data === null) {
            show_404();
            return;
        }

        $html = $this->Reservationnoteinfo->Buildnotehtml($data);
        $this->Reservationnoteinfo->Streampdf($html, $data['r']->reservation_no);
    }
}