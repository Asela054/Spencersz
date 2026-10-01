<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('Asia/Colombo');

class Api extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->model("Apiinfo");
    }
    public function StockWebhook(){
        $data = json_decode(file_get_contents('php://input'), true);
        $this->Apiinfo->insertStockWebhook($data);
    }
}