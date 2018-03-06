<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;

class InsertionClass extends Controller
{

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

    public function Init($trans_num, $download = null) {

        $booking_sales = KPAHelper::get_booking_sales_report($trans_num);
        if($booking_sales["Status"] == 404) {
            return $booking_sales;
        }

        $issue_trans = KPAHelper::get_issue_transactions($booking_sales["Data"][0]->Id);
        if(count($issue_trans["Data"]) == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Transaction History."
            );
        }

        $issue_discounts = $issue_trans["Issue_Discounts"];

        $discretionary_discount = KPAHelper::get_discount($trans_num, 1);

        $company_logo_uid = $issue_trans["Company_Information"][0]->logo_uid;
        $magazine_logo_uid = $issue_trans["logo_uid"];
        $logos = $this->Append_Logo($company_logo_uid, $magazine_logo_uid, $download);

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

        $container .= "<div style='width: 100%; width: 100%; margin-top: 10px; height: 198px;'>

                            <table border='0' cellpadding='0' cellspacing='0' style='margin: 0 auto; width: 720px;'>
                                <tr>
                                    <td valign='top' style='width: 421px;'>
                                         <div style='padding: 5px 0 0 0; margin: 0 10px 0 0;'>

                                            <img src='{$logos["Magazine_Logo"]}' style='margin-left: 10px; width: 300px; height: 100px;'  />

