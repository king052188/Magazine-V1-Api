<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Carbon\Carbon;
use App\FlatPlan;
use App\FlatPlanTransaction;

class FlatPlanController extends Controller
{
    //

    public function populate_publication() {

        KPAHelper::set_access_control_allow_origin();

        $publication = DB::select('SELECT * FROM magazine_table WHERE magazine_type = 1 AND status = 2 ORDER BY magazine_name ASC;');

        return $publication;
    }

    public function init(Request $request, $magazine_id, $reference = null) {
        $mag_id = (int)$magazine_id;
        $flat_plan = null;
        $flats = null;
        $ad_lists = null;
        $created = array("channel" => 0);

        if($reference != null) {
            $created = array("channel" => 1);
            $flat_plan = DB::select("SELECT * FROM flat_plan_table WHERE trans_number = '{$reference}';");
            $f_uid = $flat_plan[0]->Id;
            $ad_lists = $this->get_ads(
              $flat_plan[0]->magazine_id,
              $flat_plan[0]->magazine_issue
            );
            $flats = DB::select("SELECT * FROM flat_plan_transaction_table WHERE flat_id = {$f_uid} ORDER BY sort_order ASC;");
        }
        else {
          $issue = IsSet($request->issue) ? (int)$request->issue : 0;
          $ad_lists = $this->get_ads(
            $mag_id,
            $issue
          );
        }
        $reference = $this->get_new_ref_($reference);
        $mag_info = DB::select("SELECT * FROM magazine_table WHERE Id = {$mag_id} AND status = 2;");
        $mag_sizes = DB::select("SELECT * FROM price_package_table WHERE package_size IS NOT NULL;");
        return view("FlatPlan.sample", compact("created", "flat_plan", "flats", "reference", "ad_lists", "mag_info", "mag_sizes"));
    }

    public function placeholder() {
      $mag_sizes = DB::select("SELECT * FROM price_package_table WHERE package_size IS NOT NULL;");

      return view("FlatPlan.placeholder", compact("mag_sizes"));
    }

    public function get_ads($mag_id, $issue) {
      $ad_lists = DB::select("
              SELECT

                  booking.Id,

                  trans.Id AS trans_Id,

                  booking.trans_num,

                  (SELECT magazine_name FROM magazine_table WHERE Id = {$mag_id} AND status = 2) AS mag_name,

                  (SELECT company_name FROM client_table WHERE Id = booking.client_id) AS client_company,

                  (SELECT concat(first_name, ' ', last_name) FROM client_contacts_table WHERE Id = booking.agency_id) AS client_agency,

                  (SELECT name FROM price_criteria_table WHERE Id = issue.ad_criteria_id AND status = 2) AS color_name,

                  (SELECT package_name FROM price_package_table WHERE Id = issue.ad_package_id AND status = 2) AS package_name,

                  (SELECT package_size FROM price_package_table WHERE Id = issue.ad_package_id AND status = 2) AS package_size,

                  booking.created_at

              FROM booking_sales_table AS booking
              INNER JOIN magazine_transaction_table AS trans
              ON booking.Id = trans.transaction_id
              INNER JOIN magazine_issue_transaction_table AS issue
              ON trans.Id = issue.magazine_trans_id
              WHERE trans.magazine_id = {$mag_id} AND booking.status > 1 AND issue.quarter_issued = {$issue}
            ");
       return $ad_lists;
    }

    public function get_new_ref_($refNumber = null) {
        $reference = null;

        if($refNumber != null) {
            return array("number" => $refNumber);
        }

        $db_count = 0;

        do {

            $reference = KPAHelper::get_new_reference();

            $db = DB::select("SELECT * FROM flat_plan_table WHERE trans_number = '{$reference["number"]}';");

            $db_count = COUNT($db);

        } WHILE ($db_count > 0);


        return $reference;
    }

    public function populates($flat_trans_number) {
        $flat_plan = DB::select("SELECT * FROM flat_plan_table WHERE trans_number = '{$flat_trans_number}';");

        if( COUNT($flat_plan) == 0 ) {
            return array(
                "code" => 404,
                "message" => "Transaction did not found.",
                "data" => null
            );
        }

        $fid = $flat_plan[0]->Id;

        $flats = DB::select("
            SELECT (SELECT magazine_name FROM magazine_table WHERE Id = (SELECT magazine_id FROM flat_plan_table WHERE Id = f.flat_id)) AS mag_name, f.*
            FROM flat_plan_transaction_table AS f
            WHERE flat_id = {$fid} ORDER BY updated_at ASC;
        ");

        return array(
            "code" => 200,
            "message" => "Success.",
            "data" => $flats
        );
    }

    public function create_channel(Request $request) {
        $mid = (int)$request->mid;
        $mag_name = $request->mag_name;
        $tans_num = $request->tans_num;
        $year = $request->year;
        $issue = $request->issue;

        $flat = new FlatPlan();
        $flat->magazine_id = $mid;
        $flat->trans_number = $tans_num;
        $flat->magazine_name = $mag_name;
        $flat->magazine_year = $year;
        $flat->magazine_issue = (int)$issue;
        $flat->status = 1;
        $r = $flat->save();

        if($r) {
            return array(
                "code" => 200,
                "message" => "Success"
            );
        }

        return array(
            "code" => 500,
            "message" => "fail"
        );
    }

    public function insert_order(Request $request) {
        $flat = FlatPlan::where("trans_number", "=", $request->flat_ref)->first();

        if( $flat == null ) {
            return array(
                "Code" => 404,
                "Message" => "Transaction did not found."
            );
        }

        $db = FlatPlanTransaction::where("flat_id", "=", $flat->Id)
            ->where("placeholder_id", "=", $request->placeholder);

        $select_db = $db->get()->toArray();

        if( COUNT($select_db) > 0 ) {

            $inject = array();
            if($request->page == "reset") {
                $inject = array(
                    "status" => 1
                );
            }
            else {
                $inject = array(
                    "page_id" => $request->page,
                    "placeholder_id" => $request->placeholder,
                    "placeholder_size" => $request->size,
                    "status" => 2
                );
            }

            $r = $db->update(
                $inject
            );

            if($r) {
                return ["Code" => 200];
            }
            return ["Code" => 500];
        }

        if($request->page == "reset") {
            return ["Code" => 200];
        }

        $f = new FlatPlanTransaction();
        $f->flat_id = $flat->Id;
        $f->company_name = $request->company;
        $f->page_id = $request->page;
        $f->placeholder_id = $request->placeholder;
        $f->placeholder_size = $request->size;
        $f->sort_order = (int)$request->sort;
        $f->status = 2;
        $r = $f->save();

        if($r) {
            return ["Code" => 200];
        }
        return ["Code" => 500];
    }

    public function update_order(Request $request) {

        $sort_orders = explode(',', $request->sort_order);

        $r = false;

        $order = 1;
        for( $i = 0; $i < COUNT($sort_orders); $i++) {

            $index = (int)$sort_orders[$i];

            $r = FlatPlanTransaction::where("Id", "=", $index)
                ->update(
                    ["sort_order" => $order]
                );

            $order++;
        }

        if($r) {
            return ["Code" => 200];
        }
        return ["Code" => 500];
    }

    public function remove_order($id) {
        $r = FlatPlanTransaction::where("placeholder_id", "=", "drag_" . $id);

        if(COUNT($r->get()) == 0) {
          return ["Code" => 404];
        }

        if($r->delete()) {
            return ["Code" => 200];
        }
        return ["Code" => 500];
    }
}
