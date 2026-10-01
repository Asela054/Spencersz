<?php
class Commeninfo extends CI_Model{
    public function Getmenuprivilege(){
        $userID=$_SESSION['userid'];

        // $menuprivilegearray=array();
    
        // $sql="SELECT `idtbl_menu_list`, `menu` FROM `tbl_menu_list` WHERE `status`=?";
        // $respond=$this->db->query($sql, array(1));
        
        // foreach($respond->result() as $row){
        //     $menucheckID=$row->idtbl_menu_list;
        //     $menuname=str_replace(" ","_",$row->menu);
            
        //     $sqlprivilegecheck="SELECT `add`, `edit`, `statuschange`, `remove`, `access_status`, `approvestatus`, `checkstatus`, `tbl_menu_list_idtbl_menu_list` FROM `tbl_user_privilege` WHERE `tbl_user_idtbl_user`=? AND `tbl_menu_list_idtbl_menu_list`=? AND `status`=?";
        //     $respondprivilegecheck=$this->db->query($sqlprivilegecheck, array($userID, $menucheckID, 1));
            
        //     if($respondprivilegecheck->num_rows()>0){
        //         $objmenu=new stdClass();
        //         $objmenu->add=$respondprivilegecheck->row(0)->add;
        //         $objmenu->edit=$respondprivilegecheck->row(0)->edit;
        //         $objmenu->statuschange=$respondprivilegecheck->row(0)->statuschange;
        //         $objmenu->remove=$respondprivilegecheck->row(0)->remove;
        //         $objmenu->access_status=$respondprivilegecheck->row(0)->access_status;
        //         $objmenu->approvestatus=$respondprivilegecheck->row(0)->approvestatus;
        //         $objmenu->checkstatus=$respondprivilegecheck->row(0)->checkstatus;
        //         $objmenu->menuid=$respondprivilegecheck->row(0)->tbl_menu_list_idtbl_menu_list;
        //         array_push($menuprivilegearray, $objmenu);
        //     }
        // }

        // return $menuprivilegearray;

        $this->db->select('permission, module, permission_type');
        $this->db->from('tbl_permissions');
        $this->db->join('tbl_roles_has_tbl_permissions', 'tbl_permissions.idtbl_permissions = tbl_roles_has_tbl_permissions.tbl_permissions_idtbl_permissions', 'left');
        $this->db->join('tbl_user_has_tbl_roles', 'tbl_roles_has_tbl_permissions.tbl_roles_idtbl_roles = tbl_user_has_tbl_roles.tbl_roles_idtbl_roles', 'left');
        $this->db->where('tbl_user_has_tbl_roles.tbl_user_idtbl_user', $userID);
        $this->db->where('tbl_permissions.guard_name', 'web-erp');
        $query = $this->db->get();
        return $query->result();
    }
}