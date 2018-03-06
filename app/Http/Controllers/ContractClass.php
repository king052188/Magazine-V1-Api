<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Dompdf\Dompdf;

class ContractClass extends Controller
{
    //
    public static $pdf_contract = "";

    public function init_insertion_order($trans_num, $type = null) {

        $data = [
            "trans_number"  => $trans_num,
            "trans_type"    => $type
        ];

        return view('transaction.insertion', compact('data'));
    }

    public function get_root_folder($uid, $type)
    {
        $root       = $_SERVER["DOCUMENT_ROOT"] . "/kpa-uploader-plugin/basic-multiple/Storage/IMG-THUMB/{$type}/". $uid;
        $url        = "/kpa-uploader/storage/IMG-THUMB/{$type}/". $uid;
        $details    = $this->read_folder($root, $url);

        $data       = array(
                        "STATUS"    => 2,
                        $type       => $details
                    );

        return $data; //["MAGAZINE"]["img_path"];
    }

    public function read_folder($root, $url) {
        $details = array();
        $img_list = \File::files($root);

        $details = array(
            "img_name"  => "no-logo",
            "img_path"  => "",
            "img_url"   => "kpa-assets/no-logo.png"
        );

        for($m = 0; $m < count($img_list); $m++) {
            $details = array(
                "img_name"  => basename($img_list[$m]),
                "img_path"  =>  $img_list[$m],
                "img_url"   =>  $url .'/'. basename($img_list[$m])
            );
        }
        return $details;
    }

    public function append_logo($company_info, $magazine_logo_uid, $download = null) {

        $company_logo = $this->get_root_folder($company_info[0]->logo_uid, "COMPANY");

        $magazine_logo = $this->get_root_folder($magazine_logo_uid, "MAGAZINE");

        $cam_logo = asset($company_logo["COMPANY"]["img_url"]);
        $mag_logo = asset($magazine_logo["MAGAZINE"]["img_url"]);

        if($download != null) {
            $cam_logo = $company_logo["COMPANY"]["img_path"]; //$_SERVER["DOCUMENT_ROOT"] . "/kpa-assets/magazine_logo.png"; //\kpa-uploader-plugin\basic-multiple\Storage\IMG-THUMB
            $mag_logo = $magazine_logo["MAGAZINE"]["img_path"]; //$_SERVER["DOCUMENT_ROOT"] . "/kpa-assets/magazine_logo.png"; //\kpa-uploader-plugin\basic-multiple\Storage\IMG-THUMB
        }

        $container = "<div style='width: 100%; height: 200px; background: #ffffff;'> 

                            <div style='width: 200px; float: left; background: #ffffff;'>
                                <img src='". $cam_logo ."' />
                            </div>
                            
                            <div style='width: 290px; height: 180px; float: left; background: #ffffff; padding: 10px; display: table;'>
                                <div style='display: table-cell; vertical-align: middle;'>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 1em;'><b>{$company_info[0]->company_name}</b></p>
                                    <p style='padding: 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->address_1}</p>
                                    <p style='padding: 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->city}, {$company_info[0]->state} </p>
                                    <p style='padding: 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->email}</p>
                                    <p style='padding: 0; margin: 0; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>TEL: {$company_info[0]->phone}, FAX: {$company_info[0]->fax}</p>
                                </div>
                            </div>
                            
                            <div style='width: 200px; float: right; right: 0; background: #ffffff;'>
                                 <img src='". $mag_logo ."' />
                            </div>
                            
                        </div>";

        return $container;

    }

    public function generate_contract_pdf(Request $request, $trans_num, $download = null) {

        $booking_sales = KPAHelper::get_booking_sales_report($trans_num);

        if($booking_sales["Status"] == 404) {
            return $booking_sales;
        }

        $issue_trans = KPAHelper::get_issue_transactions($booking_sales["Data"][0]->Id);


        //   "Discounted_Amount" => 1990.0

        if(count($issue_trans["Data"]) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Transaction History."
            );
        }

        $company_info = KPAHelper::get_company_information($booking_sales["Data"][0]->client_id);
        
