<?php

class Reportinfo extends CI_Model {

    public function Categoryget() {
        $this->db->select('idtbl_category, category');
        $this->db->from('tbl_category');
        $this->db->where('status', 1);

        return $this->db->get();
    }

    public function Supplierget() {
        $this->db->select('idtbl_supplier, suppliername');
        $this->db->from('tbl_supplier');
        $this->db->where('status', 1);
        $this->db->order_by('suppliername', 'ASC');

        return $this->db->get();
    }

    public function stockReport() {

        $category = $this->input->post('category'); // category id
        $supplier = $this->input->post('supplier'); // supplier id
        $branchID = $_SESSION['branch_id'];

        $this->db->select('
            tbl_stock.idtbl_stock,
            tbl_stock.batchno,
            tbl_location.location AS wh_name,
            tbl_stock.qty,
            tbl_stock.costunitprice AS unitprice,
            (tbl_stock.qty * tbl_stock.costunitprice) AS total,
            tbl_product.product_code,
            tbl_product.product_name AS product_name,
            tbl_unit.unit AS measure_type,
            tbl_category.category AS `group`,
            tbl_supplier.suppliername AS supplier_name
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

        // Supplier comes from the GRN that received this stock
        $this->db->join('tbl_grn',
            'tbl_grn.idtbl_grn = tbl_stock.tbl_grn_idtbl_grn', 'left');

        $this->db->join('tbl_supplier',
            'tbl_supplier.idtbl_supplier = tbl_grn.tbl_supplier_idtbl_supplier', 'left');

        // Common conditions
        $this->db->where('tbl_stock.tbl_company_branch_idtbl_company_branch', $branchID);
        $this->db->where('tbl_stock.tbl_product_idtbl_product IS NOT NULL', null, false);
        $this->db->where_in('tbl_stock.status', [1, 2]);
        $this->db->where('tbl_stock.qty >', 0);

        // Category filter
        if ($category != '0' && $category != '') {
            $this->db->where('tbl_product.tbl_category_idtbl_category', $category);
        }

        // Supplier filter
        if ($supplier != '0' && $supplier != '') {
            $this->db->where('tbl_grn.tbl_supplier_idtbl_supplier', (int) $supplier);
        }

        $query  = $this->db->get();
        $result = $query->result();

        echo json_encode($result);
    }
}