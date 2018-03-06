<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Dompdf\Dompdf;

use DB;

class InvoiceController extends Controller
{
    public function init_invoice_order($invoice, $proposal = null, $paid = null) {

        $data = [
            "invoice"        => $invoice,
            "proposal_uid"   => $proposal,
            "paid"           => $paid,
            "version"        => 1
        ];

        return view('transaction.invoice', compact('data'));
    }

    public function do_invoice($invoice, $IsPaid = null) {

        KPAHelper::set_access_control_allow_origin();

        $invoice_trans = KPAHelper::get_invoice_details($invoice);

        $booking_trans = KPAHelper::get_booking_sales_details($invoice_trans[0]->booking_trans);

        $client_bill_to = KPAHelper::get_company_bill_to($booking_trans[0]->client_id);

        $line_item = $this->transaction_info($booking_trans, $booking_trans[0]->Id, $invoice_trans[0]->booking_trans, $invoice_trans[0]->issue);

        if($line_item == "No Transaction History")
        {
            return $line_item;
        }

        $container = null;

        $is_paid = asset('/img/invoice-icon.png');
        if($IsPaid != null) {
            $is_paid = asset('/img/paid-icon.png');
        }

        $logo = asset('/img/company-icon.png');

        $container .= "<img style='float: left; margin: 220px 0 0 120px; position: absolute;' src='{$is_paid}' />";

        $invoice_created = Carbon::parse($invoice_trans[0]->created_at)->format('M/d/Y');
        $invoice_due_date= Carbon::parse($invoice_trans[0]->due_date)->format('M/d/Y');
        $account_executive = KPAHelper::get_sales_person($invoice_trans[0]->account_executive);

        $container .= "<div style='width: 100%; margin-top: 15px; height: 190px;'> 

                            <div style='width: 180px; float: left;padding: 5px;'>
                               <img src='{$logo}' />
                            </div>
                            
                            <div style='width: 70%; float: right; padding: 5px;'>
                            
                                <div style='padding: 5px 0 0 0; margin: 35px 10px 0 0;'>
                                
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.85em;'><b>Invoice Date:</b> {$invoice_created}</p>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.85em;'><b>Invoice Due Date:</b> {$invoice_due_date}</p>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Invoice Number:</b> {$invoice_trans[0]->invoice_num}</p>
                                    <p style='padding: 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.85em;'><b>Your Account Executive:</b> {$account_executive[0]->first_name} {$account_executive[0]->last_name}</p>
                                    
                                </div>
                                
                            </div>
                            
                        </div>";
        
        $container .= "<div style='width: 100%; margin-top: 1px;'>

                            <div style='width: 100%; padding: 5px;'>
                                <h3 style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 1.8em; font-family: Consolas, Segoe UI;'>Lester Communications Invoice</h3> 
                                <p style='padding: 0 0 2px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.85em;'>701 Henry Ave. Winnipeg, MB R3E 1T9</p>
                                <p style='padding: 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.85em;'>E. billing@lesterpublications.com | P. 886-953-2189 | F. 877-565-8557</p>
                            </div>
                            
                        </div>";

        $total_amount = KPAHelper::cache_memory("total_amount", null, true);
        $total_taxes = KPAHelper::cache_memory("total_taxes", null, true);
        $overall_amount = KPAHelper::cache_memory("overall_amount", null, true);

        $container .= "<div style='width: 100%; margin-top: 10px; height: 160px;'> 

                            <div style='width: 58.8%; height: 182px; float: left; border-right: 2px solid gray;'>
                                
                                <div style='padding: 10px; height: 70px'>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Bill To:</b> {$client_bill_to[0]->first_name } {$client_bill_to[0]->last_name} (<i>{$client_bill_to[0]->position}</i>)</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>
                                        {$client_bill_to[0]->address_1}, {$client_bill_to[0]->city}, {$client_bill_to[0]->state} <br />
                                        {$client_bill_to[0]->email}  <br />{$client_bill_to[0]->landline} | {$client_bill_to[0]->mobile}
                                    </p>
                                </div>
                                
                                <div style='padding: 10px; border-top: 2px solid gray;'>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.75em;'><b>Notes:</b> This invoice has been automatically generated and should paid</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.75em;'>on {$invoice_created}.</p>
                                </div>
                                
                            </div> 
                            
                            <div style='width: 38%; height: 182px; float: right; padding: 10px;'>
                            
                                <table border='0' cellpadding='1' cellspacing='0' style='width: 100%';>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Amount</td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$total_amount}</td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Discount</td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>0.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> <hr /></td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>TOTAL</b></td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>{$total_amount}</b></td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Taxes</td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>{$total_taxes}</td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>Account Balance</b> <br /> <span style='font-size: 0.80em;'>as of {$invoice_created}</span></td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>{$overall_amount}</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> <hr /></td>
                                    </tr>
                                </table>
                                
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 22px;'>

