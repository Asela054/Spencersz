<?php
class Taxcontrolinfo extends CI_Model {

	public function Taxcontrolinsertupdate() {
        $this->db->trans_begin();

        $userID = $_SESSION['userid'];
        $tax_controlname = $this->input->post('tax_controlname');
        $tax_controldate = $this->input->post('tax_controldate');
        $percentage = $this->input->post('percentage');
        $effective = $this->input->post('effective_from');
        $chart_of_accounts = $this->input->post('chart_of_accounts');
        $recordOption = $this->input->post('recordOption');

        $insertdatetime = date('Y-m-d H:i:s');

        if (!empty($this->input->post('recordID'))) {
            $recordID = $this->input->post('recordID');
        }

        if ($recordOption == 1) {
            $this->updatePreviousEffectiveTo($effective);
        }

        if ($recordOption == 1) {
            // Insert new record
            $data = array(
                'tax_controlname' => $tax_controlname,
                'tax_controldate' => $tax_controldate,
                'percentage' => $percentage,
                'effective_from' => $effective,
                'effective_to' => null, // Set to null for the new record
                'chart_of_accounts' => $chart_of_accounts,
                'status' => '1',
                'insertdatetime' => $insertdatetime,
                'tbl_user_idtbl_user' => $userID,
            );
            $this->db->insert('tbl_tax_control', $data);
        } else {
            // Update existing record
            $data = array(
                'tax_controlname' => $tax_controlname,
                'tax_controldate' => $tax_controldate,
                'percentage' => $percentage,
                'effective_from' => $effective,
                'effective_to' => null, // Set to null for the updated record
                'chart_of_accounts' => $chart_of_accounts,
                'status' => '1',
                'updateuser' => $userID,
                'updatedatetime' => $insertdatetime,
            );
            $this->db->where('id_tax_control', $recordID);
            $this->db->update('tbl_tax_control', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $actionObj = new stdClass();
            $actionObj->icon = 'fas fa-save';
            $actionObj->title = '';
            $actionObj->message = ($recordOption == 1) ? 'Record Added Successfully' : 'Record Update Successfully';
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'success';

            $actionJSON = json_encode($actionObj);

            $this->session->set_flashdata('msg', $actionJSON);
            redirect('Taxcontrol');
        } else {
            $this->db->trans_rollback();

            $actionObj = new stdClass();
            $actionObj->icon = 'fas fa-warning';
            $actionObj->title = '';
            $actionObj->message = 'Record Error';
            $actionObj->url = '';
            $actionObj->target = '_blank';
            $actionObj->type = 'danger';

            $actionJSON = json_encode($actionObj);

            $this->session->set_flashdata('msg', $actionJSON);
            redirect('Taxcontrol');
        }
    }
 
    private function updatePreviousEffectiveTo($effective) {
        $this->db->set('effective_to', date('Y-m-d', strtotime($effective . ' - 1 day')));
        $this->db->where('effective_to IS NULL');
        $this->db->update('tbl_tax_control');
    }

	public function Taxcontroledit() {
		$recordID=$this->input->post('recordID');

		$this->db->select('*');
        $this->db->from('tbl_tax_control');
        $this->db->where('id_tax_control', $recordID);
        $this->db->where('status', 1);

		$respond=$this->db->get();

        $obj=new stdClass();
		$obj->id=$respond->row(0)->id_tax_control;
        $obj->tax_controlname=$respond->row(0)->tax_controlname;
        $obj->tax_controldate=$respond->row(0)->tax_controldate;
        $obj->percentage=$respond->row(0)->percentage;
        $obj->effective=$respond->row(0)->effective_from;
        $obj->chart_of_accounts=$respond->row(0)->chart_of_accounts;

		echo json_encode($obj);
	}

	public function Taxcontrolstatus($x, $y) {
		$this->db->trans_begin();

		$userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

		if ($type==1) {
            $data = array(
                'status' => '1',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );
			$this->db->where('id_tax_control', $recordID);
            $this->db->update('tbl_tax_control', $data);

			$this->db->trans_complete();

			if ($this->db->trans_status() === TRUE) {
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
                redirect('Taxcontrol');                
            } else {
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
                redirect('Taxcontrol');
            }
		}
		else if ($type==2) {
            $data = array(
                'status' => '2',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('id_tax_control', $recordID);
            $this->db->update('tbl_tax_control', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
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
                redirect('Taxcontrol');                
            } else {
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
                redirect('Taxcontrol');
            }
		}
		else if ($type==3) {
            $data = array(
                'status' => '3',
                'updateuser'=> $userID, 
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('id_tax_control', $recordID);
            $this->db->update('tbl_tax_control', $data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
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
                redirect('Taxcontrol');                
            } else {
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
                redirect('Taxcontrol');
            }
        }
	}

}
