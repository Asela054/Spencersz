<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

    /* true if the logged-in user holds the allstockview-check permission */
    public function canViewAllBranches() {
        $uid = (int) ($_SESSION['userid'] ?? 0);
        if ($uid <= 0) {
            return false;
        }

        $q = $this->db->query(
            "SELECT 1
             FROM tbl_user_has_tbl_roles ur
             JOIN tbl_roles_has_tbl_permissions rp
                  ON rp.tbl_roles_idtbl_roles = ur.tbl_roles_idtbl_roles
             JOIN tbl_permissions p
                  ON p.idtbl_permissions = rp.tbl_permissions_idtbl_permissions
             WHERE ur.tbl_user_idtbl_user = ?
               AND p.permission = 'allstockview-check'
               AND p.guard_name = 'web-erp'
             LIMIT 1",
            array($uid)
        );

        return $q->num_rows() > 0;
    }

    /* Returns an array of stock rows. The controller sends it as JSON.
     * $allBranches must already be permission-checked by the caller. */
    public function stockReport($allBranches = false) {

        $category = $this->input->post('category'); // category id
        $supplier = $this->input->post('supplier'); // supplier id
        $branchID = (int) ($_SESSION['branch_id'] ?? 0);

        /* true  = spread the GRN header adjustment (tbl_grn.total) over the lines
         *         (matches the GRN & Return report)
         * false = plain GRN line cost (matches the Purchases report) */
        $applyHeaderAdjustment = true;

        $factor = $applyHeaderAdjustment
            ? "IF(gt.lines_total > 0 AND g.total > 0, g.total / gt.lines_total, 1)"
            : "1";

        $sql = "SELECT
                    s.idtbl_stock,
                    p.model_no,
                    l.branch AS wh_name,
                    s.qty,
                    (IF(gl.qty > 0, gl.total / gl.qty, 0) * $factor) AS unitprice,
                    (s.qty * IF(gl.qty > 0, gl.total / gl.qty, 0) * $factor) AS total,
                    p.product_code,
                    p.product_name AS product_name,
                    un.unit AS measure_type,
                    c.category AS `group`,
                    sup.suppliername AS supplier_name
                FROM tbl_stock s
                /* approved GRN lines, one row per GRN + product */
                JOIN (
                    SELECT gd.tbl_grn_idtbl_grn         AS grn_id,
                           gd.tbl_product_idtbl_product AS pid,
                           SUM(gd.qty)                  AS qty,
                           SUM(gd.total)                AS total
                    FROM tbl_grndetail gd
                    JOIN tbl_grn gg ON gg.idtbl_grn = gd.tbl_grn_idtbl_grn
                    WHERE gd.status = 1
                      AND gg.status = 1
                      AND gg.approvestatus = 1
                    GROUP BY gd.tbl_grn_idtbl_grn, gd.tbl_product_idtbl_product
                ) gl ON gl.grn_id = s.tbl_grn_idtbl_grn
                    AND gl.pid    = s.tbl_product_idtbl_product
                JOIN tbl_grn g ON g.idtbl_grn = gl.grn_id
                /* sum of all line totals per GRN, to compare with tbl_grn.total */
                JOIN (
                    SELECT tbl_grn_idtbl_grn AS grn_id, SUM(total) AS lines_total
                    FROM tbl_grndetail
                    WHERE status = 1
                    GROUP BY tbl_grn_idtbl_grn
                ) gt ON gt.grn_id = g.idtbl_grn
                JOIN tbl_supplier sup ON sup.idtbl_supplier = g.tbl_supplier_idtbl_supplier
                LEFT JOIN tbl_company_branch l  ON l.idtbl_company_branch = s.tbl_company_branch_idtbl_company_branch
                LEFT JOIN tbl_product        p  ON p.idtbl_product  = s.tbl_product_idtbl_product
                LEFT JOIN tbl_unit           un ON un.idtbl_unit    = p.tbl_unit_idtbl_unit
                LEFT JOIN tbl_category       c  ON c.idtbl_category = p.tbl_category_idtbl_category
                WHERE s.tbl_product_idtbl_product IS NOT NULL
                  AND s.status IN (1,2)
                  AND s.qty > 0";

        $binds = [];

        // Branch filter (skipped when showing all branches)
        if (!$allBranches) {
            $sql .= " AND s.tbl_company_branch_idtbl_company_branch = ?";
            $binds[] = $branchID;
        }

        // Category filter
        if ($category != '0' && $category != '') {
            $sql .= " AND p.tbl_category_idtbl_category = ?";
            $binds[] = (int) $category;
        }

        // Supplier filter
        if ($supplier != '0' && $supplier != '') {
            $sql .= " AND g.tbl_supplier_idtbl_supplier = ?";
            $binds[] = (int) $supplier;
        }

        // all branches: keep each branch together so rows are easy to read
        $sql .= $allBranches
            ? " ORDER BY p.product_name, l.branch, s.idtbl_stock"
            : " ORDER BY p.product_name, s.idtbl_stock";

        return $this->db->query($sql, $binds)->result();
    }
}