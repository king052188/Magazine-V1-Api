<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class BulkEmailController extends Controller
{
    //
    public static $download_url = "http://home.kpa.ph:5001";


    public static function send_invoices($print=1, $temp_email=null) {

        KPAHelper::set_access_control_allow_origin();

        $is_print = (int)$print;
        $where = $is_print == 1 ? "WHERE i.issue > 0" : "WHERE i.issue = 0";
        $url = $is_print == 2 ? "/kpa/work/transaction/generate/invoice-digital-order/download/" : "/kpa/work/transaction/generate/invoice-order-v2/download/";

        $db = DB::select("
            SELECT *
            FROM invoice_table AS i
            INNER JOIN booking_sales_table AS b
            ON i.booking_trans = b.trans_num
            {$where}
        ");

        $msg[] = array();

        for( $x = 0; $x < COUNT($db); $x++) {
            if($x == 0) {
                unset($msg);
            }

            $client_id = $db[$x]->Id;

            $invoice_number = $db[$x]->invoice_num;

            $send_to = DB::select("SELECT * FROM client_contacts_table WHERE client_id = {$client_id} AND role = 3;");

            if( COUNT($send_to) > 0) {

                $first = $send_to[0]->first_name;

                $last = $send_to[0]->last_name;

                $name = $first . " " . $last;

                if($temp_email != null) {
                    $email = $temp_email;
                }
                else {
                    $email = $send_to[0]->email;
                }

                $download_url = BulkEmailController::$download_url . $url . $invoice_number;

                $message =      "<h3>We would like to personally inform you about your invoice, see below.</h3>";
                $message .=     "To download the PDF format, please click the link: ". $download_url;

                $msg[] = BulkEmailController::post_exec($name, $invoice_number, $email, $message);
            }
        }

        return $msg;

    }

    public static function post_exec($name, $invoice, $email, $message) {
        $data = array(
            "name" => $name,
            "to" => $email,
            "subject" => "Invoice Number: {$invoice} | Lester Digital",
            "message" => $message
        );

        return  BulkEmailController::post_email_send(6, "CRM.Invoice", $data);
    }

    public static function post_email_send($uid = 6, $temp = "CRM.Invoice", $arr = array()) {
        if( count($arr) == 0) {
            return false;
        }

        $name = str_replace(" ", "%20", $arr["name"]);
        $to = str_replace(" ", "%20", $arr["to"]);
        $subject = str_replace(" ", "%20", $arr["subject"]);
        $message = str_replace(" ", "%20", $arr["message"]);

        $query = "http://postmail.kpa21.info/mail/post/email?id={$uid}&name={$name}&email={$to}&subject={$subject}&temp_name={$temp}&message={$message}";

        $result = BulkEmailController::do_curl($query);

        return $result;
    }

    public static function do_curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $data;
    }
}
