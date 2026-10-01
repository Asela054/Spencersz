<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class PorderreqPrintinfo extends CI_Model {

     public function Printinvoice($x){
        $recordID=$x;

        $this->db->select('*');
        $this->db->from('tbl_print_porder_req');
        $this->db->join('tbl_print_porder_req_detail', 'idtbl_print_porder_req = tbl_print_porder_req_detail.tbl_print_porder_req_idtbl_print_porder_req', 'left');
        $this->db->join('tbl_material_group', 'tbl_print_porder_req.tbl_material_group_idtbl_material_group = tbl_material_group.idtbl_material_group', 'left');
        $this->db->join('tbl_measurements', 'tbl_print_porder_req_detail.tbl_measurements_idtbl_measurements = tbl_measurements.idtbl_mesurements', 'left');
        $this->db->where('tbl_print_porder_req.idtbl_print_porder_req', $recordID);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $company_id = $query->row(0)->tbl_company_idtbl_company;
        
            $prefix = 'MO';
            if ($company_id == 2) {
                $prefix = 'FT';
            } elseif ($company_id == 3) {
                $prefix = 'RM';
            }
        }

        $this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
        tbl_company.phone companyphone,tbl_company.email AS companyemail,
        tbl_company_branch.branch AS branchname');
        $this->db->from('tbl_print_porder_req');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_print_porder_req.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_print_porder_req.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_print_porder_req.idtbl_print_porder_req', $recordID);
        $companydetails = $this->db->get();

        $dataArray = [];
        $count = 0;
        $section = 1;
        foreach ($query->result() as $rowlist) {
        
            $requestname = $rowlist->requestname;
            $qty = $rowlist->qty;
            $measureType = $rowlist->measure_type;
            $comment = $rowlist->comment;
        
            if ($count % 8 == 0) {
                $dataArray[$section] = [];
            }
        
            $dataArray[$section][] = [
                'requestname' => $requestname,
                'qty' => $qty,
                'measureType' => $measureType,
                'comment' => $comment
            ];
        
            $count++;
        
            if ($count % 8 == 0) {
                $section++;
            }
        }  

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Multi Offset Printers</title>
            <style>
                @page {
                    size: 220mm 140mm;
                    margin: 5mm 5mm 5mm 5mm; /* top right bottom left */
                    font-family: Arial, sans-serif;
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.5;
                    text-align:left;
                    margin-top: 75px;
                }

                /** Define the header rules **/
                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 100px;
                }

                /** Define the footer rules **/
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
                            <p style="margin:0px;font-size:18px;font-weight:bold;text-transform: uppercase;">'.$companydetails->row()->companyname.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">POR No : ' . $prefix . '/' . $query->row(0)->porder_req_no.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Date : '.$query->row(0)->date.'</p>
                        </td>
                        <td style="vertical-align: top;padding:0px;text-align:right;">
                            <h3 style="margin:0px;">PURCHASE ORDER REQUEST</h3>
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
                                    <td style="padding-top: 15px;">...................................</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center;vertical-align: top;">
                            <p style="margin:0;font-size:12px;text-transform: uppercase;font-weight: bold;">'.$companydetails->row()->companyname.'</p>
                            <p style="margin:0;margin-top:25px;font-size:12px;">..........................................................</p>
                            <p style="margin:0;font-size:12px;">Authorise Officer</p>
                        </td>
                    </tr>
                </table>
            </footer>';

            foreach ($dataArray as $index => $section) {
                $html.='<main>
                    <table style="table-layout: fixed;padding:3px;width:100%;border-collapse: collapse;font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="padding-left: 10px; border: 1px solid #000;" width="60%" nowrap>Request Item</th>
                                <th style="text-align:center; border: 1px solid #000;" nowrap>Quantity</th>
                                <th style="text-align:center; border: 1px solid #000;" nowrap>UOM</th>
                                <th style="text-align:center; border: 1px solid #000;" nowrap>Comment</th>
                            </tr>
                        </thead>
                        <tbody>';
                            foreach ($section as $row) {
                                $html .= '<tr>
                                    <td style="border: 1px solid black; padding-left: 10px;" width="60%" nowrap>' . htmlspecialchars($row['requestname']) . '</td>
                                    <td style="text-align: center; border: 1px solid black;" nowrap>' . htmlspecialchars($row['qty']) . '</td>
                                    <td style="text-align: center; border: 1px solid black;" nowrap>' . htmlspecialchars($row['measureType']) . '</td>
                                    <td style="text-align: center; border: 1px solid black;" nowrap>' . htmlspecialchars($row['comment']) . '</td>
                                </tr>';
                            }
                        $html.='</tbody>';
                    $html.='</table>
                </main>';
                if ($index === count($dataArray) - 1) {
                    $html .= '<div style="page-break-before: always;"></div>'; 
                }
            }
        $html .= '</body>
        </html>';

        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream( "MULTI OFFSET PRINTERS-PURCHASE ORDER- ".$recordID.".pdf", array("Attachment"=>0));
    }

}