<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\ClientContacts;

class ContactClass extends Controller
{
    //
    public static function get_client_info() {

        KPAHelper::set_access_control_allow_origin();

        $data_1 = DB::select("SELECT * FROM client_table ORDER BY created_at ASC;");

        $total_data_1 = count($data_1);

        if($total_data_1 <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data_1,
                "Data" => []
            );
        }

        $data_2 =[];
        $total_list_of_contacts = 0;
        for($i = 0; $i < $total_data_1; $i++) {
            if($i == 0) {
                unset($data_2);
            }
            $uid = $data_1[$i]->Id;
            $list_of_contacts = DB::select("SELECT * FROM client_contacts_table WHERE client_id = {$uid} AND synched != 2 ORDER BY Id ASC;");
            $data_2[] = array(
                        "cid" => $data_1[$i]->Id,
                        "company_name" => $data_1[$i]->company_name,
                        "is_member" => $data_1[$i]->is_member,
                        "type" => $data_1[$i]->type,
                        "status" => $data_1[$i]->status,
                        "created_at" => $data_1[$i]->created_at,
                        "total_contacts" => count($list_of_contacts),
                        "list_of_contacts" => $list_of_contacts
                    );

            $total_list_of_contacts += count($list_of_contacts);
        }

        $status = 404;
        $total_data_2 = count($data_2);
        if($total_data_2 > 0) {
            $status = 200;
        }

        $view_contacts = array(
            "Status" => $status,
            "Message" => $status == 200 ? "Success." : "No Record.",
            "Total_Company" => $total_data_1,
            "Total_Company_Contacts" => $total_list_of_contacts,
            "Data" => $data_2
        );

        return $view_contacts;
    }

    public static function update_client_sync($contact_id) {
        $c_uid = (int)$contact_id;

        $data = ClientContacts::where("Id", "=", $c_uid)
            ->update(
                array("synched" => 2)
            );

        if($data) {
            return array("Status" => 200);
        }
        return array("Status" => 500);
    }
}
