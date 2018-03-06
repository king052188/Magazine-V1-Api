<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingSalesClass extends Controller
{
    //
    public function index(Request $request) {

        $trans = [];

        if( IsSet($request["trans"]) ) {

            $trans = ["id" => (int)$request["trans"]];
        }

        return view('work.kpa.contract.report', compact('trans'));
    }

    public function report($value = null) {

        $lists = KPAHelper::get_booking_sales_report($value);

        return $lists;
    }

    public function issue_report($value) {
        
        $lists = KPAHelper::get_issue_transactions($value);

        return $lists;
    }

    public function digital_report($value, $month = null, $week = null) {

        $month_ = 0;
        if($month != null) {
            $month_ = (int)$month;
        }

        $week_ = 0;
        if($week != null) {
            $week_ = (int)$week;
        }

        $lists = KPAHelper::get_digital_transactions($value, $month_, $week_);

        return $lists;
    }

    public function digital_item_discount($id) {

        KPAHelper::set_access_control_allow_origin();

        $item_id = (int)$id;

        $data = DB::select("
                SELECT item.Id, CONCAT(users.first_name, ' ', users.last_name) AS sales_name, item.amount, item.discount_percent, item.created_at
                FROM magazine_digital_discount_transaction_table AS item
                INNER JOIN user_account AS users
                ON item.sales_rep_id = users.Id
                WHERE item_id = {$item_id};");

        return
            array(
                "Count" => COUNT($data),
                "Data" => $data,
            );
    }
}
