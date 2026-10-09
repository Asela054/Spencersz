<?php

class Reportinfo extends CI_Model {

    public function Categoryget() {
        // ASSUMPTION: table tbl_category with columns idtbl_category, category
        $this->db->select('idtbl_category, category');
        $this->db->from('tbl_category');
        $this->db->where('status', 1);

        return $this->db->get();
    }

    public function stockReport() {

        $category  = $this->input->post('category'); // category id
        $companyID = $_SESSION['company_id'];

        $this->db->select('
            tbl_stock.idtbl_stock,
            tbl_stock.batchno,
            tbl_location.location,
            tbl_stock.qty,
            tbl_stock.costunitprice AS unitprice,
            (tbl_stock.qty * tbl_stock.costunitprice) AS total,
            tbl_product.product_code,
            tbl_product.product_name AS materialname,
            tbl_unit.unit AS measure_type,
            tbl_category.category AS `group`
        ', false);

        $this->db->from('tbl_stock');

        $this->db->join('tbl_location',
            'tbl_location.idtbl_location = tbl_stock.tbl_location_idtbl_location', 'left');

        $this->db->join('tbl_product',
            'tbl_product.idtbl_product = tbl_stock.tbl_product_idtbl_product', 'left');

        $this->db->join('tbl_unit',
            'tbl_unit.idtbl_unit = tbl_product.tbl_unit_idtbl_unit', 'left');

        $this->db->join('tbl_category',
            'tbl_category.idtbl_category = tbl_product.tbl_category_idtbl_category', 'left');

        // Common conditions
        $this->db->where('tbl_stock.tbl_company_idtbl_company', $companyID);
        $this->db->where('tbl_stock.tbl_product_idtbl_product IS NOT NULL', null, false);
        $this->db->where_in('tbl_stock.status', [1, 2]);
        $this->db->where('tbl_stock.qty >', 0);   // exclude zero/empty stock rows

        // Category filter
        if ($category != '0' && $category != '') {
            $this->db->where('tbl_product.tbl_category_idtbl_category', $category);
        }

        $query  = $this->db->get();
        $result = $query->result();

        echo json_encode($result);
    }
}