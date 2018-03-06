<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use Illuminate\Support\Facades\Mail;


class NotificationClass extends Controller
{
    //

    public function get_all_notification($role) {

        KPAHelper::set_access_control_allow_origin();

        $x_role = (int)$role;

        $unread = DB::select("SELECT COUNT(*) AS t_unread FROM notifications_table WHERE role = {$x_role} AND noti_flag = 1;");

        $lists = DB::select("
            SELECT *, 
                (
                    SELECT CONCAT(first_name, ' ', last_name) 
                    FROM user_account 
                    WHERE Id = from_user_uid
                ) AS from_name
            FROM notifications_table 
            WHERE role = {$x_role}
            ORDER BY created_at DESC
            LIMIT 10
        ");

        if(count($lists) > 0) {
            return array(
                "Code" => 200,
                "Message" => "Success.",
                "Total_Unread" => $unread[0]->t_unread,
                "Total_Item" => count($lists),
                "Data" => $lists
            );
        }

        return array(
            "Code" => 404,
            "Message" => "No Record.",
            "Total_Unread" => 0,
            "Total_Item" => 0,
            "Data" => []
        );
    }

    public function read_notification($id) {

        KPAHelper::set_access_control_allow_origin();

        $read = DB::table("notifications_table")
            ->where("Id", "=", $id)
            ->update(
            array("noti_flag" => 2)
        );

        if($read) {
            return array(
                "Code" => 200,
                "Message" => "Success."
            );
        }

        return array(
            "Code" => 500,
            "Message" => "Fail."
        );
    }

    public function sendBookingInvoice($bill_to, $trans, $subject) {

        KPAHelper::set_access_control_allow_origin();

        $bill_info = KPAHelper::get_contact_info($bill_to);
        if( count($bill_info) == 0) {
            return array("code" => 404);
        }

        $body = "Hi ". $bill_info[0]->first_name .",<br /><br />";
        $body .= "Here's your Insertion Order: <a href='http://client.lesterdigital.com/kpa/work/generate/insertion-order-pdf/{$trans}'>Click here to Download</a>";

        $data = [
            "body" => $body,
            "name" => $bill_info[0]->first_name .' '. $bill_info[0]->last_name,
            "to" => $bill_info[0]->email,
            "subject" => $subject
        ];

        $type = "mail.notification";

        Mail::send($type, $data, function($email) use ($data)
        {
            $email
                ->to($data["to"], $data["name"])
                ->subject($data["subject"]);
        });

        if(count(Mail::failures()) > 0){
            return array("code" => 500);
        }

        return array("code" => 200);

    }
}
