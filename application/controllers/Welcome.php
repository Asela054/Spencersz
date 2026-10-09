<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->view('login');
	}

	public function LoginUser(){
		$this->load->model('Userinfo');
        $result=$this->Userinfo->LoginUser();
		$AccountAPIURL='http://localhost/accountscode/';

        if($result['user_data']!=false){
            $user_data=array(
                'userid'=>$result['user_data']->idtbl_user,
                'name'=>$result['user_data']->name,
                'type'=>$result['user_data']->idtbl_user_type,
                'typename'=>$result['user_data']->type,
				'company_id'=>$result['company_id'],
				'companyname'=>$result['company_name'],
				'branch_id'=>$result['branch_id'],
				'branchname'=>$result['branch_name'],
				'accountapiurl'=>$AccountAPIURL,
                'loggedin'=>true
            );

			$this->session->set_userdata($user_data);

			redirect('Welcome/Dashboard');
        }
        else{
            $this->session->set_flashdata('msg', 'Invalid Username or password');
            redirect();
        }
	}

	public function Logout(){
        $this->session->unset_userdata('userid');
        $this->session->unset_userdata('name');
        $this->session->unset_userdata('type');
        $this->session->unset_userdata('typename');
		$this->session->unset_userdata('company_id');
		$this->session->unset_userdata('companyname');
		$this->session->unset_userdata('branch_id');
		$this->session->unset_userdata('branchname');
		$this->session->unset_userdata('accountapiurl');
        $this->session->unset_userdata('loggedin');
        $this->cart->destroy();

        redirect(base_url());
    }

	public function Dashboard(){
		$this->load->model('Commeninfo');
		$this->load->model('DashboardInfo');

		$result['menuaccess'] = $this->Commeninfo->Getmenuprivilege();

		// stat cards
		$result['cards'] = $this->DashboardInfo->Cards();

		// tables
		$result['lowstock']   = $this->DashboardInfo->LowStockTable(8);
		$result['recentgrn']  = $this->DashboardInfo->RecentGrn(6);
		$result['recentinv']  = $this->DashboardInfo->RecentInvoices(6);
		$result['recentret']  = $this->DashboardInfo->RecentGrnReturns(6);

		// chart data (json for Chart.js)
		$result['daily']      = json_encode($this->DashboardInfo->DailySeries(7));
		$result['monthly']    = json_encode($this->DashboardInfo->MonthlySeries(12));
		$result['bycategory'] = json_encode($this->DashboardInfo->StockByCategory());
		$result['bybrand']    = json_encode($this->DashboardInfo->StockByBrand());
		$result['topproducts']= json_encode($this->DashboardInfo->TopProducts(5));
		$result['serials']    = json_encode($this->DashboardInfo->SerialStatus());

		$this->load->view('dashboard', $result);
	}

	public function Getbranchaccocompany(){
		$recordID=$this->input->post('company_id');
        $result=CompanyBranchList($recordID);
	}

	public function DailyChartData(){
		$this->load->model('DashboardInfo');
		$endDate = $this->input->post('enddate');
		$days    = (int) $this->input->post('days');
		if (!in_array($days, array(7, 14, 30))) { $days = 7; }

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($this->DashboardInfo->DailySeries($days, $endDate)));
	}

	public function MonthlyChartData(){
		$this->load->model('DashboardInfo');
		$endMonth = $this->input->post('endmonth');

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($this->DashboardInfo->MonthlySeries(12, $endMonth)));
	}
}