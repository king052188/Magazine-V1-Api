<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;


class insertionDigitalClass extends Controller
{
    //

    public $InsertionClass;

    public function Init($trans_num, $download = null) {

        $InsertionClass = new InsertionClass();

        $booking_sales = KPAHelper::get_booking_digital_report($trans_num);
        if($booking_sales["Status"] == 404) {
            return $booking_sales;
        }

        $issue_trans = KPAHelper::get_digital_transactions($booking_sales["Data"][0]->Id);
        if(count($issue_trans["Data"]) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Transaction History."
            );
        }

        $issue_discounts = COUNT($issue_trans["Issue_Discounts"]) > 0 ? $issue_trans["Issue_Discounts"][0] : null;
        $discretionary_discount = KPAHelper::get_discount($trans_num, 1);

        $company = KPAHelper::get_company_information($booking_sales["Data"][0]->client_id, $booking_sales["Data"][0]->agency_id);
        if(count($company) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Company Information."
            );
        }

        if(count($company["Company_Info"]) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Contact Information."
            );
        }

        if(count($company["Contact_Info"]) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Contact Information."
            );
        }

        $Company_Info = $company["Company_Info"];
        $Contact_Info = $company["Contact_Info"];

        $sales_person = KPAHelper::get_sales_person($booking_sales["Data"][0]->sales_rep_code);
        if(count($sales_person) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Sales Person."
            );
        }

        $date_created = Carbon::parse($issue_trans["Created"])->format('F j, Y');

        $container = null;

        $magazine_company_info = $issue_trans["Company_Information"];


