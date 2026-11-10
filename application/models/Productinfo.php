<?php
class Productinfo extends CI_Model{

    /* Saves the extra warranties of a product.
       Old active rows are soft-deleted (status 3), then the submitted rows are inserted again. */
    private function Savewarranties($productID, $userID, $datetime){
        $names  = $this->input->post('warranty_name');
        $months = $this->input->post('warranty_part_months');

        $this->db->where('tbl_product_idtbl_product', $productID);
        $this->db->where('status', 1);
        $this->db->update('tbl_product_warranty', array(
            'status'=> '3',
            'updateuser'=> $userID,
            'updatedatetime'=> $datetime
        ));

        if(!empty($names) && is_array($names)){
            foreach($names as $i => $name){
                $name = trim($name);
                $m = isset($months[$i]) ? (int)$months[$i] : 0;
                if($name === '' || $m <= 0){ continue; }

                $this->db->insert('tbl_product_warranty', array(
                    'warranty_name'=> $name,
                    'warranty_months'=> $m,
                    'status'=> '1',
                    'insertdatetime'=> $datetime,
                    'tbl_user_idtbl_user'=> $userID,
                    'tbl_product_idtbl_product'=> $productID
                ));
            }
        }
    }

    public function Productinsertupdate(){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];

        $product_code=$this->input->post('product_code');
        $barcode=$this->input->post('barcode');
        $product_name=$this->input->post('product_name');
        $model_no=$this->input->post('model_no');
        $description=$this->input->post('description');
        $tbl_category_idtbl_category=!empty($this->input->post('tbl_category_idtbl_category')) ? $this->input->post('tbl_category_idtbl_category') : NULL;
        $tbl_brand_idtbl_brand=!empty($this->input->post('tbl_brand_idtbl_brand')) ? $this->input->post('tbl_brand_idtbl_brand') : NULL;
        $tbl_unit_idtbl_unit=!empty($this->input->post('tbl_unit_idtbl_unit')) ? $this->input->post('tbl_unit_idtbl_unit') : NULL;
        $cost_price=!empty($this->input->post('cost_price')) ? $this->input->post('cost_price') : 0;
        $selling_price=!empty($this->input->post('selling_price')) ? $this->input->post('selling_price') : 0;
        $reorder_level=!empty($this->input->post('reorder_level')) ? $this->input->post('reorder_level') : 0;
        $has_serial=!empty($this->input->post('has_serial')) ? $this->input->post('has_serial') : 0;
        $warranty_months=!empty($this->input->post('warranty_months')) ? $this->input->post('warranty_months') : 0;

        $recordOption=$this->input->post('recordOption');
        if(!empty($this->input->post('recordID'))){$recordID=$this->input->post('recordID');}

        $insertdatetime=date('Y-m-d H:i:s');