                            <div style='width: 100%;'>
                                <p style='padding: 0 0 10px 0; margin: 0 0 0 0; font-family: Tahoma, Segoe UI; font-size: 1em;'><b>Line Items</b></p>
                                {$line_item}
                            </div>

                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px; background: #cbdcfb; border: 3px solid #2f5caf;'>

                            <div style='width: 100%; padding: 5px; margin: 0; '>
                                <h3 style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>PAYMENT OPTION B: Cheque - Mail to 701 Henry Ave. Winnipeg, MB R3E 1T9</h3> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px;'>

                            <div style='width: 100%; padding: 5px; margin: 0; '>
                                <p style='padding: 1px 0 0 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.75em;'>
                                Please make cheques payable to: <b>Lester Communications Inc.</b> referencing Invoice# {$invoice_trans[0]->invoice_num} to ensure accurate posting to your account.
                                </p> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px; background: #cbdcfb; border: 3px solid #2f5caf;'>

                            <div style='width: 100%; padding: 5px; margin: 0; '>
                                <h3 style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>PAYMENT OPTION C: Wire Transfer / EFT / ACH</h3> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px;'>

                            <div style='width: 100%; padding: 5px; margin: 0; '>
                                <p style='padding: 1px 0 0 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.75em;'>
                                We also accept WIRE/EFT/ACH payments. To obtain banking information to transfer funds and make payment this way, please send a request by

                                e-mail to billing@lesterpublications.com noting the invoice number you wish to pay, the amount and the expected date the transfer will be made.
                                
                                This ensures we can properly track and post the payment to your account in a timely manner.
                                </p> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px;'>

                            <div style='width: 100%; padding: 5px; margin: 0; '>
                                <h3 style='text-align: center; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; color: #2f5caf;'>
                                If you have already paid this invoice, thank you! This copy is for your records only.
                                </h3> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; height: 50px;'> 

                            <div style='width: 45%; float: left; padding: 10px; margin-top: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                    Questions about your invoice or account? <br />
                                    Please e-mail billing@lesterpublications.com <br />
                                    or call 204-953-2580.  <br />
                                    GST/HST/BN: 88839 9912
                                </p>
                            </div>
                            
                            <div style='width: 49.3%; float: right; padding: 10px; margin-top: 5px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                    Questions about your advertising campaign? <br />
                                    Please e-mail jcumming@lesterpublications.com <br />
                                    or call 877-953-2197. <br />
                                </p>
                            </div>
                            
                        </div>";

