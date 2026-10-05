<?php
class PorderPrintinfo extends CI_Model{

    public function Printinvoice($x){
        $recordID = (int)$x;

        $respond = $this->db->query(
            "SELECT `tbl_porder`.*, `tbl_supplier`.`suppliername`, `tbl_supplier`.`address_line1`,
                    `tbl_supplier`.`address_line2`, `tbl_supplier`.`city`, `tbl_supplier`.`telephone_no`
             FROM `tbl_porder`
             LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier` = `tbl_porder`.`tbl_supplier_idtbl_supplier`
             WHERE `tbl_porder`.`idtbl_porder` = ? AND `tbl_porder`.`status` IN (1,2)",
            array($recordID)
        );

        if ($respond->num_rows() == 0) {
            show_404();
            return;
        }

        $company_id = $respond->row(0)->tbl_company_idtbl_company;

        $this->db->select('tbl_company.company AS companyname, tbl_company.address1 AS companyaddress, tbl_company.mobile AS companymobile,
                           tbl_company.phone AS companyphone, tbl_company.email AS companyemail,
                           tbl_company_branch.branch AS branchname,
                           preparer.name AS preparedByName,
                           approver.name AS authorizedByName');
        $this->db->from('tbl_porder');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_porder.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_porder.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS preparer', 'preparer.idtbl_user = tbl_porder.tbl_user_idtbl_user', 'left');
        $this->db->join('tbl_user AS approver', 'approver.idtbl_user = tbl_porder.approve_by', 'left');
        $this->db->where('tbl_porder.idtbl_porder', $recordID);
        $companydetails = $this->db->get();

        $dots = '...................................';
        $preparedByName   = !empty($companydetails->row()->preparedByName)   ? htmlspecialchars($companydetails->row()->preparedByName)   : $dots;
        $authorizedByName = !empty($companydetails->row()->authorizedByName) ? htmlspecialchars($companydetails->row()->authorizedByName) : $dots;
        // No contact-person table in the current DB, so there is no contact number to print
        $contactNo = $dots;

        $net = sprintf('%0.2f', $respond->row(0)->nettotal);

        $respond2 = $this->db->query(
            "SELECT `tbl_porder_detail`.`qty`, `tbl_porder_detail`.`unitprice`, `tbl_porder_detail`.`netprice`,
                    `tbl_porder_detail`.`comment`,
                    `tbl_product`.`product_code`, `tbl_product`.`product_name`,
                    COALESCE(NULLIF(`tbl_unit`.`unit_short`, ''), `tbl_unit`.`unit`) AS `measure_type`
             FROM `tbl_porder_detail`
             LEFT JOIN `tbl_product` ON `tbl_product`.`idtbl_product` = `tbl_porder_detail`.`tbl_product_idtbl_product`
             LEFT JOIN `tbl_unit` ON `tbl_unit`.`idtbl_unit` = `tbl_product`.`tbl_unit_idtbl_unit`
             WHERE `tbl_porder_detail`.`status` = 1
             AND `tbl_porder_detail`.`tbl_porder_idtbl_porder` = ?
             ORDER BY `tbl_porder_detail`.`idtbl_porder_detail` ASC",
            array($recordID)
        );

        $dataArray = [];
        $count = 0;
        $section = 1;

        $remarkFeild = trim((string)$respond->row(0)->remark);
        $supplierId  = (int)$respond->row(0)->tbl_supplier_idtbl_supplier;

        foreach ($respond2->result() as $rowlist) {
            $itemDescription = (string)$rowlist->product_name;
            if (trim((string)$rowlist->comment) !== '') {
                $itemDescription .= ' - ' . $rowlist->comment;
            }

            if ($count % 5 == 0) {
                $dataArray[$section] = [];
            }

            $dataArray[$section][] = [
                'materialInfoCode' => $rowlist->product_code,
                'itemDescription'  => $itemDescription,
                'qty'              => $rowlist->qty,
                'measureType'      => $rowlist->measure_type,
                'unitPrice'        => $rowlist->unitprice,
                'netprice'         => $rowlist->netprice
            ];

            $count++;

            if ($count % 5 == 0) {
                $section++;
            }
        }

        if (empty($dataArray)) {
            $dataArray[1] = [];
        }

        $tpnumber = '&nbsp;';
        if (strlen((string)$respond->row(0)->telephone_no) >= 9) {
            $tpnumber = htmlspecialchars($respond->row(0)->telephone_no);
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
                    margin-top: 160px;
                }

                /** Define the header rules **/
                header {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    right: 0px;
                    height: 250px;
                }

                /** Define the footer rules **/
                footer {
                    position: fixed;
                    bottom: 1rem;
                    left: 0px;
                    right: 0px;
                    height: 55px;
                    font-family: Arial, sans-serif;
                }
            </style>
        </head>
        <body>
            <header>
                <table style="width:100%;border-collapse: collapse;">
                <tr>
                    <td width="55%" style="vertical-align: top;padding:0px;">
                        <p style="margin:0px;font-size:16px;font-weight: bold;">PURCHASE ORDER</p>
                        <p style="margin:0px;font-size:13px;font-weight: bold;">To: '.htmlspecialchars((string)$respond->row(0)->suppliername).'</p>';

                        if ($supplierId == 65 && !empty($remarkFeild)) {
                            $html .= '<p style="margin:0px;font-size:13px;font-weight:bold;">'
                                . htmlspecialchars($remarkFeild)
                                . '</p>';
                        }

                        $address_line1 = trim((string)$respond->row(0)->address_line1);
                        $address_line2 = trim((string)$respond->row(0)->address_line2);
                        $city = trim((string)$respond->row(0)->city);

                        if ($address_line1 !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . htmlspecialchars($address_line1) . ',' . '</p>';
                        }
                        if ($address_line2 !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . htmlspecialchars($address_line2) . ',' . '</p>';
                        }
                        if ($city !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . htmlspecialchars($city) . '.' . '</p>';
                        }

                        $tpnumber_clean = trim(str_replace('&nbsp;', '', $tpnumber));
                        if ($tpnumber_clean !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . $tpnumber . '</p>';
                        }

                        $html .= '</td>
                        <td style="vertical-align: top;padding:0px;">
                            <p style="margin:0px;font-size:18px;font-weight:bold;text-transform: uppercase;">'.htmlspecialchars((string)$companydetails->row()->companyname).'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;text-transform: uppercase;">'.htmlspecialchars((string)$companydetails->row()->companyaddress).'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Phone : '.htmlspecialchars((string)$companydetails->row()->companymobile).'/'.htmlspecialchars((string)$companydetails->row()->companyphone).'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;"><u>E-Mail : '.htmlspecialchars((string)$companydetails->row()->companyemail).'</u></p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">PO No : ' . htmlspecialchars((string)$respond->row(0)->porder_no) . '</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Date : '.$respond->row(0)->orderdate.'</p>
                            '.($company_id == 1 ? '<p style="margin:0px;font-size:13px;font-weight:normal;">Our Vat No : &nbsp; 103305667-7000</p>' : '').'
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-top: -5px;">
                            <p style="margin:0px;font-size:13px;">Atten ....................................................</p>
                        </td>
                    </tr>
                </table>
            </header>

            <footer>';

            if ($supplierId != 65 && !empty($remarkFeild)) {
                $html .= '<p style="margin:0px 0px 3px 0px;font-size:12px;font-weight:bold;">
                            '.htmlspecialchars($remarkFeild).'
                        </p>';
            }

            $html .= '
                <table style="table-layout: fixed;padding:3px;width:100%;border-collapse: collapse;font-size:12px;">
                    <tr>
                        <td style="width:35%;">Prepared by &nbsp;: &nbsp;'.$preparedByName.'</td>
                        <td style="width:35%;">Authorized by &nbsp;: &nbsp;'.$authorizedByName.'</td>
                        <td style="width:30%;">Contact No &nbsp;: &nbsp;'.$contactNo.'</td>
                    </tr>
                </table>
                <p style="font-size:12px;text-align:center;padding:0 3px;">
                    This is a computer-generated document. No signature is required.
                </p>
            </footer>';

            // PHP 7.2/older-safe replacement for array_key_last()/array_key_first()
            $sectionKeys     = array_keys($dataArray);
            $firstSectionKey = reset($sectionKeys);
            $lastSectionKey  = end($sectionKeys);

            foreach ($dataArray as $index => $section) {

                // page break BEFORE every section except the first one
                if ($index !== $firstSectionKey) {
                    $html .= '<div style="page-break-before: always;"></div>';
                }

                $html .= '<main>
                    <table style="table-layout: fixed;padding:3px;width:100%;border-collapse: collapse;font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="width: 12%;text-align:center; border: 1px solid #000;">Code</th>
                                <th style="width: 46%;text-align:center; border: 1px solid #000;">Item Description </th>
                                <th style="width: 10%;text-align:center; border: 1px solid #000;">Qty</th>
                                <th style="width: 10%;text-align:center; border: 1px solid #000;">UOM</th>
                                <th style="width: 11%;text-align:right; border: 1px solid #000;padding-right: 10px;">Unit Price</th>
                                <th style="width: 11%;text-align:right; border: 1px solid #000;padding-right: 10px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>';
                            foreach ($section as $row) {
                                $html .= '<tr style="page-break-inside: avoid;">
                                    <td style="text-align:center; border-right: 1px solid black; border-left: 1px solid #000;">' . htmlspecialchars((string)$row['materialInfoCode']) . '</td>
                                    <td style="border-right: 1px solid black; padding-left: 10px;">' . htmlspecialchars((string)$row['itemDescription']) . '</td>
                                    <td style="text-align:center; border-right: 1px solid black;">' . htmlspecialchars((string)$row['qty']) . '</td>
                                    <td style="text-align:center; border-right: 1px solid black;">' . htmlspecialchars((string)$row['measureType']) . '</td>
                                    <td style="text-align:right; border-right: 1px solid black;padding-right: 10px;">' . htmlspecialchars(number_format($row['unitPrice'],2)) . '</td>
                                    <td style="text-align:right; border-right: 1px solid black;padding-right: 10px;">' . htmlspecialchars(number_format($row['netprice'],2)) . '</td>
                                </tr>';
                            }
                        $html.='</tbody>';

                            // only the TRUE last section shows the actual totals
                            $totalShow = ($index === $lastSectionKey) ? number_format($net,2) : '';

                            $html .= '<tfoot>
                                <tr>
                                    <td colspan="3" style="border-top: 1px solid #000;font-size:12px;"></td>
                                    <td colspan="2" style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Total (Excl)</td>
                                    <td style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label id="lbltotal">'.$totalShow.'</label></td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="font-size:11px;"></td>
                                    <td colspan="2" style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Tax</td>
                                    <td style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;"><label class="padding-right:10px;" id="lbldiscount"></label></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td colspan="2" style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left; font-weight:bold;padding-left:35px;">Total (Incl)</td>
                                    <th style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label class="font-weight-bold text-dark" id="lblbalance">'.$totalShow.'</label></th>
                                </tr>
                            </tfoot>';
                    $html.='</table>
                </main>';
            }
        $html .= '</body>
        </html>';

        $this->load->library('pdf');
        $this->pdf->loadHtml($html);
        $this->pdf->render();
        $this->pdf->stream( "MULTI OFFSET PRINTERS-PURCHASE ORDER- ".$recordID.".pdf", array("Attachment"=>0));
    }

}