        if(count($company_info) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Company Information."
            );
        }

        $date_created = Carbon::parse($issue_trans["Created"])->format('F j, Y');

        $doc = "<table border='0' cellpadding='5' cellspacing='0' style='width: 710px; border: 0px solid gray;'>";
        $doc .= "<tr> 
                    <td style='width: 30px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>#</b> </td> 
                    <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Description</b> </td> 
                    <td style='width: 70px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>Issue</b> </td> 
                    <td style='width: 50px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray; border-bottom: 2px solid gray;'> <b>QTY</b> </td> 
                    <td style='width: 100px; text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-top: 2px solid gray;  border-right: 2px solid gray; border-bottom: 2px solid gray;'> <b>Amount</b> </td> 
                </tr>";

        $data_issue_trans = $issue_trans["Data"];

        dd($data_issue_trans);

        $item_number = 1;

        $over_all_total_line_item = 0;

        for($i = 0; $i < count($data_issue_trans); $i++) {

            $quarter_issued = "IS" . $data_issue_trans[$i]->quarter_issued;

            $sub_line_item_total = $data_issue_trans[$i]->total_amount_with_discount;

            $sub_line_item_total_format = number_format($sub_line_item_total, 2, '.', ',');

            $doc .= "<tr> 
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$item_number}</td>
                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$data_issue_trans[$i]->ad_color} - {$data_issue_trans[$i]->ad_size}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$quarter_issued}</td>
                        <td style='text-align: center; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-bottom: 2px solid gray;'>{$data_issue_trans[$i]->line_item_qty}</td>
                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em; border-left: 2px solid gray; border-bottom: 2px solid gray; border-right: 2px solid gray;'>{$sub_line_item_total_format}</td>
                     </tr>";

            $item_number++;
            $over_all_total_line_item += $sub_line_item_total;
        }

        $country_code = (int)$issue_trans["Mag_Country_Code"];

        $over_all_total_line_item_format = number_format($over_all_total_line_item, 2, '.', ',');

        $total_amount = $over_all_total_line_item;

        $discounted = $issue_trans["Discounted_Amount"];

        if($discounted > 0) {

            $percent_discounted = (100 - (($discounted / $total_amount) * 100)) / 100;

            $total_discounted = $total_amount * $percent_discounted;

            $sub_amount = $discounted;

            $tax_amount = 0;

            $new_total_amount = $sub_amount + $tax_amount;
        }
        else {

            $total_discounted = 0;

            $sub_amount = $total_amount;

            $tax_amount = 0;

            $new_total_amount = $sub_amount + $tax_amount;
        }

        $total_amount_format = number_format($total_amount, 2, '.', ',');

        $total_discounted_format = number_format($total_discounted, 2, '.', ',');

        $sub_amount_format = number_format($sub_amount, 2, '.', ',');

        $new_total_amount_format = number_format($new_total_amount, 2, '.', ',');

        $doc .= "<tr> 
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>TOTAL</b></td>
                    <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>{$over_all_total_line_item_format}</b></td>
                 </tr>";

        $doc .= "</table>";

        $data_magazine_logo_uid = $issue_trans["logo_uid"];

        $data_magazine_company = $issue_trans["Company_Information"];
        
        $container = $this->append_logo($data_magazine_company, $data_magazine_logo_uid, $download);

        $container .= "<div style='width: 100%; margin-top: 15px; height: 59px; background: #ffffff;'> 

                            <div style='width: 45%; float: left; background: #ffffff; padding: 10px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Magazine:</b> {$issue_trans["Magazine_Name"]} | {$issue_trans["Mag_Code"]}</p>
                                <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Country:</b> {$issue_trans["Mag_Country"]}</p>
                            </div>
                            
                            <div style='width: 49.3%; float: right; background: #ffffff; padding: 10px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Date Created:</b> {$date_created}</p>
                                <p style='padding: 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Transaction #:</b> {$booking_sales["Data"][0]->trans_num}</p>
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px; height: 55px; background: #ffffff;'>

                            <div style='width: 100%; background: #ffffff; '>
                                <h3 style='text-align: center; padding: 10px; 0 0 0; margin: 0 0 0 0; font-size: 1.8em; font-family: Consolas, Segoe UI;'>
                                    Insertion Order Contract
                                </h3> 
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; margin-top: 10px; height: 160px; background: #ffffff;'> 

                            <div style='width: 58.8%; float: left; background: #ffffff; border-right: 2px solid gray;'>
                                
                                <div style='padding: 10px;'>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Bill To:</b> {$company_info[0]->company_name}</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->branch_name}</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->email}</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$company_info[0]->mobile}</p>
                                </div>
                                
                                <div style='padding: 10px; border-top: 2px solid gray;'>
                                    <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Notes:</b> This transaction of contract has been automatically generated and not yet approved.</p>
                                    <p style='padding: 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>If there are any problems or concerns please contact billing@magazine.com</p>
                                </div>
                                
                            </div> 
                            
                            <div style='width: 38%; float: right; background: #ffffff; padding: 10px;'>
                            
                                <table border='0' cellpadding='1' cellspacing='0' style='width: 100%';>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>Prior Balance</td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$total_amount_format}</td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>Discount</td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>({$total_discounted_format})</td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> <hr /></td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>TOTAL</b></td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>{$sub_amount_format}</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> &nbsp; </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Account Balance</b> <br /> <span style='font-size: 0.85em;'>as of {$date_created}</span></td>
                                        <td style='text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>{$new_total_amount_format}</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'> <hr /></td>
                                    </tr>
                                </table>
                                
                            </div>
                            
                        </div>";

        $container .= "<div style='width: 100%; background: #FFFFFF; margin-top: 50px;'>

                            <div style='width: 100%;'>
                                <p style='padding: 0 0 10px 0; margin: 0 0 0 0; font-family: Tahoma, Segoe UI; font-size: 1em;'><b>Line Items</b></p>
                                {$doc}
                            </div>

                        </div>";

        $sales_person = KPAHelper::get_sales_person($booking_sales["Data"][0]->sales_rep_code);

        $container .= "<div style='width: 100%; height: 59px; background: #ffffff;'> 

                            <div style='width: 45%; float: left; background: #ffffff; padding: 10px; margin-top: 30px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Prepared By:</b></p>
                                <p style='padding: 20px 0 0 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$booking_sales["Data"][0]->sales_rep_name}</p>
                                <p style='padding: 1px 0 0 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$sales_person[0]->email}</p>
                                <p style='padding: 1px 0 0 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$sales_person[0]->mobile}</p>
                            </div>
                            
                            <div style='width: 49.3%; float: right; background: #ffffff; padding: 10px; margin-top: 30px;'>
                                <p style='padding: 0 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'><b>Agreed & Signed By:</b></p>
                                <p style='padding: 20px 0 5px 0; margin: 0; text-align: right; font-family: Tahoma, Segoe UI; font-size: 0.9em;'>{$booking_sales["Data"][0]->client_name}</p>
                            </div>
                            
                        </div>";

        if($download == null) {

            $preview = false;
            if ( IsSet($request["show"]) ) {
                $preview = true;
            }

            return $this->html_show($container, $trans_num, $preview);
        }
        return $this->html_to_pdf($container) ;
    }

    public function html_show($container, $trans_num, $preview = false) {

        $html = "<div style='float: right; width: 340px; background: #E6E6E6; border-radius: 5px;'>
        <style>
            .btn {
                  -webkit-border-radius: 28;
                  -moz-border-radius: 28;
                  border-radius: 28px;
                  font-family: Arial;
                  color: #ffffff;
                  font-size: 14px;
                  padding: 10px 20px 10px 20px;
                  text-decoration: none;
            }
            
            .default {
                 background: #3498db;
                  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
                  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
                  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
                  background-image: -o-linear-gradient(top, #3498db, #2980b9);
                  background-image: linear-gradient(to bottom, #3498db, #2980b9);
            }
            
            .upload {
                  background: #d93484;
                  background-image: -webkit-linear-gradient(top, #d93484, #b82b6a);
                  background-image: -moz-linear-gradient(top, #d93484, #b82b6a);
                  background-image: -ms-linear-gradient(top, #d93484, #b82b6a);
                  background-image: -o-linear-gradient(top, #d93484, #b82b6a);
                  background-image: linear-gradient(to bottom, #d93484, #b82b6a);
            }
                
            .default:hover {
                  background: #3cb0fd;
                  background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
                  background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
                  background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
                  background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
                  background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
                  text-decoration: none;
            }
            
            .upload:hover {
                  background: #d93484;
                  background-image: -webkit-linear-gradient(top, #d93484, #d93484);
                  background-image: -moz-linear-gradient(top, #d93484, #d93484);
                  background-image: -ms-linear-gradient(top, #d93484, #d93484);
                  background-image: -o-linear-gradient(top, #d93484, #d93484);
                  background-image: linear-gradient(to bottom, #d93484, #d93484);
                  text-decoration: none;
            }
        </style>
        <script>
            function download() {
                window.location.href = '/kpa/work/transaction/generate/pdf/{$trans_num}/download';
            }
            
            function upload() {
                alert('Oops, It is being updated!');
            }
        </script>
        <div style='padding: 10px;'>
            <button class='btn default' onclick='download();'> Download Contract </button>
            <button class='btn upload' onclick='upload();'> Upload Contract </button>
        </div>
        
        </div>";

        $html = "";

        if($preview) {
            $html = "";
        }

//        $html = "<html><head></head><body style='background: #ffffff;'>";
//        $html .= "<div style='width: 100%; margin-top: 10px;'>";
        $html .= "<div style='margin: 0 auto; width: 710px; height: 900px; background: #ffffff;'>";
        $html .= $container;
        $html .= "</div>";
//        $html .= "</div>";
//        $html .= "</body></html>";

        return $html;
    }

    public function download_pdf() {

        return $this::$pdf_contract;
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

    //
    
    public function get_invoice_transaction($invoice) {

        $invoices = KPAHelper::get_invoice_details($invoice);

        $booking_sales = KPAHelper::get_booking_sales_report($invoices[0]->booking_trans);
        
        if($booking_sales["Status"] == 404) {
            return $booking_sales;
        }

        $issue_trans = KPAHelper::get_issue_transactions($booking_sales["Data"][0]->Id, $invoices[0]->issue);

        return $issue_trans;
    }
}