        return $container;
    }

    public function transaction_info($booking_sales, $trans_uid, $trans_booked, $proposal = null) {

        $client_id = $booking_sales[0]->client_id != null ? $booking_sales[0]->client_id : 0;

        $bill_to_id = $booking_sales[0]->agency_id != null ? $booking_sales[0]->agency_id : 0;

        $booking_trans = KPAHelper::get_issue_transactions($trans_uid, $proposal);

        $issue_discounts = $booking_trans["Issue_Discounts"][0];

        $discretionary_discount = KPAHelper::get_discount($trans_booked, 1);

        $discretionary_discount_added = 0;

        if( count($discretionary_discount) > 0 ) {
            $discretionary_discount_added = $discretionary_discount[0]->discount_percent / 100;
        }

        $get_taxes = KPAHelper::get_taxes($bill_to_id);

        $data_count = count($booking_trans["Data"]);

        if($data_count == 0) {
            return "No Transaction History";
        }

        $company_info = $booking_trans["Company_Information"];

        $company = KPAHelper::get_company_information($client_id, 0);

        $is_member = 0;
        if(count($company["Company_Info"]) > 0) {
            $is_member = $company["Company_Info"][0]->is_member;
        }
        $data = $booking_trans["Data"];

        $doc = "<table border='0' cellpadding='1' cellspacing='0' style='width: 750px; border: 0px solid gray;'>";

        $doc .= "<tr> 
                    <td style='width: 30px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>ID</b> </td> 
                    <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Pub.</b> </td> 
                    <td style='width: 50px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Issue</b> </td> 
                    <td style='width: 50px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Year</b> </td> 
                    <td style='width: 70px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Ad Size</b> </td> 
                    <td style='width: 70px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Colour</b> </td> 
                    <td style='width: 50px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>QTY</b> </td> 
                    <td style='width: 70px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Net</b> </td> 
                    <td style='width: 70px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>GST/HST</b> </td> 
                    <td style='width: 100px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-top: 2px solid gray;  border-right: 2px solid gray; border-bottom: 2px solid gray;'> <b>Amount</b> </td> 
                </tr>";

        $item_number = 0;
        $publisher = $company_info[0]->company_name;
        $year_issue = $booking_trans["Magazine_Year"];

        $total_amount = 0;
        $total_taxes = 0;
        $overall_amount = 0;
        for($i = 0; $i < count($data); $i++) {

            $colour = KPAHelper::get_criteria($data[$i]->ad_criteria_id);
            $ad_size = KPAHelper::get_package($data[$i]->ad_package_id);

            // get basis amount
            $net = (float)$data[$i]->total_amount_with_discount;

            // apply member discount
            if($is_member > 0) {
                $net = $net - ($net * 0.15);
            }

            // apply more that 1 issue discount
            $issue_total_discount = (float)$issue_discounts->Total_Issue_Discount;
            $issue_total_discount = $net * $issue_total_discount;
            $net = $net - $issue_total_discount;

            // apply discretionary discount
            $net = $net - ($net * $discretionary_discount_added);

            $net_amount = number_format($net, 2, '.', ',');

            $tax = $net * (float)$get_taxes;

            $taxes = number_format($tax, 2, '.', ',');

            $amount = $net + $tax;

            $t_amount = number_format($amount, 2, '.', ',');

            $total_amount += $net;
            $total_taxes += $tax;
            $overall_amount += $amount;

            $doc .= "<tr> 
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$data[$i]->id}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$publisher}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$data[$i]->quarter_issued}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$year_issue}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$ad_size[0]->package_name}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$colour[0]->name}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$data[$i]->line_item_qty}</td>
                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$net_amount}</td>
                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$taxes}</td>
                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em; border-left: 2px solid gray; border-bottom: 2px solid gray; border-right: 2px solid gray;'>{$t_amount}</td>
                     </tr>";

            $item_number++;
        }

        KPAHelper::cache_memory("total_amount", number_format($total_amount, 2, '.', ','));
        KPAHelper::cache_memory("total_taxes", number_format($total_taxes, 2, '.', ','));
        KPAHelper::cache_memory("overall_amount", number_format($overall_amount, 2, '.', ','));

        $overall_amount_formated = number_format($overall_amount, 2, '.', ',');
        $doc .= "<tr> 
                    <td colspan='8'></td>
                    <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>TOTAL</b></td>
                    <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.8em;'><b>{$overall_amount_formated}</b></td>
                 </tr>";

        $doc .= "</table>";

        $doc .= "</table>";

        return $doc;
    }

    public function html_to_pdf($html) {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
    
}
