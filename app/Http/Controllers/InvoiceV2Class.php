<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;
use DB;

class InvoiceV2Class extends Controller
{
    //

    public function init_invoice_order($invoice, $is_digital = null, $proposal = null, $paid = null) {

        $data = [
            "invoice"        => $invoice,
            "is_digital"     => $is_digital,
            "proposal_uid"   => $proposal,
            "paid"           => $paid,
            "version"        => 2
        ];

        return view('transaction.invoice', compact('data'));
    }

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

    public function do_invoice($invoice, $download = null) {

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

        $issue_trans = KPAHelper::get_issue_transactions($booking_trans[0]->Id, $invoice_trans[0]->issue);
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

        $company_logo_uid = IsSet($publisher_info[0]->logo_uid) ? $publisher_info[0]->logo_uid : null;
        $magazine_logo_uid = IsSet($issue_trans["logo_uid"]) ? $issue_trans["logo_uid"] : null;
        $logos = $this->Append_Logo($company_logo_uid, $magazine_logo_uid, $download);

        $contact_info = $company_details["Contact_Info"];

        
        $invoice_created = Carbon::parse($invoice_trans[0]->created_at)->format('M/d/Y');
        $invoice_due_date= Carbon::parse($invoice_trans[0]->due_date)->format('M/d/Y');
        $account_executive = KPAHelper::get_sales_person($invoice_trans[0]->account_executive);

        $line_item = $this->transaction_info($booking_trans, $issue_trans, $invoice_trans[0]->booking_trans, $company_details);

        $total_amount = KPAHelper::cache_memory("total_amount", null, true);
        $total_taxes = KPAHelper::cache_memory("total_taxes", null, true);
        $overall_amount = KPAHelper::cache_memory("overall_amount", null, true);
        $show_total = (float)$total_taxes > 0 ? "Total CAD: ". $overall_amount : "Total USD: ". $overall_amount;

        $show_total_2 = (float)$total_taxes > 0 ? "$". $overall_amount : "$". $overall_amount;

//        dd($publisher_info);

        $container .= "<div style='width: 100%; margin-top: 15px; height: 110px;'> 

                            <div style='width: 280px; float: left; padding: 5px;'>
                               <img src='{$logos["Magazine_Logo"]}' style='width: 280px;' />
                            </div>
                            
                            <div style='width: 455px; float: right;'>
                           
                                <table border='0' cellpadding='0' cellspacing='0' style='width: 450px; height: 110px;'>
                                    <tr>
                                        <td style='text-align: right; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>
                                            <span>{$publisher_info[0]->company_name}</span> <br />
                                            <span>{$publisher_info[0]->address_1} {$publisher_info[0]->city}, {$publisher_info[0]->state}, {$publisher_info[0]->zip_code}</span> <br /><br />
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
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Bill To:</b></p> <br />
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$company_details["Company_Info"][0]->company_name}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->first_name} {$contact_info[0]->last_name}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->address_1}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$contact_info[0]->city}, {$contact_info[0]->state}</p>
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
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$show_total_2}. A transaction receipt will be e-mailed to: {$contact_info[0]->email}</p>
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

        $issue_discounts = IsSet($booking_trans["Issue_Discounts"]) ? $booking_trans["Issue_Discounts"] : 0;
        
        $discretionary_discount = KPAHelper::get_discount($trans_booked, 1);

        $discretionary_discount_added = 0;

        if( count($discretionary_discount) > 0 ) {
            $discretionary_discount_added = $discretionary_discount[0]->discount_percent / 100;
        }

        $data_count = count($booking_trans["Data"]);

        if($data_count == 0) {
            return "No Transaction History";
        }

        $company_info = $company_details["Company_Info"];

        $contact_info = $company_details["Contact_Info"];

        $get_taxes = KPAHelper::get_taxes($contact_info[0]->Id);

        $is_member = 0;
        if(count($company_info) > 0) {
            $is_member = $company_info[0]->is_member;
        }
        $data = $booking_trans["Data"];

        $doc = " <tr>
                    <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Prop. ID</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; color: #234BE2;'> <b>Publication</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 80px; color: #234BE2;'> <b>Issue</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 80px; color: #234BE2;'> <b>Year</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 80px; color: #234BE2;'> <b>Ad Size</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 80px; color: #234BE2;'> <b>Color</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Net</b> </td>
                    <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI; width: 100px; color: #234BE2;'> <b>Amount</b> </td>
                </tr>";

        $item_number = 0;
        $publication = $booking_trans["Magazine_Name"];
        $year_issue = $booking_trans["Magazine_Year"];

        $total_amount = 0;
        $total_taxes = 0;
        $overall_amount = 0;
        for($i = 0; $i < count($data); $i++) {

            $colour = KPAHelper::get_criteria($data[$i]->ad_criteria_id);
            $ad_size = KPAHelper::get_package($data[$i]->ad_package_id);

            // get basis amount

            $qty_discount = (float)$data[$i]->total_discount_by_percent;
            $net = (float)$data[$i]->sub_total_amount;

            if($qty_discount > 0) {
                $net = (float)$data[$i]->total_amount_with_discount;
            }

            // apply member discount
            if($is_member > 0) {
                $net = $net - ($net * 0.15);
            }

            // apply more that 1 issue discount
            $issue_total_discount = (float)$issue_discounts["Issue_Percent"];
            $issue_total_discount = $net * $issue_total_discount;
            $net = $net - $issue_total_discount;

            // apply discretionary discount
            $net = $net - ($net * $discretionary_discount_added);

            $net_amount = number_format($net, 2, '.', ',');

            $tax = 0;
            if((float)$get_taxes > 0) {
                $tax = $net * (float)$get_taxes;
            }

            $taxes = number_format($tax, 2, '.', ',');

            $amount = $net + $tax;

            $t_amount = number_format($amount, 2, '.', ',');

            $total_amount += $net;
            $total_taxes += $tax;
            $overall_amount += $amount;

            $doc .= "<tr>
                        <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$data[$i]->id} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$publication} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$data[$i]->quarter_issued} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$year_issue} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$ad_size[0]->package_name} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$colour[0]->name} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$net_amount} </td>
                        <td style='border-bottom: 2px dashed gray; text-align: center; font-size: 0.8em; font-family: Tahoma, Segoe UI;'> {$net_amount} </td>
                    </tr>";

            $item_number++;
        }

        KPAHelper::cache_memory("total_amount", number_format($total_amount, 2, '.', ','));
        KPAHelper::cache_memory("total_taxes", number_format($total_taxes, 2, '.', ','));
        KPAHelper::cache_memory("overall_amount", number_format($overall_amount, 2, '.', ','));
        
        return $doc;
    }

    public function Append_Logo($company, $magazine, $download = null) {

        $company_logo = KPAHelper::get_root_folder($company, "COMPANY");
        $magazine_logo = KPAHelper::get_root_folder($magazine, "MAGAZINE");

        $cam_logo = asset($company_logo["COMPANY"]["img_url"]);
        $mag_logo = asset($magazine_logo["MAGAZINE"]["img_url"]);

        if($download != null) {
            $cam_logo = $company_logo["COMPANY"]["img_path"]; //$_SERVER["DOCUMENT_ROOT"] . "/kpa-assets/magazine_logo.png"; //\kpa-uploader-plugin\basic-multiple\Storage\IMG-THUMB
            $mag_logo = $magazine_logo["MAGAZINE"]["img_path"]; //$_SERVER["DOCUMENT_ROOT"] . "/kpa-assets/magazine_logo.png"; //\kpa-uploader-plugin\basic-multiple\Storage\IMG-THUMB
        }

        return array(
            "Company_Logo" => $cam_logo,
            "Magazine_Logo" => $mag_logo
        );
    }
}
