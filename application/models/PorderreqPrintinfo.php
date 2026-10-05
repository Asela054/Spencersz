<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class PorderreqPrintinfo extends CI_Model {

    private $rowsPerPage = 8;

    public function Printinvoice($x){
        $recordID = (int)$x;

        /* ---------- header (request + company + branch + checker) ---------- */
        $this->db->select('r.idtbl_porder_req, r.porder_req_no, r.date, r.confirmstatus, r.tbl_company_idtbl_company,
                           c.company AS companyname, c.address1 AS companyaddress, c.mobile AS companymobile,
                           c.phone AS companyphone, c.email AS companyemail,
                           b.branch AS branchname,
                           cu.name AS checkedbyname');
        $this->db->from('tbl_porder_req AS r');
        $this->db->join('tbl_company AS c', 'c.idtbl_company = r.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch AS b', 'b.idtbl_company_branch = r.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS cu', 'cu.idtbl_user = r.check_by', 'left');
        $this->db->where('r.idtbl_porder_req', $recordID);
        $this->db->where_in('r.status', array(1, 2));
        $headerQuery = $this->db->get();

        if ($headerQuery->num_rows() == 0) {
            show_404();
            return;
        }
        $header = $headerQuery->row();

        /* ---------- document number prefix by company ---------- */
        $prefix = 'SP';
        if ($header->tbl_company_idtbl_company == 2) {
            $prefix = 'FT';
        } elseif ($header->tbl_company_idtbl_company == 3) {
            $prefix = 'RM';
        }

        /* ---------- detail lines ---------- */
        $this->db->select('d.qty, d.comment, p.product_code, p.product_name, p.model_no, u.unit, u.unit_short');
        $this->db->from('tbl_porder_req_detail AS d');
        $this->db->join('tbl_product AS p', 'p.idtbl_product = d.tbl_product_idtbl_product', 'left');
        $this->db->join('tbl_unit AS u', 'u.idtbl_unit = p.tbl_unit_idtbl_unit', 'left');
        $this->db->where('d.tbl_porder_req_idtbl_porder_req', $recordID);
        $this->db->where('d.status', 1);
        $this->db->order_by('d.idtbl_porder_req_detail', 'ASC');
        $detailQuery = $this->db->get();

        /* ---------- split lines into pages ---------- */
        $pages = array_chunk($detailQuery->result(), $this->rowsPerPage);
        if (empty($pages)) {
            $pages = array(array()); // still print an empty sheet
        }

        $companyName = htmlspecialchars($header->companyname);
        $e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Purchase Order Request</title>
            <style>
                @page {
                    size: 220mm 140mm;
                    margin: 5mm 5mm 5mm 5mm;
                    font-family: Arial, sans-serif;
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.5;
                    text-align: left;
                    margin-top: 85px;   /* was 75px */
                }
                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 100px;
                }
                footer {
                    position: fixed;
                    bottom: 0px;
                    left: 0px;
                    right: 0px;
                    height: 100px;
                }
            </style>
        </head>
        <body>
            <header>
                <table style="width:100%;border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top;padding:0px;">
                            <p style="margin:0px;font-size:18px;font-weight:bold;text-transform: uppercase;">'.$companyName.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">POR No : '.$e($prefix).'/'.$e($header->porder_req_no).'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Date : '.$e($header->date).'</p>
                        </td>
                        <td style="vertical-align: top;padding:0px;text-align:right;">
                            <h3 style="margin:0px;">PURCHASE ORDER REQUEST</h3>
                            <p style="margin:4px 0 0 0;font-size:13px;font-weight:normal;">Branch : '.$e($header->branchname).'</p>
                        </td>
                    </tr>
                </table>
            </header>

            <footer>
                <table style="width:100%;">
                    <tr>
                        <td style="vertical-align: top;">
                            <table style="width:100%;font-size:12px;margin-top: 15px;">
                                <tr>
                                    <td>Prepared by</td>
                                    <td style="width: 5%;">:</td>
                                    <td>...................................</td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 15px;">Checked by</td>
                                    <td style="width: 5%;padding-top: 15px;">:</td>
                                    <td style="padding-top: 15px;">'.($header->checkedbyname ? $e($header->checkedbyname) : '...................................').'</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center;vertical-align: top;">
                            <p style="margin:0;font-size:12px;text-transform: uppercase;font-weight: bold;">'.$companyName.'</p>
                            <p style="margin:0;margin-top:25px;font-size:12px;">..........................................................</p>
                            <p style="margin:0;font-size:12px;">Authorise Officer</p>
                        </td>
                    </tr>
                </table>
            </footer>';

        $lastIndex = count($pages) - 1;

        foreach ($pages as $index => $lines) {
            $html .= '<main>
                <table style="table-layout: fixed;padding:3px;width:100%;border-collapse: collapse;font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000;" width="14%" nowrap>Code</th>
                            <th style="padding-left: 10px; border: 1px solid #000;text-align:left;" width="40%" nowrap>Product</th>
                            <th style="text-align:center; border: 1px solid #000;" width="10%" nowrap>Qty</th>
                            <th style="text-align:center; border: 1px solid #000;" width="10%" nowrap>Unit</th>
                            <th style="text-align:center; border: 1px solid #000;" nowrap>Comment</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($lines as $row) {
                $productText = $row->product_name;
                if (!empty($row->model_no)) {
                    $productText .= ' (' . $row->model_no . ')';
                }
                $unit = $row->unit_short ? $row->unit_short : $row->unit;

                $html .= '<tr>
                    <td style="border: 1px solid black; padding-left: 5px;">'.$e($row->product_code).'</td>
                    <td style="border: 1px solid black; padding-left: 10px;">'.$e($productText).'</td>
                    <td style="text-align: center; border: 1px solid black;">'.$e((float)$row->qty).'</td>
                    <td style="text-align: center; border: 1px solid black;">'.$e($unit).'</td>
                    <td style="text-align: center; border: 1px solid black;">'.$e($row->comment).'</td>
                </tr>';
            }

            $html .= '</tbody>
                </table>
            </main>';

            // page break between pages only (not after the last one)
            if ($index < $lastIndex) {
                $html .= '<div style="page-break-after: always;"></div>';
            }
        }

        $html .= '</body></html>';

        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->render();

        $filename = strtoupper($header->companyname) . ' - PURCHASE ORDER REQUEST - ' . $header->porder_req_no . '.pdf';
        $this->pdf->stream($filename, array("Attachment" => 0));
    }
}