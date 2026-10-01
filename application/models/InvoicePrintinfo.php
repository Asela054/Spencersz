<?php
class InvoicePrintinfo extends CI_Model{

    public function Printinvoice($x){
        $recordID=$x;
        $sql =" SELECT * FROM `tbl_print_porder` 
        LEFT JOIN `tbl_supplier` ON `tbl_supplier`.`idtbl_supplier` = `tbl_print_porder`.`tbl_supplier_idtbl_supplier` 
        WHERE `idtbl_print_porder` = '$recordID'";
        $respond=$this->db->query($sql, array(1, $recordID));

        if ($respond->num_rows() > 0) {
            $company_id = $respond->row(0)->tbl_company_idtbl_company;
        
            $prefix = 'MO';
            if ($company_id == 2) {
                $prefix = 'FT';
            } elseif ($company_id == 3) {
                $prefix = 'RM';
            }
        }

        $this->db->select('tbl_company.company AS companyname,tbl_company.address1 As companyaddress,tbl_company.mobile AS companymobile,
                                tbl_company.phone companyphone,tbl_company.email AS companyemail,
                                tbl_company_branch.branch AS branchname,
                                preparer.name AS preparedByName,
                                tbl_po_contact_person.contact_person AS authorizedByName,
                                tbl_po_contact_person.contact AS contactNo');
        $this->db->from('tbl_print_porder');
        $this->db->join('tbl_company', 'tbl_company.idtbl_company = tbl_print_porder.tbl_company_idtbl_company', 'left');
        $this->db->join('tbl_company_branch', 'tbl_company_branch.idtbl_company_branch = tbl_print_porder.tbl_company_branch_idtbl_company_branch', 'left');
        $this->db->join('tbl_user AS preparer', 'preparer.idtbl_user = tbl_print_porder.tbl_user_idtbl_user', 'left');
        $this->db->join('tbl_user AS approver', 'approver.idtbl_user = tbl_print_porder.approve_by', 'left');
        $this->db->join('tbl_po_contact_person', 'tbl_po_contact_person.idtbl_po_contact_person = tbl_print_porder.idtbl_po_contact_person', 'left');
        $this->db->where('tbl_print_porder.idtbl_print_porder', $recordID);
        $companydetails = $this->db->get();

        $preparedByName   = !empty($companydetails->row()->preparedByName)   ? htmlspecialchars($companydetails->row()->preparedByName)   : '...................................';

        // Only reveal the contact person's name/number on the printed PO when
        // cp_status was explicitly set to 1 (via the "Include Contact No" checkbox
        // ticked at approval time). Otherwise keep the placeholder dots so the
        // information stays hidden on the document.
        $cpStatus = !empty($respond->row(0)->cp_status) ? $respond->row(0)->cp_status : 0;
        $authorizedByName = !empty($companydetails->row()->authorizedByName) ? htmlspecialchars($companydetails->row()->authorizedByName) : '...................................';

        if ($cpStatus == 1) {
            $contactNo = !empty($companydetails->row()->contactNo) ? htmlspecialchars($companydetails->row()->contactNo) : '...................................';
        } else {
            $contactNo = '...................................';
        }

        $net = sprintf('%0.2f', $respond->row(0)->nettotal);
    
        $sql2="SELECT 
        `tbl_print_porder_detail`.*,
        `tbl_print_porder`.*,
        `tbl_print_material_info`.`materialinfocode`,
        `tbl_print_material_info`.`materialname`,
        `tbl_measurements`.`measure_type`,
        `tbl_material_group`.`idtbl_material_group`
        FROM `tbl_print_porder`
        LEFT JOIN `tbl_print_porder_detail` ON `tbl_print_porder`.`idtbl_print_porder` = `tbl_print_porder_detail`.`tbl_print_porder_idtbl_print_porder`
        LEFT JOIN `tbl_material_group` ON `tbl_material_group`.`idtbl_material_group` = `tbl_print_porder`.`tbl_material_group_idtbl_material_group`
        LEFT JOIN `tbl_print_material_info` ON `tbl_print_material_info`.`idtbl_print_material_info` = `tbl_print_porder_detail`.`tbl_material_id`
        LEFT JOIN `tbl_measurements` ON `tbl_measurements`.`idtbl_mesurements` = `tbl_print_porder_detail`.`tbl_measurements_idtbl_measurements`
        WHERE 
        `tbl_print_porder_detail`.`status` = '1'
        AND `tbl_print_porder`.`idtbl_print_porder` = '$recordID'";
        $respond2=$this->db->query($sql2, array(1, $recordID));

        $dataArray = [];
        $count = 0;
        $section = 1;
        $remarkFeild = '';
        $supplierId = 0;

        if ($respond->num_rows() > 0) {
            $remarkFeild = trim($respond->row(0)->remark);
            $supplierId = (int)$respond->row(0)->idtbl_supplier;
        }
        foreach ($respond2->result() as $rowlist) {
            $unitPrice = $rowlist->unitprice;
            $price     = !empty($rowlist->packetprice) ? $rowlist->packetprice : 0;
            $netprice = $rowlist->netprice;

            // Total still follows the same rule the app uses elsewhere:
            // packet price when present, otherwise plain unit price × qty
            $effectivePrice = !empty($rowlist->packetprice) ? $rowlist->packetprice : $rowlist->unitprice;
            $nettotal = $effectivePrice * $rowlist->qty;

            $materialInfoCode = $rowlist->materialinfocode;
            $itemDescription = '';

            if ($rowlist->idtbl_material_group == 4) {
                $itemDescription = $rowlist->comment;
            } else {
                $itemDescription = $rowlist->materialname;
                if (!empty($rowlist->materialinfocode)) {
                    $itemDescription .= ' / ' . $rowlist->materialinfocode;
                }
            }
            $qty = $rowlist->qty;
            $measureType = $rowlist->measure_type;

            if ($count % 5 == 0) {
                $dataArray[$section] = [];
            }

            $dataArray[$section][] = [
                'materialInfoCode' => $materialInfoCode,
                'itemDescription' => $itemDescription,
                'qty' => $qty,
                'measureType' => $measureType,
                'unitPrice' => $unitPrice,
                'netprice' => $netprice,
                'price' => $price,          // NEW
                'nettotal' => $nettotal
            ];

            $count++;

            if ($count % 5 == 0) {
                $section++;
            }
        }        



        $htmlcusdetail='';
        $travelinfotbl='';
        $additional ='';
        $chequeinfotbl ='';
        $cashinfotbl ='';

        $tpnumber='&nbsp;';
        if(strlen($respond->row(0)->telephone_no)>=9){$tpnumber=$respond->row(0)->telephone_no;}
        

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
                        <p style="margin:0px;font-size:13px;font-weight: bold;">To: '.$respond->row(0)->suppliername.'</p>';
                        
                        if ($supplierId == 65 && !empty($remarkFeild)) {
                            $html .= '<p style="margin:0px;font-size:13px;font-weight:bold;">'
                                . htmlspecialchars($remarkFeild)
                                . '</p>';
                        }

                        $address_line1 = trim($respond->row(0)->address_line1);
                        $address_line2 = trim($respond->row(0)->address_line2);
                        $city = trim($respond->row(0)->city);

                        if (!empty($address_line1) && $address_line1 !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . $address_line1 . ',' . '</p>';
                        }
                        if (!empty($address_line2) && $address_line2 !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . $address_line2 . ',' . '</p>';
                        }
                        if (!empty($city) && $city !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . $city . '.' . '</p>';
                        }

                        $tpnumber_clean = trim(str_replace('&nbsp;', '', $tpnumber));
                        if (!empty($tpnumber_clean) && $tpnumber_clean !== '') {
                            $html .= '<p style="margin:0px;font-size:13px;padding-left: 24px;">' . $tpnumber . '</p>';
                        }

                        $html .= '</td>
                        <td style="vertical-align: top;padding:0px;">
                            <p style="margin:0px;font-size:18px;font-weight:bold;text-transform: uppercase;">'.$companydetails->row()->companyname.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;text-transform: uppercase;">'.$companydetails->row()->companyaddress.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Phone : '.$companydetails->row()->companymobile.'/'.$companydetails->row()->companyphone.'</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;"><u>E-Mail : '.$companydetails->row()->companyemail.'</u></p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">PO No : ' . $respond->row(0)->porder_no . '</p>
                            <p style="margin:0px;font-size:13px;font-weight:normal;">Date : '.$respond->row(0)->orderdate.'</p>
                            '.($respond->num_rows() > 0 && $company_id == 1 ? '<p style="margin:0px;font-size:13px;font-weight:normal;">Our Vat No : &nbsp; 103305667-7000</p>' : '').'
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
                                <th style="width: 10%;text-align:center; border: 1px solid #000;">Code</th>
                                <th style="width: 50%;text-align:center; border: 1px solid #000;">Item Description </th>
                                <th style="width: 8%;text-align:center; border: 1px solid #000;">Qty</th>
                                <th style="width: 8%;text-align:center; border: 1px solid #000;">UOM</th>
                                <th style="width: 8%;text-align:right; border: 1px solid #000;padding-right: 10px;">Unit Price</th>
                                <th style="width: 8%;text-align:right; border: 1px solid #000;padding-right: 10px;">Price</th>
                                <th style="width: 9%;text-align:right; border: 1px solid #000;padding-right: 10px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>';
                            foreach ($section as $row) {
                                $html .= '<tr style="page-break-inside: avoid;">
                                    <td style="width: 9%; text-align:center; border-right: 1px solid black; border-left: 1px solid #000;">' . htmlspecialchars($row['materialInfoCode']) . '</td>
                                    <td style="width: 30%; border-right: 1px solid black; padding-left: 10px;">' . htmlspecialchars($row['itemDescription']) . '</td>
                                    <td style="width: 8%; text-align:center; border-right: 1px solid black;">' . htmlspecialchars($row['qty']) . '</td>
                                    <td style="width: 8%; text-align:center; border-right: 1px solid black;">' . htmlspecialchars($row['measureType']) . '</td>
                                    <td style="width: 14%; text-align:right; border-right: 1px solid black;padding-right: 10px;">' . htmlspecialchars(number_format($row['unitPrice'],2)) . '</td>
                                    <td style="width: 14%; text-align:right; border-right: 1px solid black;padding-right: 10px;">' . htmlspecialchars(number_format($row['price'],2)) . '</td>
                                    <td style="width: 17%; text-align:right; border-right: 1px solid black;padding-right: 10px;">' . htmlspecialchars(number_format($row['netprice'],2)) . '</td>
                                </tr>';
                            }
                        $html.='</tbody>';

                            // only the TRUE last section shows the actual totals
                            if ($index === $lastSectionKey) {
                                $html .= '<tfoot>
                                    <tr>
                                        <td colspan="4" style="border-top: 1px solid #000;font-size:12px;"></td>
                                        <td colspan="2" style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Total (Excl)</td>
                                        <td style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label id="lbltotal">'.number_format($net,2).'</label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="font-size:11px;"></td>
                                        <td colspan="2" style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Tax</td>
                                        <td style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;"><label class="padding-right:10px;" id="lbldiscount"></label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2" style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left; font-weight:bold;padding-left:35px;">Total (Incl)</td>
                                        <th style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label class="font-weight-bold text-dark" id="lblbalance">'.number_format($net,2).'</label></th>
                                    </tr>
                                </tfoot>';
                            } else {
                                // not the last section -> leave totals blank, continues on next page
                                $html .= '<tfoot>
                                    <tr>
                                        <td colspan="4" style="border-top: 1px solid #000;font-size:12px;"></td>
                                        <td colspan="2" style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Total (Excl)</td>
                                        <td style="border-top: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label id="lbltotal"></label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="font-size:11px;"></td>
                                        <td colspan="2" style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:left;padding-left:35px;">Tax</td>
                                        <td style="border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;"><label class="padding-right:10px;" id="lbldiscount"></label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2" style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:left; font-weight:bold;padding-left:35px;">Total (Incl)</td>
                                        <th style="border-bottom: 1px solid #000;border-left: 1px solid #000;border-right: 1px solid #000;text-align:right;padding-right:10px;"><label class="font-weight-bold text-dark" id="lblbalance"></label></th>
                                    </tr>
                                </tfoot>';
                            }
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