        if($recordOption==1){
            $data = array(
                'product_code'=> $product_code,
                'barcode'=> $barcode,
                'product_name'=> $product_name,
                'model_no'=> $model_no,
                'description'=> $description,
                'tbl_category_idtbl_category'=> $tbl_category_idtbl_category,
                'tbl_brand_idtbl_brand'=> $tbl_brand_idtbl_brand,
                'tbl_unit_idtbl_unit'=> $tbl_unit_idtbl_unit,
                'cost_price'=> $cost_price,
                'selling_price'=> $selling_price,
                'reorder_level'=> $reorder_level,
                'has_serial'=> $has_serial,
                'warranty_months'=> $warranty_months,
                'status'=> '1',
                'insertdatetime'=> $insertdatetime,
                'tbl_user_idtbl_user'=> $userID,
            );

            $this->db->insert('tbl_product', $data);
            $newProductID = $this->db->insert_id();

            // extra warranties (compressor, motor ...)
            $this->Savewarranties($newProductID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
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
                redirect('Product');
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
                redirect('Product');
            }
        }
        else{
            $data = array(
                'product_code'=> $product_code,
                'barcode'=> $barcode,
                'product_name'=> $product_name,
                'model_no'=> $model_no,
                'description'=> $description,
                'tbl_category_idtbl_category'=> $tbl_category_idtbl_category,
                'tbl_brand_idtbl_brand'=> $tbl_brand_idtbl_brand,
                'tbl_unit_idtbl_unit'=> $tbl_unit_idtbl_unit,
                'cost_price'=> $cost_price,
                'selling_price'=> $selling_price,
                'reorder_level'=> $reorder_level,
                'has_serial'=> $has_serial,
                'warranty_months'=> $warranty_months,
                'status'=> '1',
                'updatedatetime'=> $insertdatetime,
                'updateuser'=> $userID,
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

            // extra warranties (compressor, motor ...)
            $this->Savewarranties($recordID, $userID, $insertdatetime);

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
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
                redirect('Product');
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
                redirect('Product');
            }
        }
    }

    public function Productstatus($x, $y){
        $this->db->trans_begin();

        $userID=$_SESSION['userid'];
        $recordID=$x;
        $type=$y;
        $updatedatetime=date('Y-m-d H:i:s');

        if($type==1){
            $data = array(
                'status' => '1',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

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
                redirect('Product');
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
                redirect('Product');
            }
        }
        else if($type==2){
            $data = array(
                'status' => '2',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

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
                redirect('Product');
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
                redirect('Product');
            }
        }
        else if($type==3){
            $data = array(
                'status' => '3',
                'updateuser'=> $userID,
                'updatedatetime'=> $updatedatetime
            );

            $this->db->where('idtbl_product', $recordID);
            $this->db->update('tbl_product', $data);

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
                redirect('Product');
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
                redirect('Product');
            }
        }
    }

    public function Productedit(){
        $recordID=$this->input->post('recordID');

        $this->db->select('*');
        $this->db->from('tbl_product');
        $this->db->where('idtbl_product', $recordID);
        $this->db->where('status', 1);

        $respond=$this->db->get();

        $obj=new stdClass();
        $obj->id=$respond->row(0)->idtbl_product;
        $obj->product_code=$respond->row(0)->product_code;
        $obj->barcode=$respond->row(0)->barcode;
        $obj->product_name=$respond->row(0)->product_name;
        $obj->model_no=$respond->row(0)->model_no;
        $obj->description=$respond->row(0)->description;
        $obj->tbl_category_idtbl_category=$respond->row(0)->tbl_category_idtbl_category;
        $obj->tbl_brand_idtbl_brand=$respond->row(0)->tbl_brand_idtbl_brand;
        $obj->tbl_unit_idtbl_unit=$respond->row(0)->tbl_unit_idtbl_unit;
        $obj->cost_price=$respond->row(0)->cost_price;
        $obj->selling_price=$respond->row(0)->selling_price;
        $obj->reorder_level=$respond->row(0)->reorder_level;
        $obj->has_serial=$respond->row(0)->has_serial;
        $obj->warranty_months=$respond->row(0)->warranty_months;

        // extra warranties
        $this->db->select('warranty_name, warranty_months');
        $this->db->from('tbl_product_warranty');
        $this->db->where('tbl_product_idtbl_product', $recordID);
        $this->db->where('status', 1);
        $this->db->order_by('idtbl_product_warranty', 'ASC');
        $obj->warranties=$this->db->get()->result();

        echo json_encode($obj);
    }

    public function Getcategorylist(){
        $this->db->select('idtbl_category, category');
        $this->db->from('tbl_category');
        $this->db->where('status', 1);
        $this->db->order_by('category', 'ASC');
        return $this->db->get()->result();
    }
    public function Getbrandlist(){
        $this->db->select('idtbl_brand, brand');
        $this->db->from('tbl_brand');
        $this->db->where('status', 1);
        $this->db->order_by('brand', 'ASC');
        return $this->db->get()->result();
    }
    public function Getunitlist(){
        $this->db->select('idtbl_unit, unit');
        $this->db->from('tbl_unit');
        $this->db->where('status', 1);
        $this->db->order_by('unit', 'ASC');
        return $this->db->get()->result();
    }
}