                                            <table border='0' cellpadding='1' cellspacing='0' style='position: relative; left: 10px; width: 290px; margin-top: 10px;'>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_company_info[0]->address_1}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_company_info[0]->city}, {$magazine_company_info[0]->state}</span></td>
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
                                                    <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1;'><span>Agreement No.: {$trans_num}</span></td>
                                                </tr>
                                                <tr>
                                                    <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; font-weight: 600; border: 1px solid #b1b1b1;'><span>Agreement Date.: {$date_created}</span></td>
                                                </tr>
                                            </table>

                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </div>";

        $container .= "<div style='width: 100%; height: 170px; '>

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
                                            {$Company_Info[0]->city}, {$Company_Info[0]->state}
                                        </td>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$Contact_Info[0]->first_name} {$Contact_Info[0]->last_name}<br />
                                            {$Contact_Info[0]->address_1}<br />
                                            {$Contact_Info[0]->city}, {$Contact_Info[0]->state}<br />
                                            {$Contact_Info[0]->email}<br />
                                            {$Contact_Info[0]->mobile}
                                        </td>
                                        <td valign='top' style='height: 110px; text-align: left; padding: 5px; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #b1b1b1;'>
                                            {$Contact_Info[0]->first_name} {$Contact_Info[0]->last_name}<br />
                                            {$Contact_Info[0]->address_1}<br />
                                            {$Contact_Info[0]->city}, {$Contact_Info[0]->state}<br />
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

        $container .= "<div style='width: 100%; height: 320px;'>

                            <div style='width: 100%; padding: 5px;'>
                                <table border='0' cellpadding='5' cellspacing='0' style='position: relative; left: 5px; width: 700px; margin-top: 10px;'>
                                    <tr>
                                        <th style='border: 1px solid #b1b1b1;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Publication</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Issue</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Year</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Ad Size</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Colour</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Amount</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Discounts</span></th>
                                        <th style='border-top: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Net</span></th>
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

        $container .= "<div style='width: 100%; height: 170px; background: white;'>

                            <table border='0' cellpadding='0' cellspacing='0' style='margin: 0 auto; width: 720px;'>
                                <tr>
                                    <td style='width: 402px; padding: 10px; ' valign='top'>
                                        <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                            For online payments, please go to online.lesterdigital.com. We accept Visa and
                                            MasterCard.
                                        </p>
                                         <p style='padding: 0 0 5px 0; margin: 0; text-align: left; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>
                                            Note: When paying for specific issues only. simply add the net items together
                                            and add the applicable taxes. If you need assistance, please call
                                            204-953-2189 or email billing@lesterpublications.com
                                        </p>
                                    </td>
                                    <td>
                                        <table border='0' cellpadding='5' cellspacing='0' style='width: 288px;'>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Total:</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>{$new_total}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Issue Discount:</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>({$issue_discount})</b></td>
                                            </tr>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Sub Total:</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>{$sub_total}</b></td>
                                            </tr><tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Discretionary Discount:</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>({$discretionary_discount})</b></td>
                                            </tr>
                                            <tr>
                                                <td style='text-align: left; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-left: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'> <b>Taxes</b> </td>
                                                <td style='text-align: right; font-size: 0.8em; font-family: Tahoma, Segoe UI; border-right: 1px solid #b1b1b1; border-top: 1px solid #b1b1b1;'><b>+{$total_taxes}</b></td>
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

        if($download == null) {
            $artwork = KPAHelper::get_artwork($trans_num);

            if( COUNT($artwork) > 0 ) {
                $artwork_build = $this->get_artwork($artwork[0]->artwork);

                $container .= "<div style='width: 100%; height: 100px; margin-top: 30px;'>
                            <div style='width: 96%; padding: 10px; margin: 0 auto; background: #F2F2F2; '>
                                <h3 style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Artwork</h3>
                                <p style='border-bottom: 1px solid black;  text-align: left; padding: 0 0 0 0; margin: 10px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$artwork_build}</p>
                                <p style='text-align: left; padding: 0 0 0 0; margin: 10px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$artwork[0]->directions}</p>
                            </div>
                        </div>";
            }
        }

        $container .= $this->page_2($trans_num);

        return $container;
    }

    public function page_2($ref_num) {

        $html = "<div style='page-break-after: always; width: 100%; height: 200px; margin-top: 30px; position: relative;'>
                        <div style='width: 100%; padding: 5px;'>
                          <h3 style='text-align: center; padding: 0 0 0 0; margin: 10px 0 0 0; font-family: Tahoma, Segoe UI;'>INSERTION ORDER AGREEMENT</h3>
                          <h4 style='text-align: center; padding: 0 0 0 0; margin: 10px 0 0 0; font-family: Tahoma, Segoe UI;'>Agreement No.: {$ref_num}</h4>

                          <div style='border: 2px solid #E2E2E2; margin: 20px 0 0 0; background-color: #F3F3F3;'>
                            <h5 style='text-align: left; padding: 10px 10px 10px 10px; margin: 0 0 0 0; font-family: Tahoma, Segoe UI;'><span style='color: gray;'>
                              PAYMENT OPTION:</span> CREDIT CARD | <span style='color: gray;'>Rate is</span> NET CAD
                              <span style='color: gray;'>Subject to</span> GST/HST, If applicable
                            </h5>
                          </div>

                          <table border='0' style='width: 100%; padding: 0 0 0 0; margin: 20px 0 20px 0;'>
                            <tr>
                              <td style='text-align: center;'>[ ] VISA or [ ] MASTER</td>
                              <td>
                                <p style='text-align: left; padding: 2px; margin: 3px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b> Please charge credit card <b>total amount of insertion order [ ____ ]</b></p>
                                <p style='text-align: left; padding: 2px; margin: 3px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>*</b> Please charge credit card for <b>each seperate edition [ ____ ]</b></p>
                                <p style='text-align: left; padding: 2px; margin: 3px 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'><b>Note:</b> Please check off one selection above for proper credit card processing!</p>
                              </td>
                            </tr >
                          </table>

                          <table style='width: 100%;'>
                               <tr>
                                   <td>
                                       <p style='border-top: 1px solid #b1b1b1; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Card No.</p>
                                   </td>
                                   <td style='width: 180px;'>
                                       <p style='border-top: 1px solid #b1b1b1;  font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Expiry</p>
                                   </td>
                                   <td style='width: 180px;'>
                                       <p style='border-top: 1px solid #b1b1b1; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>CVV (Required)</p>
                                   </td>
                               </tr>
                          </table>

                          <table style='width: 100%;'>
                               <tr>
                                   <td>
                                       <p style='border-top: 1px solid #b1b1b1;  font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Card Holder Name</p>
                                   </td>
                                   <td>
                                       <p style='border-top: 1px solid #b1b1b1; font-family: Tahoma, Segoe UI; font-size: 0.8em;'>Card Holder Signature</p>
                                   </td>
                               </tr>
                          </table>

                        </div>
                        <div style='width: 100%; padding: 5px;'>

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

    public function Generate_PDF($trans_num) {
        $html = $this->Init($trans_num, "PDF");
        $html = "<div style='width: 720px; height: 198px;'>{$html}</div>";
        return $this->html_to_pdf($html, $trans_num);
    }

    public function list_of_line_items($booking_sales, $issue_trans, $discretionary_discount, $is_member, $issue_discounts) {

        $bill_to_id = $booking_sales[0]->agency_id != null ? $booking_sales[0]->agency_id : 0;
        $data_issue_trans = $issue_trans["Data"];
        $total_taxes = KPAHelper::get_taxes($bill_to_id);
        $new_total = 0;
        $row = "";

        $exceeded = false;
        if(count($data_issue_trans) > 8) {
            $exceeded = true;
        }

        for($i = 0; $i < count($data_issue_trans); $i++) {

            $total_member_discount = 0;
            $qty_discount = (float)$data_issue_trans[$i]->total_discount_by_percent;
            $new_price = (float)$data_issue_trans[$i]->sub_total_amount;

            if($qty_discount > 0) {
                $new_price = (float)$data_issue_trans[$i]->total_amount_with_discount;
            }

            if($is_member > 0) {
                $discount = 15;
                $discount_plus = $discount / 100;
                $total_member_discount = $new_price * $discount_plus;
                $new_price = $new_price - ($new_price * $discount_plus);
            }

            $total_member_discount_format = number_format($total_member_discount, 2, '.', ',');
            $total_amount_with_discount_format = number_format($new_price, 2, '.', ',');
            $new_total += $new_price;
            $magazine_info = KPAHelper::get_magazine_information_v2($data_issue_trans[$i]->id);

            if(!$exceeded) {
                $row .= "
                <tr>
                    <td style='padding: 5px; border-left: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_info[0]->magazine_name}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$data_issue_trans[$i]->quarter_issued}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$magazine_info[0]->magazine_year}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$data_issue_trans[$i]->ad_size}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7;'><span style='text-align: left; padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$data_issue_trans[$i]->ad_color}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; text-align: right;'><span style=' padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$data_issue_trans[$i]->sub_total_amount}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; text-align: right;'><span style=' padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>Member: {$total_member_discount_format}</span></td>
                    <td style='padding: 5px; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; text-align: right;'><span style='padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI;'>{$total_amount_with_discount_format}</span></td>
                </tr>
                ";
            }
        }

        if($exceeded) {
            $row .= "
            <tr>
                <td colspan='8' style='padding: 5px; border-left: 1px solid #b1b1b1; border-right: 1px solid #b1b1b1; border-bottom: 1px solid #c7c7c7; text-align: center;'><span style=' padding: 0 0 0 0; margin: 0 0 0 0; font-size: 0.8em; font-family: Tahoma, Segoe UI; color:red;'>Oops, you are exceeding the 8 line items limit</span></td>
            </tr>
            ";
        }

        KPAHelper::cache_memory("total", number_format($new_total, 2, '.', ','));
        $issue_total_discount = (float)$issue_discounts["Issue_Percent"];
        $issue_total_discount = $new_total * $issue_total_discount;
        $sub_total = $new_total - $issue_total_discount;

        KPAHelper::cache_memory("issue_discount", number_format($issue_total_discount, 2, '.', ','));
        KPAHelper::cache_memory("sub_total", number_format($sub_total, 2, '.', ','));

        $discretionary_discount_added = 0;
        if(count($discretionary_discount) > 0) {
            $discounted_added = (float)$discretionary_discount[0]->discount_percent;
            $discounted_plus = $discounted_added / 100;
            $discretionary_discount_added = $sub_total * $discounted_plus;
            $sub_total = $sub_total - $discretionary_discount_added;
        }
        KPAHelper::cache_memory("discretionary_discount", number_format($discretionary_discount_added, 2, '.', ','));

        $over_all_total = $sub_total;
        if($total_taxes > 0) {
            $total_taxes =  $sub_total * $total_taxes;
            $over_all_total = $sub_total + $total_taxes;
        }

        KPAHelper::cache_memory("total_taxes", number_format($total_taxes, 2, '.', ','));
        KPAHelper::cache_memory("over_all_total", number_format($over_all_total, 2, '.', ','));

        return $row;
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

        $file_name = "Insertion-Order-". $ref_number;
        $dompdf->stream($file_name);
    }

    public function get_artwork($artwork) {
        $artwork_text = null;
        switch ($artwork) {
            case 1 :
                $artwork_text = "Supplied";
                break;
            case 2 :
                $artwork_text = "Build";
                break;
            case 3 :
                $artwork_text = "Renewal";
                break;
            case 4 :
                $artwork_text = "Renewal with Changes";
                break;
        }
        return $artwork_text;
    }
}
