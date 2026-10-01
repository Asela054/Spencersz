<?php class Supplierinfo extends CI_Model {

	public function GetSuppliercategory() {
		$this->db->select('idtbl_supplier_type, type');
		$this->db->from('tbl_supplier_type');
		$this->db->where('status', 1);

		return $respond=$this->db->get();
	}


	public function Supplierinsertupdate() {
		$userID=$_SESSION['userid'];

		$supplier_name=$this->input->post('supplier_name');
		$suppliertype=$this->input->post('suppliertype');
		$business_regno=$this->input->post('business_regno');
		$nbtno=$this->input->post('nbtno');
		$svatno=$this->input->post('svatno');
		$telephoneno=$this->input->post('telephoneno');
		$faxno=$this->input->post('faxno');
		$vatno=$this->input->post('vatno');
		$line1=$this->input->post('line1');
		$line2=$this->input->post('line2');
		$city=$this->input->post('city');
		$state=$this->input->post('state');
		$dline1=$this->input->post('dline1');
		$dline2=$this->input->post('dline2');
		$dcity=$this->input->post('dcity');
		$dstate=$this->input->post('dstate');
		$business_status=$this->input->post('bstatus');
		$payementmethod=$this->input->post('payementmethod');
		$credit_days=$this->input->post('credit_days');
		$company_id=$this->input->post('f_company_id');
		$branch_id=$this->input->post('f_branch_id');

		$recordOption=$this->input->post('recordOption');

		if( !empty($this->input->post('recordID'))) {
			$recordID=$this->input->post('recordID');
		}

		$insertdatetime=date('Y-m-d H:i:s');

		if($recordOption==1) {
			try {
				$this->db->trans_begin();

				$data=array(
					'suppliername'=> $supplier_name,
					'bus_reg_no'=> $business_regno,
					'nbt_no'=> $nbtno,
					'svat_no'=> $svatno,
					'telephone_no'=> $telephoneno,
					'fax_no'=> $faxno,
					'address_line1'=> $line1,
					'delivery_address_line1'=> $dline1,
					'address_line2'=> $line2,
					'delivery_address_line2'=> $dline2,
					'city'=> $city,
					'delivery_city'=> $dcity,
					'state'=> $state,
					'delivery_state'=> $dstate,
					'vat_no'=> $vatno,
					'business_status'=> $business_status,
					'payment_method'=> $payementmethod,
					'credit_days'=> $credit_days,
					'tbl_supplier_type_idtbl_supplier_type'=> $suppliertype,
					'tbl_company_idtbl_company'=> $company_id, 
					'tbl_company_branch_idtbl_company_branch'=> $branch_id, 
					'status'=> '1',
					'insertdatetime'=> $insertdatetime,
					'tbl_user_idtbl_user'=> $userID,
				);

				$this->db->insert('tbl_supplier', $data);

				$insertId=$this->db->insert_id();

				if ( !empty($_FILES['image']['name'])) {
					$config1['upload_path']='./images/supplier_br_cetificate';
					$config1['allowed_types']='gif|jpg|png|jpeg';
					$config1['max_size']=10000;
					$this->load->library('upload', $config1);

					$this->upload->initialize($config1);

					if ( !$this->upload->do_upload('image')) {
						return false;
					}

					else {
						$image1_data=$this->upload->data();
						$filedata=array('imagepath'=> $image1_data['file_name'],
						);
						$this->db->where('idtbl_supplier', $insertId);
						$this->db->update('tbl_supplier', $filedata);
					}
				}


				if (!empty($_FILES['cretificates']['name'])) {
					$config2['upload_path']='./images/supplier_bill';
					$config2['allowed_types'] = 'gif|jpg|png|jpeg';
					$config2['max_size'] = 10000;
					$this->load->library('upload', $config2);
		
					$this->upload->initialize($config2);
					if (!$this->upload->do_upload('cretificates')) {
						return false;
					} else {
						$image2_data = $this->upload->data();
						$filedata = array(
							'imagename' => $image2_data['file_name'],
							'size' => '0',
							'extention' => 'Jpeg',
							'status' => '1',
							'insertdatetime' => $insertdatetime,
							'tbl_user_idtbl_user' => $userID,
							'tbl_supplier_idtbl_supplier'=> $insertId,
						);
						$this->db->insert('tbl_supplier_cetificate_bill', $filedata);
					}
				}

				$apiURL = $_SESSION['accountapiurl'].'Api/Createdetailaccount';

				// Use http_build_query for safer parameter encoding
				$postData = http_build_query([
					'userid' => $userID,
					'company' => $company_id,
					'branch' => $branch_id,
					'optiontype' => '1',
					'optionid' => $insertId,
					'optiontext' => $supplier_name
				]);

				$ch = curl_init();
				curl_setopt_array($ch, [
					CURLOPT_URL => $apiURL,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $postData,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTPHEADER => [
						'Content-Type: application/x-www-form-urlencoded',
					]
				]);
				
				$server_output = curl_exec($ch);
				$curlError = curl_error($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				// Check both HTTP status and API response
				$apiResponse = json_decode($server_output, true);

				if ($httpCode != 200 || !isset($apiResponse['status']) || $apiResponse['status'] !== 'success') {
					$errorMsg = $apiResponse['message'] ?? 'API request failed';
					throw new Exception($errorMsg);
				}

				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-save';
				$actionObj->title='';
				$actionObj->message='Record Added Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='success';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
			catch (Exception $e) {
				$this->db->trans_rollback();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-warning';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
		}
		else {
			try {
				$this->db->trans_begin();

				$data=array('suppliername'=> $supplier_name,
					'bus_reg_no'=> $business_regno,
					'nbt_no'=> $nbtno,
					'svat_no'=> $svatno,
					'telephone_no'=> $telephoneno,
					'fax_no'=> $faxno,
					'address_line1'=> $line1,
					'delivery_address_line1'=> $dline1,
					'address_line2'=> $line2,
					'delivery_address_line2'=> $dline2,
					'city'=> $city,
					'delivery_city'=> $dcity,
					'state'=> $state,
					'delivery_state'=> $dstate,
					'vat_no'=> $vatno,
					'business_status'=> $business_status,
					'payment_method'=> $payementmethod,
					'credit_days'=> $credit_days,
					'tbl_supplier_type_idtbl_supplier_type'=> $suppliertype,
					'tbl_company_idtbl_company'=> $company_id, 
					'tbl_company_branch_idtbl_company_branch'=> $branch_id, 
					'status'=> '1',
					'updatedatetime'=> $insertdatetime,
					'tbl_user_idtbl_user'=> $userID,
				);

				$this->db->where('idtbl_supplier', $recordID);
				$this->db->update('tbl_supplier', $data);

				$apiURL = $_SESSION['accountapiurl'].'Api/Createdetailaccount';

				// Use http_build_query for safer parameter encoding
				$postData = http_build_query([
					'userid' => $userID,
					'company' => $company_id,
					'branch' => $branch_id,
					'optiontype' => '1',
					'optionid' => $recordID,
					'optiontext' => $supplier_name
				]);

				$ch = curl_init();
				curl_setopt_array($ch, [
					CURLOPT_URL => $apiURL,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $postData,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTPHEADER => [
						'Content-Type: application/x-www-form-urlencoded',
					]
				]);
				
				$server_output = curl_exec($ch);
				$curlError = curl_error($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				// Check both HTTP status and API response
				$apiResponse = json_decode($server_output, true);

				if ($httpCode != 200 || !isset($apiResponse['status']) || $apiResponse['status'] !== 'success') {
					$errorMsg = $apiResponse['message'] ?? 'API request failed';
					throw new Exception($errorMsg);
				}

				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-save';
				$actionObj->title='';
				$actionObj->message='Record Update Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='primary';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
			catch (Exception $e) {
				$this->db->trans_rollback();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-warning';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
		}
	}

	public function Supplierstatus($x, $y) {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];
		$recordID=$x;
		$type=$y;
		$updatedatetime=date('Y-m-d H:i:s');

		if($type==1) {
			$data=array('status'=> '1',
				'tbl_user_idtbl_user'=> $userID,
				'updatedatetime'=> $updatedatetime);

			$this->db->where('idtbl_supplier', $recordID);
			$this->db->update('tbl_supplier', $data);

			$this->db->trans_complete();

			if ($this->db->trans_status()===TRUE) {
				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-check';
				$actionObj->title='';
				$actionObj->message='Record Activate Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='success';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}

			else {
				$this->db->trans_rollback();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-warning';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
		}

		else if($type==2) {
			$data=array('status'=> '2',
				'tbl_user_idtbl_user'=> $userID,
				'updatedatetime'=> $updatedatetime);

			$this->db->where('idtbl_supplier', $recordID);
			$this->db->update('tbl_supplier', $data);

			$this->db->trans_complete();

			if ($this->db->trans_status()===TRUE) {
				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-times';
				$actionObj->title='';
				$actionObj->message='Record Deactivate Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='warning';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}

			else {
				$this->db->trans_rollback();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-warning';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
		}

		else if($type==3) {
			$data=array('status'=> '3',
				'tbl_user_idtbl_user'=> $userID,
				'updatedatetime'=> $updatedatetime);

			$this->db->where('idtbl_supplier', $recordID);
			$this->db->update('tbl_supplier', $data);

			$this->db->trans_complete();

			if ($this->db->trans_status()===TRUE) {
				$this->db->trans_commit();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-trash-alt';
				$actionObj->title='';
				$actionObj->message='Record Remove Successfully';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}

			else {
				$this->db->trans_rollback();

				$actionObj=new stdClass();
				$actionObj->icon='fas fa-warning';
				$actionObj->title='';
				$actionObj->message='Record Error';
				$actionObj->url='';
				$actionObj->target='_blank';
				$actionObj->type='danger';

				$actionJSON=json_encode($actionObj);

				$this->session->set_flashdata('msg', $actionJSON);
				redirect('Supplier');
			}
		}
	}

	public function Supplieredit() {
		$recordID=$this->input->post('recordID');

		$this->db->select('*');
		$this->db->from('tbl_supplier');
		$this->db->where('idtbl_supplier', $recordID);
		$this->db->where('status', 1);

		$respond=$this->db->get();

		$obj=new stdClass();
		$obj->id=$respond->row(0)->idtbl_supplier;
		$obj->name=$respond->row(0)->suppliername;
		$obj->business_regno=$respond->row(0)->bus_reg_no;
		$obj->nbtno=$respond->row(0)->nbt_no;
		$obj->svatno=$respond->row(0)->svat_no;
		$obj->telephoneno=$respond->row(0)->telephone_no;
		$obj->faxno=$respond->row(0)->fax_no;
		$obj->line1=$respond->row(0)->address_line1;
		$obj->line2=$respond->row(0)->address_line2;
		$obj->city=$respond->row(0)->city;
		$obj->state=$respond->row(0)->state;
		$obj->dline1=$respond->row(0)->delivery_address_line1;
		$obj->dline2=$respond->row(0)->delivery_address_line2;
		$obj->dcity=$respond->row(0)->delivery_city;
		$obj->dstate=$respond->row(0)->delivery_state;
		$obj->business_status=$respond->row(0)->business_status;
		$obj->payementmethod=$respond->row(0)->payment_method;
		$obj->credit_days=$respond->row(0)->credit_days;
		$obj->vat_no=$respond->row(0)->vat_no;
		$obj->type=$respond->row(0)->tbl_supplier_type_idtbl_supplier_type;
		echo json_encode($obj);
	}

	public function Expenseseimageview() {
		$recordID = $this->input->post('recordID');
	
		$this->db->select('suppliername, bus_reg_no, nbt_no, svat_no');
		$this->db->from('tbl_supplier');
		$this->db->where('idtbl_supplier', $recordID);
		$supplier = $this->db->get()->row();
	
		$this->db->select('*');
		$this->db->from('tbl_supplier_cetificate_bill');
		$this->db->where('tbl_supplier_idtbl_supplier', $recordID);
		$this->db->where('status', 1);
	
		$result = $this->db->get()->result();
	
		$html = '';
	
		if ($supplier) {
			$html .= '<div class="supplier-info">';
			$html .= '<h3>Supplier: ' . htmlspecialchars($supplier->suppliername) . '</h3>';
	
			if (!empty($supplier->bus_reg_no)) {
				$html .= '<p>Business Reg No: ' . htmlspecialchars($supplier->bus_reg_no) . '</p>';
			}
			if (!empty($supplier->nbt_no)) {
				$html .= '<p>NBT No: ' . htmlspecialchars($supplier->nbt_no) . '</p>';
			}
			if (!empty($supplier->svat_no)) {
				$html .= '<p>SVAT No: ' . htmlspecialchars($supplier->svat_no) . '</p>';
			}
			
			$html .= '</div>';
		}
	
		if (count($result) == 1) {
			$row = $result[0];
			if (!empty($row->imagename)) {
				$html .= '<img class="bill-image" src="images/supplier_bill/' . htmlspecialchars($row->imagename) . '" width="100%" height="100%"/>';
			}
		} else {
			$html .= '<div class="image-gallery">';
			
			foreach ($result as $row) {
				if (!empty($row->imagename)) {
					$html .= '<img class="bill-image" src="images/supplier_bill/' . htmlspecialchars($row->imagename) . '" width="100%" height="100%" style="margin: 6px; border-radius: 8px; margin-left: auto; margin-right: auto;" />';
				}
			}
			
			$html .= '</div>';
		}
	
		echo $html;
	}	
}