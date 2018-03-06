<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;
use DB;
use Symfony\Component\Console\Helper\Helper;

class InvoiceDigitalController extends Controller
{
    //

    public function Generate_PDF($trans_num) {
        $html = $this->do_invoice($trans_num, "PDF");
        $html = "<div style='width: 720px; height: 198px;'>{$html}</div>";

        return $this->html_to_pdf($html, $trans_num);
//        return $html;
    }

    public function html_to_pdf($html, $ref_number) {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Letter', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser

        $file_name = "Invoice-". $ref_number;
        $dompdf->stream($file_name);
    }

    public function do_invoice($invoice) {

        $container = null;

        $is_paid = asset('/img/invoice-icon.png');

        $logo = asset('/img/no-logo-300-100.png');

        $invoice_trans = KPAHelper::get_invoice_details($invoice);
        if( COUNT($invoice_trans) == 0)
        {
            return [
                "Code" => 400
            ];
        }

        $booking_trans = KPAHelper::get_booking_sales_details($invoice_trans[0]->booking_trans);
        if( COUNT($booking_trans) == 0)
        {
            return [
                "Code" => 401
            ];
        }

        $issue_trans = KPAHelper::get_digital_transactions($booking_trans[0]->Id, $invoice_trans[0]->digital_month, $invoice_trans[0]->digital_week);
        if( COUNT($issue_trans) == 0)
        {
            return [
                "Code" => 403
            ];
        }

        $company_details = KPAHelper::get_company_information($booking_trans[0]->client_id, $booking_trans[0]->agency_id);
        if( COUNT($company_details) == 0)
        {
            return [
                "Code" => 404
            ];
        }

        $publisher_info = $issue_trans["Company_Information"];

//        $company_logo_uid = IsSet($issue_trans["Company_Information"][0]->logo_uid) ? $issue_trans["Company_Information"][0]->logo_uid : null;
//        $magazine_logo_uid = IsSet($issue_trans["logo_uid"]) ? $issue_trans["logo_uid"] : null;
//        $logos = $this->Append_Logo($company_logo_uid, $magazine_logo_uid, null);

        $contact_info = $company_details["Contact_Info"];

        $invoice_created = Carbon::parse($invoice_trans[0]->created_at)->format('M/d/Y');
        $invoice_due_date= Carbon::parse($invoice_trans[0]->due_date)->format('M/d/Y');
        $account_executive = KPAHelper::get_sales_person($invoice_trans[0]->account_executive);

//        $line_item = $this->transaction_info($booking_trans, $issue_trans, $invoice_trans[0]->booking_trans, $company_details);

        $total_amount = KPAHelper::cache_memory("total_amount", null, true);
        $total_taxes = KPAHelper::cache_memory("total_taxes", null, true);
        $overall_amount = KPAHelper::cache_memory("overall_amount", null, true);
        $show_total = "Total CAD: ". $overall_amount;

        $mag_name = $issue_trans["Magazine_Name"] != null ? strtoupper($issue_trans["Magazine_Name"]) : "N/A";

        $container .= "<div style='width: 100%; margin-top: 15px; height: 110px;'> 

                            <div style='width: 280px; float: left; padding: 5px;'>
                                <h2 style='margin: 15px 0 0 0;'>{$mag_name}</h2>
                                <p style='margin: 0 0 5px 0;'>{$publisher_info[0]->company_name}</p>
                            </div>
                            
                            <div style='width: 450px; float: right;'>
                           
                                <table border='0' cellpadding='0' cellspacing='0' style='width: 450px; height: 110px;'>
                                    <tr>
                                        <td style='text-align: right; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>
                                            <span>{$publisher_info[0]->company_name}</span> <br />
                                            <span>{$publisher_info[0]->address_1} {$publisher_info[0]->city}, {$publisher_info[0]->state}, {$publisher_info[0]->zip_code}</span><br /><br />
                                            <span>Email: {$publisher_info[0]->email}</span><br />
                                            <span>Phone: {$publisher_info[0]->phone}</span><br /> 
                                            <span>Fax: {$publisher_info[0]->fax}</span>
                                        </td>
                                    </tr>
                                </table>
                                
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px; height: 150px;'>

                            <div style='width: 350px;; float: left; padding: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Bill To:</b></p><br />
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$company_details["Company_Info"][0]->company_name}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->first_name} {$contact_info[0]->last_name}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->address_1}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->city}, {$contact_info[0]->state}, {$contact_info[0]->zip_code}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->email}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->mobile}</p>
                            </div>
                            
                            <div style='width: 250px; height: 137px; float: right; padding: 5px;'>
                                <h1 style='padding: 95px 0 0 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 42px;'><b>INVOICE</b></h1>
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 0px; height: 67px;'> 

                            <div style='width: 100%; float: left; border-top: 2px solid gray; border-bottom: 2px solid gray;'>
                                
                                <table border='0' cellpadding='1' cellspacing='0' style='width: 100%; padding: 10px 0 10px 0;' >
                                    <tr>
                                        <td> &nbsp; </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Invoice No. </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Invoice Date </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Due Date </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Amount Due </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 150px;'> Account Executive </td>
                                    </tr>
                                     <tr>
                                        <td> &nbsp; </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600;'> {$invoice_trans[0]->invoice_num} </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600;'> {$invoice_created} </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600;'> {$invoice_created} </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600;'> {$overall_amount} </td>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600;'> {$account_executive[0]->first_name} {$account_executive[0]->last_name} </td>
                                    </tr>
                                </table>
                                
                            </div> 
                            
                        </div>";

        $line_item = $this->transaction_info($booking_trans, $issue_trans, $invoice_trans[0]->booking_trans, $company_details);

        $container .= "<div style='width: 100%; margin-top: 0px;'>

                            <div style='width: 100%;'>
                                <table border='0' cellpadding='1' cellspacing='0' style='width: 100%; padding: 10px 0 10px 0;' >
                                    {$line_item}
                                    <tr>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                        <td style='border-bottom: 2px dashed gray;'> &nbsp; </td>
                                    </tr>
                                </table>
                            </div>

                        </div>";

        $container .= "<div style='width: 100%; margin-top: 4px; height: 83px;'>

                            <div style='width: 460px;; float: left; padding: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em; color: #234BE2;'><b>PAYMENT OPTION A:</b></p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Visa or MasterCard - Please complete below and fax this</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>invoice to 877-565-8557. Credit Card will be charged one time in the amount of</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>$600.00. A transaction receipt will be e-mailed to: corybradburn@comcast.net</p>
                            </div>
                            
                            <div style='width: 230px; float: right;'>
                                <h3 style='background: gray; padding: 10px 5px 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em; color: #ffffff;'><b>Total Tax: {$total_taxes}</b></h3>
                                <h3 style='background: gray; padding: 5px 5px 10px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em; color: #ffffff;'><b>{$show_total}</b></h3>
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 0px; height: 165px;'>

                            <div style='width: 460px; float: left; padding: 5px;'>
                                <table border='1' cellpadding='0' cellspacing='0' style='width: 100%; height: 150px; padding: 0 0 0 0;' >
                                    <tr>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Card No. </td>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Expiry </td>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> CVV (Required) </td>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Card Holder Name </td>
                                        <td> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px;'> Card Holder Signature </td>
                                        <td> &nbsp; </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style='width: 230px; float: right;'>
                                <p style='padding: 0 0 0px 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; color: red;'>If you have already paid this invoice, thank you! This copy is for your records only.</p>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                    <span><b>Questions about your invoice or account?</b></span><br />
                                    Please e-mail billing@lesterpublications.com <br />
                                    or call 204-953-2580.  <br />
                                    GST/HST/BN: 88839 9912
                                </p>
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 0px; height: 52px;'>

                            <div style='width: 460px; float: left; padding: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em; color: #234BE2;'><b>PAYMENT OPTION B:</b></p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Cheque - Mail to {$publisher_info[0]->address_1} {$publisher_info[0]->city}, {$publisher_info[0]->state}, {$publisher_info[0]->zip_code}</p>
                            </div>
                            
                            <div style='width: 230px; float: right;'>
                                 <p style='padding: 0 0 5px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                    <span><b>Questions about your advertising campaign?</b></span><br />
                                    Please e-mail billing@lesterpublications.com <br />
                                    or call 204-953-2580.
                                </p>
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 0px; height: 80px;'>

                            <div style='width: 460px; float: left; padding: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em; color: #234BE2;'><b>PAYMENT OPTION C:</b></p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Wire Transfer / EFT / ACH. We also accept WIRE/EFT/ACH payments. To obtain</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>banking information to transfer funds and make payment this way, please send</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>a request by e-mail to billing@lesterpublications.com noting the invoice number</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>you wish to pay, the amount and the expected date the transfer will be made.</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>This ensures we can properly track and post the payment to your account in a timely manner.</p>
                            </div>
                            
                            <div style='width: 230px; float: right;'>
                                 
                            </div>
                            
                        </div>";
        
        return $container;
    }

    public function transaction_info($booking_sales, $booking_trans, $trans_booked, $company_details) {

        $client_id = $booking_sales[0]->client_id != null ? $booking_sales[0]->client_id : 0;

        $bill_to_id = $booking_sales[0]->agency_id != null ? $booking_sales[0]->agency_id : 0;

        $issue_discounts = COUNT($booking_trans["Issue_Discounts"]) > 0 ? $booking_trans["Issue_Discounts"][0] : null;

        $discretionary_discount_added = 0;

        if( $issue_discounts != null ) {
            $discretionary_discount_added = 0;
        }

        $data_count = count($booking_trans["Data"]);

        if($data_count == 0) {
            return "No Transaction History";
        }

        $company_info = $company_details["Company_Info"];

        $contact_info = $company_details["Contact_Info"];

        $get_taxes = KPAHelper::get_taxes($contact_info[0]->Id);

        $data = $booking_trans["Data"];

        $doc = " <tr>
                    <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 40px; color: #234BE2;'> <b>ID</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; color: #234BE2;'> <b>Publication</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 130px; color: #234BE2;'> <b>Size</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 130px; color: #234BE2;'> <b>Issue</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Amount</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Discount</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Total</b> </td>
                </tr>";

        $item_number = 0;
        $publication = $booking_trans["Magazine_Name"];
        $year_issue = $booking_trans["Magazine_Year"];

        $total_amount = 0;
        $total_taxes = 0;
        $overall_amount = 0;

        for($i = 0; $i < count($data); $i++) {

            $item_discount = KPAHelper::get_total_digital_discount_item($trans_booked, $data[$i]->Id);
//            $item_discount = KPAHelper::get_total_digital_discount_item("1706SN9554", 2);

            $month =$this->month_name($data[$i]->month_id);
            if($data[$i]->week_id > 0) {
                $td = "<td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$year_issue}<br />{$month} Week-{$data[$i]->week_id}</td>";
            }
            else {
                $td = "<td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$year_issue}<br />{$month} Week-1 </td>";
            }

            $_discount = 0;
            $_total_amount = $data[$i]->amount;
            if( COUNT($item_discount) > 0 ) {
                $_discount = $data[$i]->amount * ($item_discount[0]->TOTAL_DISCOUNT / 100);
                $_total_amount = $data[$i]->amount - $_discount;
            }

            $_discount_format = number_format($_discount, 2, '.', ',');
            $_total_amount_format = number_format($_total_amount, 2, '.', ',');

            $sizes = explode(' - ', $data[$i]->ad_size);

            $doc .= "<tr>
                        <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$data[$i]->Id} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$publication} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$sizes[0]}<br />{$sizes[1]} </td>
                        {$td}
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$data[$i]->amount} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$_discount_format} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$_total_amount_format} </td>
                    </tr>";

            $total_amount += $_total_amount;
            $item_number++;
        }

        $get_to_be_tax = $total_amount * $get_taxes;

        $overall_amount = $total_amount + $get_to_be_tax;

        KPAHelper::cache_memory("total_amount", number_format($total_amount, 2, '.', ','));
        KPAHelper::cache_memory("total_taxes", number_format($get_to_be_tax, 2, '.', ','));
        KPAHelper::cache_memory("overall_amount", number_format($overall_amount, 2, '.', ','));

        return $doc;
    }

    public function month_name($month_id) {
        switch ($month_id) {
            case 1 : return "January";
            case 2 : return "February";
            case 3 : return "March";
            case 4 : return "April";
            case 5 : return "May";
            case 6 : return "June";
            case 7 : return "July";
            case 8 : return "August";
            case 9 : return "September";
            case 10 : return "October";
            case 11 : return "November";
            case 12 : return "December";
        }
    }
}
