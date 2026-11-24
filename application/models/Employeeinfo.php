<?php
class Employeeinfo extends CI_Model{

    private $types=array(1, 2, 3, 4);   // 1 Permanent, 2 Contract, 3 Probation, 4 Part-time

    /* ---------- lists for the view ---------- */
    public function Getbranches(){
        $this->db->select('idtbl_company_branch AS id, branch AS name');
        $this->db->from('tbl_company_branch');
        $this->db->where('status', 1);
        $this->db->order_by('branch');

        return $this->db->get()->result();
    }

    // $col is only ever 'department' or 'designation' (never user input)
    public function Getdistinct($col){
        if(!in_array($col, array('department', 'designation'))){ return array(); }

        $respond=$this->db->query("SELECT DISTINCT $col AS v FROM tbl_employee
                                   WHERE status IN (1,2) AND $col IS NOT NULL AND $col <> '' ORDER BY $col");
        return array_column($respond->result_array(), 'v');
    }

    /* ---------- insert / update ---------- */
    public function Employeeinsertupdate(){
        $userID=$_SESSION['userid'];
        $companyID=$_SESSION['company_id'];

        $recordOption=$this->input->post('recordOption');
        $recordID=0;
        if(!empty($this->input->post('recordID'))){$recordID=(int)$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        $p=function($k){ return trim((string)$this->input->post($k)); };
        $date=function($v){ return ($v!=='' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null; };

        $fullname=$p('full_name');
        $nic=strtoupper($p('nic'));
        $phone=$p('phone');
        $email=$p('email');
        $branch=(int)$this->input->post('branch_id');
        $type=(int)$this->input->post('employment_type');
        $status=(int)$this->input->post('status');
        $salary=$p('basic_salary');
        $join=$date($p('join_date'));
        $resign=$date($p('resign_date'));

        // ----- validation (server side) -----
        if($fullname===''){ $this->flash('fas fa-warning', 'Enter the full name', 'danger'); }
        if(!preg_match('/^([0-9]{9}[VX]|[0-9]{12})$/', $nic)){ $this->flash('fas fa-warning', 'Enter a valid NIC (9 digits + V/X, or 12 digits)', 'danger'); }
        if($phone!=='' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)){ $this->flash('fas fa-warning', 'Invalid phone number', 'danger'); }
        if($email!=='' && !filter_var($email, FILTER_VALIDATE_EMAIL)){ $this->flash('fas fa-warning', 'Invalid email address', 'danger'); }
        if(!$branch){ $this->flash('fas fa-warning', 'Select the branch', 'danger'); }
        if(!in_array($type, $this->types)){ $this->flash('fas fa-warning', 'Select the employment type', 'danger'); }
        if(!in_array($status, array(1, 2))){ $status=1; }
        if($salary!=='' && (!is_numeric($salary) || $salary<0)){ $this->flash('fas fa-warning', 'Invalid basic salary', 'danger'); }
        if($p('join_date')!=='' && !$join){ $this->flash('fas fa-warning', 'Invalid join date', 'danger'); }
        if($p('resign_date')!=='' && !$resign){ $this->flash('fas fa-warning', 'Invalid resign date', 'danger'); }
        if($join && $resign && $resign<$join){ $this->flash('fas fa-warning', 'Resign date cannot be before the join date', 'danger'); }

        $dup=$this->db->query("SELECT 1 FROM tbl_employee WHERE nic = ? AND status <> 3 AND idtbl_employee <> ? LIMIT 1", array($nic, $recordID))->row();
        if($dup){ $this->flash('fas fa-warning', 'An employee with this NIC already exists', 'danger'); }

        $data = array(
            'full_name'=> $fullname,
            'name_with_initials'=> $p('name_with_initials'),
            'nic'=> $nic,
            'dob'=> $date($p('dob')),
            'gender'=> in_array($p('gender'), array('Male', 'Female', 'Other')) ? $p('gender') : null,
            'marital_status'=> in_array($p('marital_status'), array('Single', 'Married', 'Other')) ? $p('marital_status') : null,
            'address'=> $p('address'),
            'phone'=> $phone,
            'email'=> $email,
            'emergency_name'=> $p('emergency_name'),
            'emergency_phone'=> $p('emergency_phone'),
            'designation'=> $p('designation'),
            'department'=> $p('department'),
            'join_date'=> $join,
            'resign_date'=> $resign,
            'employment_type'=> $type,
            'epf_no'=> $p('epf_no'),
            'basic_salary'=> $salary==='' ? 0 : round((float)$salary, 2),
            'bank_name'=> $p('bank_name'),
            'bank_branch'=> $p('bank_branch'),
            'bank_account_no'=> $p('bank_account_no'),
            'remark'=> $p('remark'),
            'status'=> $status,
            'tbl_company_branch_idtbl_company_branch'=> $branch,
        );

        $this->db->trans_begin();

        if($recordOption==1){
            // employee number is generated inside the transaction (FOR UPDATE stops duplicates)
            $last=$this->db->query("SELECT MAX(CAST(SUBSTRING(emp_no, 4) AS UNSIGNED)) AS m FROM tbl_employee FOR UPDATE")->row();
            $empNo='EMP'.str_pad(((int)$last->m)+1, 4, '0', STR_PAD_LEFT);

            $data['emp_no']=$empNo;
            $data['insertdatetime']=$insertdatetime;
            $data['tbl_user_idtbl_user']=$userID;
            $data['tbl_company_idtbl_company']=$companyID;

            $this->db->insert('tbl_employee', $data);

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->flash('fas fa-save', 'Record Added Successfully ('.$empNo.')', 'success');
            } else {
                $this->db->trans_rollback();
                $this->flash('fas fa-warning', 'Record Error', 'danger');
            }
        }
        else{
            $data['updatedatetime']=$insertdatetime;
            $data['updateuser']=$userID;

            $this->db->where('idtbl_employee', $recordID);
            $this->db->where('status <>', 3);
            $this->db->update('tbl_employee', $data);

            if ($this->db->trans_status() === TRUE) {
                $this->db->trans_commit();
                $this->flash('fas fa-save', 'Record Update Successfully', 'primary');
            } else {
                $this->db->trans_rollback();
                $this->flash('fas fa-warning', 'Record Error', 'danger');
            }
        }
    }

    /* ---------- status: 1 activate, 2 deactivate, 3 remove ---------- */
    public function Employeestatus($x, $y){
        $userID=$_SESSION['userid'];
        $recordID=(int)$x;
        $type=(int)$y;
        $updatedatetime=date('Y-m-d H:i:s');

        $messages=array(
            1=> array('fas fa-check', 'Record Activate Successfully', 'success'),
            2=> array('fas fa-times', 'Record Deactivate Successfully', 'warning'),
            3=> array('fas fa-trash-alt', 'Record Remove Successfully', 'danger'),
        );
        if(!isset($messages[$type])){ $this->flash('fas fa-warning', 'Record Error', 'danger'); }

        $this->db->trans_begin();

        $data = array(
            'status' => (string)$type,
            'updateuser'=> $userID, 
            'updatedatetime'=> $updatedatetime
        );

        $this->db->where('idtbl_employee', $recordID);
        $this->db->where('status <>', 3);
        $this->db->update('tbl_employee', $data);

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();
            $this->flash($messages[$type][0], $messages[$type][1], $messages[$type][2]);
        } else {
            $this->db->trans_rollback();
            $this->flash('fas fa-warning', 'Record Error', 'danger');
        }
    }

    /* ---------- edit (returns JSON to fill the form) ---------- */
    public function Employeeedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_employee');
        $this->db->where('idtbl_employee', $recordID);
        $this->db->where_in('status', array(1, 2));

        $respond=$this->db->get();

        if($respond->num_rows()==0){
            echo json_encode(new stdClass());
            return;
        }

        $obj=$respond->row(0);
        $obj->id=$obj->idtbl_employee;
        $obj->branch_id=$obj->tbl_company_branch_idtbl_company_branch;
        echo json_encode($obj);
    }

    /* ---------- same flash + redirect as Brandinfo ---------- */
    private function flash($icon, $message, $type){
        $actionObj=new stdClass();
        $actionObj->icon=$icon;
        $actionObj->title='';
        $actionObj->message=$message;
        $actionObj->url='';
        $actionObj->target='_blank';
        $actionObj->type=$type;

        $actionJSON=json_encode($actionObj);

        $this->session->set_flashdata('msg', $actionJSON);
        redirect('Employee');
    }
}