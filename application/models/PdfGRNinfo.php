<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGRNinfo extends CI_Model {
    public function pdfgrnget($x) {
        $recordID = $x;
        $insertdatetime = date('Y-m-d H:i:s');

        $this->db->select("
            *,
            COALESCE(tbl_print_grn.idtbl_print_grn, 0) AS idtbl_print_grn,
            COALESCE(tbl_print_grn.subtotalcost, 0) AS grn_subtotal,
            COALESCE(tbl_print_grn.totalcost, 0) AS grn_total,
            COALESCE(tbl_print_grn.vatamountcost, 0) AS vatamount,
            COALESCE(tbl_print_grn.discount, 0) AS discount,
            COALESCE(tbl_print_grn.remark, '') AS remark,
            COALESCE(tbl_print_grndetail.qty, 0) AS qty,
            COALESCE(tbl_print_grndetail.costunitprice, 0) AS costunitprice,
            COALESCE(tbl_material_group.idtbl_material_group, 0) AS idtbl_material_group,
            COALESCE(tbl_print_grndetail.comment, '') AS comment,
            tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info AS grn_material_id,

            (
                SELECT COALESCE(pd.qty, 0)
                FROM tbl_print_porder_detail pd
                WHERE pd.status = 1
                AND pd.tbl_print_porder_idtbl_print_porder = tbl_print_grn.tbl_print_porder_idtbl_print_porder
                AND pd.tbl_material_id = tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info
                ORDER BY
                    ABS(pd.qty - tbl_print_grndetail.qty) ASC,
                    pd.idtbl_print_porder_detail ASC
                LIMIT 1
            ) AS ordered_qty,

            (
                SELECT COALESCE(SUM(gd.qty), 0)
                FROM tbl_print_grn g
                INNER JOIN tbl_print_grndetail gd
                    ON g.idtbl_print_grn = gd.tbl_print_grn_idtbl_print_grn
                WHERE g.tbl_print_porder_idtbl_print_porder =
                    tbl_print_grn.tbl_print_porder_idtbl_print_porder
                AND gd.tbl_print_material_info_idtbl_print_material_info =
                    tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info
                AND g.status = 1
                AND gd.status = 1
                AND g.idtbl_print_grn < tbl_print_grn.idtbl_print_grn
            ) AS prev_qty

        ", false);
        $this->db->from('tbl_print_grn');

        $this->db->join(
            'tbl_print_grndetail',
            'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail.tbl_print_grn_idtbl_print_grn',
            'left'
        );

        $this->db->join(
            'tbl_print_material_info',
            'tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info = tbl_print_material_info.idtbl_print_material_info',
            'left'
        );

        $this->db->join(
            'tbl_supplier',
            'tbl_print_grn.tbl_supplier_idtbl_supplier = tbl_supplier.idtbl_supplier',
            'left'
        );

        $this->db->join(
            'tbl_location',
            'tbl_print_grn.tbl_location_idtbl_location = tbl_location.idtbl_location',
            'left'
        );

        $this->db->join(
            'tbl_material_group',
            'tbl_print_grn.tbl_material_group_idtbl_material_group = tbl_material_group.idtbl_material_group',
            'left'
        );

        $this->db->join(
            'tbl_measurements',
            'tbl_print_grndetail.tbl_measurements_idtbl_mesurements = tbl_measurements.idtbl_mesurements',
            'left'
        );

        $this->db->join(
            'tbl_print_porder',
            'tbl_print_grn.tbl_print_porder_idtbl_print_porder = tbl_print_porder.idtbl_print_porder',
            'left'
        );

        $this->db->where(
            'tbl_print_grn.idtbl_print_grn',
            $recordID
        );
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

        $remarkFeild = '';
        if ($query->num_rows() > 0) {
            $remarkFeild = $query->row(0)->remark;
        }

        $totalSum   = $query->row()->grn_subtotal;
        $grn_total  = $query->row()->grn_total;
        $grn_vat    = $query->row()->vatamount;

        $dataArray = [];
        $count = 0;
        $section = 1;

        foreach ($query->result() as $rowlist) {
            if ($count % 6 == 0) {
                $dataArray[$section] = [];
            }

            $itemDescription = '';
            if ($rowlist->idtbl_material_group == 4) {
                $itemDescription = $rowlist->comment;
            } else {
                $itemDescription = $rowlist->materialname;
                if (!empty($rowlist->materialinfocode)) {
                    $itemDescription .= ' / ' . $rowlist->materialinfocode;
                }
            }

            $dataArray[$section][] = [
                'itemcode' => $rowlist->materialinfocode,
                'itemDescription' => $itemDescription,
                'ordered' => (float) $rowlist->ordered_qty,
                'prev' => (float) $rowlist->prev_qty,
                'received' => (float) $rowlist->qty,
                'unit' => $rowlist->measure_type,
                'price' => !empty($rowlist->packetprice) ? $rowlist->packetprice : $rowlist->costunitprice,
                'total' => $rowlist->total,
            ];

            $count++;
            if ($count % 6 == 0) {
                $section++;
            }
        }

        $this->load->library('pdf');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile, tbl_company.phone companyphone,tbl_company.email AS companyemail, tbl_company_branch.branch AS branchname');
        $this->db->from('tbl_print_grn');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_print_grn.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_print_grn.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->where('tbl_print_grn.idtbl_print_grn', $recordID);
        $companydetails = $this->db->get();

        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Goods Received Note</title>
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
                    margin-top: 190px;   /* was 165px */
                    /* margin-bottom: 140px; */
                }

                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 280px;   /* was 255px */
                }

                /** Define the footer rules **/
                footer {
                    position: fixed;
                    bottom: 0px;
                    left: 0px;
                    right: 0px;
                    height: 25px;
                }
            </style>
        </head>
        <body>
        <header>
            <table style="width:100%;border-collapse: collapse;">
                <tr>
                    <td style="text-align: center;vertical-align: top;padding: 0px;">
                        <p style="font-size: 15px;font-weight: bold; margin-top: 0px; margin-bottom: 0px;text-transform: uppercase;">'.$companydetails->row()->companyname.'</p>
                        <p style="margin:0px;font-size:13px;text-transform: uppercase;">' . $companydetails->row()->companyaddress . '</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table style="width:100%;border-collapse: collapse;">
                            <td width="40%" style="vertical-align: top;">
                                <p style="margin:0px;font-size: 13px;font-weight: bold;">'. $query->row()->suppliername .'</p>';
                                $html .= '
                                <p style="margin:0px;font-size: 13px;">'. $query->row()->delivery_address_line1 .', '. $query->row()->delivery_address_line2 .',</p>
                                <p style="margin:0px;font-size: 13px;">'. $query->row()->delivery_city .',</p>
                                <p style="margin:0px;font-size: 13px;">'. $query->row()->delivery_state .'</p>
                            </td>
                            <td width="25%" style="vertical-align: top;text-align: left;font-size: 18px;font-weight: bold;"><u>Good Receive Note</u></td>
                            <td width="35%" style="vertical-align: top;">
                                <table style="width:100%;border-collapse: collapse;">
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">GRN No</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">' . $prefix . '/'. $query->row()->grn_no .'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">Date</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">'. $query->row()->grndate .'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">Invoice Number</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">'. $query->row()->invoicenum .'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">PO Number</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">' . $query->row()->porder_no . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">Batch Number</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">'. $query->row()->batchno .'</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size: 13px;font-weight: bold;" width="40%">Location</td>
                                        <td style="font-size: 13px;font-weight: bold;" width="5%">:</td>
                                        <td style="font-size: 13px;">'. $query->row()->location .'</td>
                                    </tr>
                                </table>
                            </td>
                        </table>
                    </td>
                </tr>
            </table>
        </header>
        <footer>
            <table width="100%" style="border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="
                        width: 33.33%;
                        text-align: center;
                        vertical-align: bottom;
                        padding: 0 20px;
                    ">
                        <div style="
                            border-top: 1px dotted #000;
                            width: 75%;
                            margin: 0 auto 8px auto;
                            height: 1px;
                        "></div>

                        <div style="
                            font-size: 12px;
                            text-align: center;
                        ">
                            Received by
                        </div>
                    </td>

                    <td style="
                        width: 33.33%;
                        text-align: center;
                        vertical-align: bottom;
                        padding: 0 20px;
                    ">
                        <div style="
                            border-top: 1px dotted #000;
                            width: 75%;
                            margin: 0 auto 8px auto;
                            height: 1px;
                        "></div>

                        <div style="
                            font-size: 12px;
                            text-align: center;
                        ">
                            Approved by
                        </div>
                    </td>

                    <td style="
                        width: 33.33%;
                        text-align: center;
                        vertical-align: bottom;
                        padding: 0 20px;
                    ">
                        <div style="
                            border-top: 1px dotted #000;
                            width: 75%;
                            margin: 0 auto 8px auto;
                            height: 1px;
                        "></div>

                        <div style="
                            font-size: 12px;
                            text-align: center;
                        ">
                            Accountant
                        </div>
                    </td>
                </tr>
            </table>
        </footer>
        ';

       // PHP 7.2/older-safe replacement for array_key_last()/array_key_first()
        $sectionKeys     = array_keys($dataArray);
        $firstSectionKey = reset($sectionKeys);
        $lastSectionKey  = end($sectionKeys);

        $totalsRowsHtml = '
            <tr>
                <td style="border: 1px solid #000;font-size:12px;" colspan="5" rowspan="3">'.$remarkFeild.'</td>
                <th style="border: 1px solid #000;font-size:12px;padding-left: 10px;" colspan="2">Total (Ex)</th>
                <th style="border: 1px solid #000;font-size:12px;text-align: right;padding-right: 5px;">'.number_format($totalSum, 2).'</th>
            </tr>
            <tr>
                <th style="border: 1px solid #000;font-size:12px;padding-left: 10px;" colspan="2">Vat</th>
                <th style="border: 1px solid #000;font-size:12px;text-align: right;padding-right: 5px;">'.number_format($grn_vat, 2).'</th>
            </tr>
            <tr>
                <th style="border: 1px solid #000;font-size:12px;padding-left: 10px;" colspan="2">Total (Icl)</th>
                <th style="border: 1px solid #000;font-size:12px;text-align: right;padding-right: 5px;">'.number_format($grn_total, 2).'</th>
            </tr>';

        foreach ($dataArray as $index => $section) {

            // page break BEFORE every section except the first one — same pattern as VoucherPdf
            if ($index !== $firstSectionKey) {
                $html .= '<div style="page-break-before: always;"></div>';
            }

            $html.='
            <main>
                <table style="width:100%;border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="text-align:left;font-size: 12px;border: 1px thin solid;padding-left: 10px;">Item Code</th>
                            <th rowspan="2" style="text-align:center;font-size: 12px;border: 1px thin solid;">Item Description</th>
                            <th colspan="3" style="text-align:center;font-size: 12px;border: 1px thin solid;">Quanttity</th>
                            <th rowspan="2" style="text-align:center;font-size: 12px;border: 1px thin solid;">Unit</th>
                            <th rowspan="2" style="text-align:center;font-size: 12px;border: 1px thin solid;">Price</th>
                            <th rowspan="2" style="text-align:center;font-size: 12px;border: 1px thin solid;">Total</th>
                        </tr>
                        <tr>
                            <th style="text-align:center;font-size: 12px;border: 1px thin solid;">Ordered</th>
                            <th style="text-align:center;font-size: 12px;border: 1px thin solid;">Prev</th>
                            <th style="text-align:center;font-size: 12px;border: 1px thin solid;">Received</th>
                        </tr>
                    </thead>
                    <tbody>';
                        foreach ($section as $row) {
                            $html .= '<tr style="page-break-inside: avoid;">
                                <td style="font-size: 12px;border: 1px thin solid;padding-left: 10px;">' . htmlspecialchars($row['itemcode']) . '</td>
                                <td style="width: 35%;text-align:left;font-size: 12px;border: 1px thin solid;padding-left: 10px;">' . htmlspecialchars($row['itemDescription']) . '</td>
                                <td style="text-align:center;font-size: 12px;border: 1px thin solid;">' . htmlspecialchars($row['ordered']) . '</td>
                                <td style="text-align:center;font-size: 12px;border: 1px thin solid;">' . htmlspecialchars($row['prev']) . '</td>
                                <td style="text-align:center;font-size: 12px;border: 1px thin solid;">' . htmlspecialchars($row['received']) . '</td>
                                <td style="text-align:center;font-size: 12px;border: 1px thin solid;">' . htmlspecialchars($row['unit']) . '</td>
                                <td style="text-align:right;font-size: 12px;border: 1px thin solid;padding-right: 5px;">' . htmlspecialchars($row['price']) . '</td>
                                <td style="text-align:right;font-size: 12px;border: 1px thin solid;padding-right: 5px;">' . number_format($row['total'], 2) . '</td>
                            </tr>';
                        }
                    $html.='</tbody>';

                    // only the true last section carries the totals footer
                    if ($index === $lastSectionKey) {
                        $html .= '<tfoot>'.$totalsRowsHtml.'</tfoot>';
                    }

                    $html .= '</table>
                    </main>';
        }

        $html.='</body>
        </html>
        ';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Goods Received Note - ". $recordID .".pdf", ["Attachment"=>0]);
    }
    public function VoucherPdf($x){
        $recordID=$x;
        $sql ="SELECT *, `tbl_print_grn`.`subtotal` AS `grnsubtotal` FROM `tbl_grn_vouchar_import_cost` 
        LEFT JOIN `tbl_print_grn` ON `tbl_print_grn`.`idtbl_print_grn` = `tbl_grn_vouchar_import_cost`.`tbl_print_grn_idtbl_print_grn` 
        LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier` = `tbl_print_grn`.`tbl_supplier_idtbl_supplier` 
        LEFT JOIN `tbl_print_porder` ON `tbl_print_porder`.`idtbl_print_porder` = `tbl_print_grn`.`tbl_print_porder_idtbl_print_porder` 
        WHERE `idtbl_grn_vouchar_import_cost` = ?";
        $respond=$this->db->query($sql, array($recordID));

        // print_r($this->db->last_query());

        $grnID=$respond->row(0)->idtbl_print_grn;

        $this->db->select('tbl_grn_vouchar_import_cost.*, tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile, tbl_company.phone companyphone,tbl_company.email AS companyemail, tbl_company_branch.branch AS branchname');
		$this->db->from('tbl_grn_vouchar_import_cost');
		$this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_grn_vouchar_import_cost.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_grn_vouchar_import_cost.tbl_company_branch_idtbl_company_branch', 'left');
		$this->db->where('tbl_grn_vouchar_import_cost.idtbl_grn_vouchar_import_cost', $recordID);
		$companydetails = $this->db->get();

        $this->db->select('*, COALESCE(tbl_print_grn.idtbl_print_grn, 0) AS idtbl_print_grn, COALESCE(tbl_print_grn.total, 0) AS grn_total, COALESCE(tbl_print_grn.discount, 0) AS discount, COALESCE(tbl_print_grndetail.qty, 0) AS qty, COALESCE(tbl_print_grndetail.costunitprice, 0) AS costunitprice');
        $this->db->from('tbl_print_grn');
        $this->db->join('tbl_print_grndetail', 'tbl_print_grn.idtbl_print_grn = tbl_print_grndetail.tbl_print_grn_idtbl_print_grn', 'left');
        $this->db->join('tbl_print_material_info', 'tbl_print_grndetail.tbl_print_material_info_idtbl_print_material_info = tbl_print_material_info.idtbl_print_material_info', 'left');
        $this->db->join('tbl_supplier', 'tbl_print_grn.tbl_supplier_idtbl_supplier = tbl_supplier.idtbl_supplier', 'left');
        $this->db->join('tbl_location', 'tbl_print_grn.tbl_location_idtbl_location = tbl_location.idtbl_location', 'left');
        $this->db->join('tbl_material_group', 'tbl_print_grn.tbl_material_group_idtbl_material_group = tbl_material_group.idtbl_material_group', 'left');
        $this->db->join('tbl_measurements', 'tbl_print_grndetail.tbl_measurements_idtbl_mesurements = tbl_measurements.idtbl_mesurements', 'left');
        $this->db->where('tbl_print_grn.idtbl_print_grn' ,$grnID);
        $respondgrn = $this->db->get();
    
        $sql2="SELECT 
        `tbl_grn_vouchar_import_cost_detail`.*,
        `tbl_import_cost_types`.`cost_type`
        FROM `tbl_grn_vouchar_import_cost_detail`
        LEFT JOIN `tbl_import_cost_types` ON `tbl_import_cost_types`.`idtbl_import_cost_types` = `tbl_grn_vouchar_import_cost_detail`.`tbl_import_cost_types_idtbl_import_cost_types`
        WHERE 
        `tbl_grn_vouchar_import_cost_detail`.`status` = ?
        AND `tbl_grn_vouchar_import_cost_detail`.`tbl_grn_vouchar_import_cost_idtbl_grn_vouchar_import_cost` = ?";
        $respond2=$this->db->query($sql2, array(1, $recordID));

        $dataArray = [];
        $count = 0;
        $section = 1;
        foreach ($respond2->result() as $rowlist) {        
            if ($count % 15 == 0) {
                $dataArray[$section] = [];
            }
        
            $dataArray[$section][] = [
                'cost_type' => $rowlist->cost_type,
                'cost_amount' => $rowlist->cost_amount,
                'comment' => $rowlist->comment,
            ];
        
            $count++;
        
            if ($count % 15 == 0) {
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
                    /* size: 220mm 140mm; */
                    margin: 5mm 5mm 5mm 5mm; /* top right bottom left */
                    font-family: Arial, sans-serif;
                }
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.5;
                    text-align:left;
                    margin-top: 260px;
                }

                /** Define the header rules **/
                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 350px;
                }

                /** Define the footer rules **/
                footer {
                    position: fixed; 
                    bottom: 0px; 
                    left: 0px; 
                    right: 0px;
                    height: 120px;
                }
            </style>
        </head>
        <body>
            <header>
                <table style="width:100%;border-collapse: collapse;">
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <p style="margin:0px;font-size:18px;font-weight: bold;padding-bottom: 15px;color:#0000FF">GOOD RECEIVE VOUCHER</p>
                        </td>
                    </tr>
                    <tr>
                        <td width="55%" style="vertical-align: top;padding:0px;">
                            <p style="margin:0px;font-size:16px;font-weight:bold;text-transform: uppercase;">'.$companydetails->row()->companyname.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;text-transform: uppercase;">'.$companydetails->row()->companyaddress.'</p>
                        </td>
                        <td style="vertical-align: top;padding:0px;">
                            <p style="margin:0px;font-size:13px;">Tax Registration</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Telephone : '.$companydetails->row()->companymobile.'/'.$companydetails->row()->companyphone.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;"><u>E-Mail : '.$companydetails->row()->companyemail.'</u></p>
                            <!--<p style="margin:0px;font-size:13px;font-weight:normal;">GRN No: MO/GRN-0000'. $respond->row()->idtbl_print_grn .'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Date : '.$respond->row(0)->grndate.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Our Vat No : &nbsp; 103305667-7000</p>-->
                        </td>
                    </tr>
                    <tr>
                        <td width="55%" style="vertical-align: top;padding:0px;padding-top: 15px;padding-bottom: 15px;">
                            <p style="margin:0px;font-size:13px;font-weight: bold;">To: '.$respond->row(0)->suppliername.'</p>
                            <p style="margin:0px;font-size:13px;padding-left: 24px;"> '.$respond->row(0)->address_line1.',</p>
                            <p style="margin:0px;font-size:13px;padding-left: 24px;"> '.$respond->row(0)->address_line2.',</p>
                            <p style="margin:0px;font-size:13px;padding-left: 24px;"> '.$respond->row(0)->city.'.</p>
                        </td>
                        <td style="vertical-align: top;padding:0px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table style="width:100%;">
                                <tr>
                                    <th style="width: 20%;text-align: center;font-size: 13px;">Account</th>
                                    <th style="width: 20%;text-align: center;font-size: 13px;">Date</th>
                                    <th style="width: 20%;text-align: center;font-size: 13px;">Order No</th>
                                    <th style="width: 20%;text-align: center;font-size: 13px;">Supplier Invoice</th>
                                    <th style="width: 20%;text-align: center;font-size: 13px;">Our Reference</th>
                                </tr>
                                <tr>
                                    <td style="width: 20%;text-align: center;font-size: 13px;border: 1px thin solid;border-radius: 4px;">&nbsp;</td>
                                    <td style="width: 20%;text-align: center;font-size: 13px;border: 1px thin solid;border-radius: 4px;">'.$respond->row(0)->grndate.'</td>
                                    <td style="width: 20%;text-align: center;font-size: 13px;border: 1px thin solid;border-radius: 4px;">'.$respond->row(0)->porder_no.'</td>
                                    <td style="width: 20%;text-align: center;font-size: 13px;border: 1px thin solid;border-radius: 4px;">'.$respond->row(0)->invoicenum.'</td>
                                    <td style="width: 20%;text-align: center;font-size: 13px;border: 1px thin solid;border-radius: 4px;">'.$respond->row(0)->invoiceno.'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </header>

            <footer>
                <table style="width:100%;">
                    <tr>
                        <td style="width: 50%;vertical-align: top;">
                            <table style="width:100%;">
                                <tr>
                                    <td style="font-size:13px;" width="25%">Received by</td>
                                    <td style="font-size:13px;">.................................................................</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;" width="25%">Date</td>
                                    <td style="font-size:13px;">.................................................................</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;" width="25%">Signed</td>
                                    <td style="font-size:13px;">.................................................................</td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align: top;">
                            <table style="width:100%;border-collapse: collapse;">
                                <tr>
                                    <td style="font-size:13px;">Total (Excl)</td>
                                    <td style="text-align: right;font-size:13px;">'.number_format($respond->row(0)->grnsubtotal, 2).'</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;">Tax</td>
                                    <td style="text-align: right;font-size:13px;">'.number_format($respond->row(0)->vatamount, 2).'</td>
                                </tr>
                                <tr>
                                    <th style="font-size:13px;">Total (Incl)</th>
                                    <th style="text-align: right;font-size:13px;">'.number_format($respond->row(0)->total, 2).'</th>
                                </tr>
                                <tr>
                                    <td style="font-size:13px;">Discount</td>
                                    <td style="text-align: right;font-size:13px;">'.number_format($respond->row(0)->discount, 2).'</td>
                                </tr>
                                <tr>
                                    <td colspan="2"><div style="border-top: 1px thin solid;width: 100%;"></div></td>
                                </tr>
                                <tr>
                                    <th style="font-size: 16px;">Total (Incl)</th>
                                    <th style="text-align: right; font-size: 16px;">'.number_format($respond->row(0)->total, 2).'</th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </footer>';

            // ---- Item list now renders ONCE, before the cost-split pages ----
            // (previously this whole block was inside the per-section loop below,
            //  so the full GRN item list was reprinted on every Importation Split page)
            $html.='
            <main>
                <table style="width:100%;border-collapse: collapse;">
                    <tr>
                        <td colspan="2" style="padding-top: 25px;">
                            <table style="width:100%;border-collapse: collapse;">
                                <tr>
                                    <th style="text-align: left;font-size: 12px;" nowrap><u>Item Code</u></th>
                                    <th style="text-align: left;font-size: 12px;" nowrap><u>Item Description</u></th>
                                    <th style="text-align: center;font-size: 12px;"><u>Ordered</u></th>
                                    <th style="text-align: center;font-size: 12px;"><u>Prev</u></th>
                                    <th style="text-align: center;font-size: 12px;"><u>Quantity</u></th>
                                    <th style="text-align: center;font-size: 12px;"><u>Unit</u></th>
                                    <th style="text-align: right;font-size: 12px;" nowrap><u>Price (Ex</u></th>
                                    <th style="text-align: left;font-size: 12px;" nowrap><u>Disc %</u></th>
                                    <th style="text-align: right;font-size: 12px;" nowrap><u>Tax</u></th>
                                    <th style="text-align: right;font-size: 12px;" nowrap><u>Total (Inc)</u></th>
                                </tr>';
                                foreach ($respondgrn->result() as $rowgrninfo) {                               
                                        $itemcode=$rowgrninfo->materialinfocode;
                                        $itemdesc=$rowgrninfo->materialname;
                        
                                    $html.='
                                    <tr style="page-break-inside: avoid;">
                                        <td style="text-align: left;font-size: 12px;">'.$itemcode.'</td>
                                        <td style="text-align: left;font-size: 12px;" nowrap>'.$itemdesc.'</td>
                                        <td style="text-align: center;font-size: 12px;">'.$rowgrninfo->qty.'</td>
                                        <td style="text-align: center;font-size: 12px;">0.00</td>
                                        <td style="text-align: center;font-size: 12px;">'.$rowgrninfo->qty.'</td>
                                        <td style="text-align: center;font-size: 12px;">'.$rowgrninfo->measure_type.'</td>
                                        <td style="text-align: right;font-size: 12px;" nowrap>'.$rowgrninfo->costunitprice.'</td>
                                        <td style="text-align: left;font-size: 12px;" nowrap>'.$rowgrninfo->unit_discount.'</td>
                                        <td style="text-align: right;font-size: 12px;" nowrap>'.number_format($rowgrninfo->costunitprice, 2).'</td>
                                        <td style="text-align: right;font-size: 12px;" nowrap>'.number_format($rowgrninfo->total, 2).'</td>
                                    </tr>
                                    ';
                                }
                            $html.='</table>
                            <div style="border-top: 1px thin solid;width: 100%;margin-top: 10px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2" style="padding-top: 15px;font-size: 13px;">
                            Importation Split - Additional Cost Allocation
                        </th>
                    </tr>
                </table>
            </main>
            ';

            // PHP 7.2/older-safe replacement for array_key_last()/array_key_first()
            $sectionKeys     = array_keys($dataArray);
            $firstSectionKey = reset($sectionKeys);
            $lastSectionKey  = end($sectionKeys);

            foreach ($dataArray as $index => $section) {

                // page break BEFORE every split-cost section except the first one
                if ($index !== $firstSectionKey) {
                    $html .= '<div style="page-break-before: always;"></div>';
                }

                $html.='
                <main>
                    <table style="width:100%;border-collapse: collapse;">
                        <tr>
                            <td colspan="2" style="padding-top: 15px;">
                                <table style="width:100%;border-collapse: collapse;">
                                    <tr>
                                        <th style="font-size: 13px;text-align: left;"><u>Supplier Code</u></th>
                                        <th style="font-size: 13px;text-align: left;"><u>Supplier Name</u></th>
                                        <th style="font-size: 13px;text-align: left;"><u>Description</u></th>
                                        <th style="font-size: 13px;text-align: right;"><u>Amount (excl)</u></th>
                                        <th style="font-size: 13px;text-align: right;"><u>Tax Amount</u></th>
                                        <th style="font-size: 13px;text-align: right;"><u>Total</u></th>
                                    </tr>';
                                    $totalcostamount=0;
                                    $totalcostvatamount=0;
                                    foreach ($section as $row) {
                                        $totalcostamount+=htmlspecialchars($row['cost_amount']);
                                        $html.='<tr style="page-break-inside: avoid;">
                                            <td style="font-size: 12px;text-align: left;">&nbsp;</td>
                                            <td style="font-size: 12px;text-align: left;">'.htmlspecialchars($row['cost_type']).'</td>
                                            <td style="font-size: 12px;text-align: left;">'.htmlspecialchars($row['cost_type']).'</td>
                                            <td style="font-size: 12px;text-align: right;">'.number_format(htmlspecialchars($row['cost_amount']), 2).'</td>
                                            <td style="font-size: 12px;text-align: right;"></td>
                                            <td style="font-size: 12px;text-align: right;">'.number_format(htmlspecialchars($row['cost_amount']), 2).'</td>
                                        </tr>';
                                    }

                                    // only the TRUE last section shows the split totals
                                    if ($index === $lastSectionKey) {
                                        $html .= '<tfoot>
                                            <tr>
                                                <th colspan="3" style="font-size:13px;">Importation Split Totals</th>
                                                <th style="font-size:13px;text-align:right;">'.number_format($totalcostamount,2).'</th>
                                                <th style="font-size:13px;text-align:right;">'.number_format($totalcostvatamount,2).'</th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </tfoot>';
                                    } else {
                                        $html .= '<tfoot>
                                            <tr>
                                                <td colspan="6" style="font-size:12px;">&nbsp;</td>
                                            </tr>
                                        </tfoot>';
                                    }
                                $html.='</table>
                                <div style="border-top: 1px thin solid;width: 100%;margin-top: 10px;"></div>
                            </td>
                        </tr>
                        
                    </table>
                </main>
                ';
            }
        $html .= '</body>
        </html>';
        
        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'potrait');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream( "MULTI OFFSET PRINTERS-PURCHASE ORDER- ".$recordID.".pdf", array("Attachment"=>0));
    }
}