//        <img src='{$logos["Magazine_Logo"]}' style='margin-left: 10px; width: 300px; height: 100px;'

        $mag_name = $issue_trans["Magazine_Name"] != null ? strtoupper($issue_trans["Magazine_Name"]) : "N/A";

        $container .= "<div style='width: 100%; margin-top: 10px; height: 150px;'> 

                            <table border='0' cellpadding='0' cellspacing='0' style='margin: 0 auto; width: 720px;'>
                                <tr>
                                    <td valign='top' style='width: 421px;'>
                                         <div style='padding: 5px 0 0 0; margin: 0 10px 0 0;'>
                                
                                            <h2 style='margin: 15px 0 0 13px;'>{$mag_name}</h2>
                                            <p style='margin: 0 0 5px 13px;'>{$magazine_company_info[0]->company_name}</p>
                                           
                                            <table border='0' cellpadding='1' cellspacing='0' style='position: relative; left: 10px; width: 290px; margin-top: 0px;'>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_company_info[0]->address_1}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_company_info[0]->city}, {$magazine_company_info[0]->state}, {$magazine_company_info[0]->zip_code}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_company_info[0]->email}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>TEL: {$magazine_company_info[0]->phone}</span></td>
                                                </tr>
                                            </table>
                                            
                                        </div>
                                    </td>
                                    <td valign='top'>
                                         <div style='padding: 0 0 0 0; margin: 20px 0 0 0;'>
                                
                                            <h3 style='text-align: right; padding: 0 0 0 0; margin: 5px 25px 0 0; font-size: 1.8em; font-family: Tahoma, Segoe UI;'>Insertion Order</h3> 
                                           
                                            <table border='0' cellpadding='5' cellspacing='0' style='width: 290px; margin-top: 10px;'>
                                                <tr>
                                                    <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1;'><span>Agreement No. {$trans_num}</span></td>
                                                </tr>
                                                <tr>
                                                    <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600; border: 1px solid #b1b1b1;'><span>Agreement Date. {$date_created}</span></td>
                                                </tr>
                                            </table>
                                            
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                        </div>";

        $container .= "<div style='width: 100%; height: 180px; '>

                            <div style='width: 100%; padding: 5px;'>
                                <table border='0' cellpadding='5' cellspacing='0' style='position: relative; left: 5px; width: 700px; margin-top: 10px;'>
                                    <tr>
                                        <th style='border: 1px solid #b1b1b1;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Advertiser</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; '><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Bill To</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; '><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Agency</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; '><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Account Executive</span></th>
                                    </tr>
                                    <tr>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$Company_Info[0]->company_name}<br />
                                            {$Company_Info[0]->address}<br />
                                            {$Company_Info[0]->city}, {$Company_Info[0]->state}, {$Company_Info[0]->zip_code}
                                        </td>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$Contact_Info[0]->first_name} {$Contact_Info[0]->last_name}<br />
                                            {$Contact_Info[0]->address_1}<br />
                                            {$Contact_Info[0]->city}, {$Contact_Info[0]->state}, {$Contact_Info[0]->zip_code}<br />
                                            {$Contact_Info[0]->email}<br />
                                            {$Contact_Info[0]->mobile}
                                        </td>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$Contact_Info[0]->first_name} {$Contact_Info[0]->last_name}<br />
                                            {$Contact_Info[0]->address_1}<br />
                                            {$Contact_Info[0]->city}, {$Contact_Info[0]->state}, {$Contact_Info[0]->zip_code}<br />
                                            {$Contact_Info[0]->email}<br />
                                            {$Contact_Info[0]->mobile}
                                        </td>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$booking_sales["Data"][0]->sales_rep_name}<br />
                                            {$sales_person[0]->email}<br />
                                            {$sales_person[0]->mobile}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                        </div>";

        $line_items = $this->list_of_line_items($booking_sales["Data"], $issue_trans, $discretionary_discount, $Company_Info[0]->is_member, $issue_discounts);

        $container .= "<div style='width: 100%; height: 395px;'>

                            <div style='width: 100%; padding: 5px;'>
                                <table border='0' cellpadding='5' cellspacing='0' style='position: relative; left: 5px; width: 700px; margin-top: 10px;'>
                                    <tr>
                                        <th style='border: 1px solid #b1b1b1;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Publication</span></th>
                                        <th style='width: 120px; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Issue</span></th>
                                        <th style='width: 120px; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Size</span></th>
                                        <th style='width: 70px; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: right; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Amount</span></th>
                                        <th style='width: 70px; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Discount</span></th>
                                        <th style='width: 70px; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Total</span></th>
                                    </tr>
                                    {$line_items}
                                </table>
                            </div>
                            
                        </div>";

        $new_total = KPAHelper::cache_memory("total", null, true);
        $issue_discount = KPAHelper::cache_memory("issue_discount", null, true);
        $sub_total = KPAHelper::cache_memory("sub_total", null, true);
        $discretionary_discount = KPAHelper::cache_memory("discretionary_discount", null, true);
        $total_taxes = KPAHelper::cache_memory("total_taxes", null, true);
        $over_all_total = KPAHelper::cache_memory("over_all_total", null, true);

        $container .= "<div style='width: 100%; height: 130px; background: white;'> 

                            <table border='0' cellpadding='0' cellspacing='0' style='margin: 0 auto; width: 720px;'>
                                <tr>
                                    <td style='width: 402px; padding: 10px; '>
                                        <p style='padding: 15px 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                            For online payments, please go to online.lesterdigital.com. We accept Visa and 
                                            MasterCard.<br /><br />
                                            Note: When paying for specific issues only. simply add the net items together
                                            and add the applicable taxes. If you need assistance, please call
                                            204-953-2189 or email billing@lesterpublications.com
                                        </p>
                                    </td>
                                    <td>
                                        <table border='0' cellpadding='5' cellspacing='0' style='width: 290px;'>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Total:</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>{$new_total}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Taxes</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>+ {$total_taxes}%</b></td>
                                            </tr>
                                            <tr style='background: #262626;'>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; color: #ffffff;'> <b>TOTAL</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; color: #ffffff;'><b>{$over_all_total}</b></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                        </div>";

        $container .= $this->page_2($trans_num);

        return $container;
    }

    public function page_2($ref_num) {

        $html = "<div style='page-break-after: always; width: 100%; height: 200px; margin-top: 30px; position: relative;'>

                        <div style='width: 100%; padding: 5px;'>
                            
                           <h3 style='text-align: center; padding: 0 0 0 0; margin: 10px 0 0 0; font-family: Tahoma, Segoe UI;'>INSERTION ORDER AGREEMENT</h3>
                           
                           <h4 style='text-align: center; padding: 0 0 0 0; margin: 10px 0 0 0; font-family: Tahoma, Segoe UI;'>Agreement No.: {$ref_num}</h4>
                            
                           <p style='text-align: left; padding: 0 0 0 0; margin: 20px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>By choosing, 'total amount of insertion order' plus applicable taxes, a one time payment will be charged to Credit Card immediately.</p>
                           <p style='text-align: left; padding: 0 0 0 0; margin: 5px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>By choosing 'each separate edition', Credit Card will be charged at the beginning of each editions production process.</p>
                           <p style='text-align: left; padding: 0 0 0 0; margin: 5px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>An invoice and transaction receipt for credit card charges/payments will be e-mailed to: [ BILL TO EMAIL ADDRESS ].</p>
                           
                           <h4 style='text-align: center; padding: 0 0 0 0; margin: 20px 0 0 0; font-family: Tahoma, Segoe UI;'>PLEASE LEAVE BLANK IF YOU WISH TO RECEIVE AN INVOICE</h4>
                           
                           <br />
                           
                           <table style='width: 100%;'>
                                <tr>
                                    <td style='width: 55%;'>
                                        <p style='border-top: 1px solid #b1b1b1; width: 90%; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Name (Please Print)</p>
                                    </td>
                                    <td>
                                        <p style='border-top: 1px solid #b1b1b1; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Authorized Signature</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style='border-top: 1px solid #b1b1b1; width: 90%; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>PO/IO Number If Applicable</p>
                                    </td>
                                    <td>
                                        <p style='border-top: 1px solid #b1b1b1; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Date</p>
                                    </td>
                                </tr>
                           </table>
                           
                           <br />
                           <p style='text-align: left; padding: 0 0 0 0; margin: 0px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b>By signing this form, you agree to the Advertising Contract & Regulations listed at: www.lesterpublications.com/ads.php</p>
                           <p style='text-align: left; padding: 0 0 0 0; margin: 5px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b>By signing this form, you wis to receive an invoice & tearsheet in the following format: Physical Invoice/Tearsheet</p>
                           <p style='text-align: left; padding: 0 0 0 0; margin: 5px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b>Payment Terms on Invoice is Due Upon Receipt.</p>
                           <p style='text-align: left; padding: 0 0 0 0; margin: 5px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b>Subject to applicable taxes.</p>
                           
                           <p style='text-align: center; padding: 0 0 0 0; margin: 20px 0 0 0; font-size: 0.9em; font-family: Tahoma, Segoe UI;'>
                            Unless noted otherwise, paperless billing is the preferred billing method in support of greener initiatives as a company.
                            A physical copy of the above mentioned publication(s) will be mailed for your records and advertising reference.</p>
                            
                            <br />
                            
                        </div>
                        
                    </div>";
        return $html;

    }

    public function list_of_line_items($booking_sales, $issue_trans, $discretionary_discount, $is_member, $issue_discounts) {

        $data = $booking_sales;
        $trans_num = $data[0]->trans_num;

        $bill_to_id = $data[0]->agency_id != null ? $data[0]->agency_id : 0;
        $data_issue_trans = $issue_trans["Data"];
        $total_taxes = KPAHelper::get_taxes($bill_to_id);
        $new_total = 0;
        $row = "";

        for($i = 0; $i < count($data_issue_trans); $i++) {

            $week = $data_issue_trans[$i]->week_id;;
            $month =  $this->getMonth($data_issue_trans[$i]->month_id);

            if($week > 0) {
                $mode = "Weekly";
                $issue = $month . " | Week-" . $week;
            }
            else {
                $mode = "Monthly";
                $issue = $month . ' - ' . $data_issue_trans[$i]->year;
            }

            $amount = $data_issue_trans[$i]->amount;

            $amount_formatted = number_format($amount, 2, '.', ',');

            $item_discount = KPAHelper::get_digital_discount_per_item($trans_num, $data_issue_trans[$i]->Id);

            $item_discount = $amount * ($item_discount / 100);

            $item_discount_formatted = number_format($item_discount, 2, '.', ',');

            $total_amount = $amount - $item_discount;

            $new_total += $total_amount;

            $total_amount_formatted = number_format($total_amount, 2, '.', ',');

            $sizes = explode(" - ", $data_issue_trans[$i]->ad_size);

            $row .= "
            <tr>
                <td style='padding: 5px; border-left: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$data_issue_trans[$i]->mag_name}</span></td>
                <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$mode}<br />{$issue}</span></td>
                <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$sizes[0]}<br />{$sizes[1]}</span></td>
                <td style='text-align: right; padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style=' font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$amount_formatted}</span></td>
                <td style='text-align: right; padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style=' font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$item_discount_formatted}</span></td>
                <td style='text-align: right; padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style=' font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$total_amount_formatted}</span></td>
            </tr>
            ";
        }

        KPAHelper::cache_memory("total", number_format($new_total, 2, '.', ','));
        KPAHelper::cache_memory("issue_discount", number_format(0, 2, '.', ','));
        KPAHelper::cache_memory("sub_total", number_format($new_total, 2, '.', ','));

        $discretionary_discount_added = 0;
        if( COUNT($issue_discounts) > 0) {
            $IsApproved = (int)$issue_discounts->status;
            if($IsApproved == 2) {
                $discretionary_discount_added = $new_total - $issue_trans["Discounted_Amount"];
            }
        }

        KPAHelper::cache_memory("discretionary_discount", number_format($discretionary_discount_added, 2, '.', ','));
        $tax_percentage = $total_taxes * 100;
        KPAHelper::cache_memory("total_taxes", number_format($tax_percentage, 0, '.', ','));

        $new_total = $new_total - $discretionary_discount_added;
        $new_total = $new_total + ($new_total * $total_taxes);

        KPAHelper::cache_memory("over_all_total", number_format($new_total, 2, '.', ','));

        return $row;
    }

    public function getMonth($Id) {
        switch ((int)$Id) {
            case 1: return "January";
            case 2: return "February";
            case 3: return "March";
            case 4: return "April";
            case 5: return "May";
            case 6: return "June";
            case 7: return "July";
            case 8: return "August";
            case 9: return "September";
            case 10: return "October";
            case 11: return "November";
            case 12: return "December";
        }
    }

    public function Generate_PDF($trans_num) {
        $html = $this->Init($trans_num, "PDF");
        $html = "<div style='width: 720px; height: 198px;'>{$html}</div>";
        return $this->html_to_pdf($html, $trans_num);
    }

    public function html_to_pdf($html, $trans_num) {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Letter', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser

        $file_name = "Digital-Insertion-Order-" . $trans_num;
        $dompdf->stream($file_name);
    }
}
