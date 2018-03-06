<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class ReportController extends Controller
{
    //

    public function get_goal_amount($mag_id, $issue, $year, $sales) {
      KPAHelper::set_access_control_allow_origin();

      $a = (int)$mag_id;
      $b = (int)$issue;
      $c = (int)$year;
      $d = (int)$sales;

      $db = DB::select("
      SELECT c.trans_num
      FROM magazine_transaction_table AS a
      INNER JOIN magazine_issue_transaction_table AS b
      ON a.Id = b.magazine_trans_id
      INNER JOIN booking_sales_table AS c
      ON a.transaction_id = c.Id
      WHERE b.quarter_issued = {$b}
      AND DATE_FORMAT(b.created_at, '%Y') = {$c}
      AND a.magazine_id = {$a}
      AND c.sales_rep_code = {$d}
      AND b.status = 3
      ");

      $data = [];
      $total_amount = 0;
      for($i = 0; $i < COUNT($db); $i++) {
        $issue_report = $this->issue_report($db[$i]->trans_num, $b, $d);
        $data[] = $issue_report;
        $total_amount += $issue_report["Total"];
      }

      return array(
        "Total_Amount" => $total_amount,
        "Data" => $data
      );
    }

    public function issue_report($trans_num, $issue, $sales = -1) {

        $booking_sales = KPAHelper::get_booking_sales_report($trans_num, $sales);
        if($booking_sales["Status"] == 404) {
            return array(
                "Total" => 0,
                "Sales_Commission" => 0
            );
        }

        $lists = KPAHelper::get_issue_transactions($booking_sales["Data"][0]->Id);
        $info = KPAHelper::get_company_information($booking_sales["Data"][0]->client_id, $booking_sales["Data"][0]->agency_id);
        $client = $info["Company_Info"][0];
        $total_ = 0;

        if( COUNT($lists) > 0) {

            $discretionary = (float)$lists["Discounted_Amount"];
            $line_discount = 0;
            if( COUNT($lists["Issue_Discounts"]) > 0 ) {
                $line_discount = $lists["Issue_Discounts"]["Issue_Percent"];
            }

            $total_item_amount = 0;
            $issue_booked = (int)$issue;
            $data_issue_trans = $lists["Data"];

            for($i = 0; $i < COUNT($data_issue_trans); $i++ ) {

                $total_member_discount = 0;
                $qty_discount = 0;
                $new_price = 0;

                if($issue_booked != -1) {
                    if((int)$data_issue_trans[$i]->quarter_issued == $issue_booked) {
                        $qty_discount = 0; //(float)$data_issue_trans[$i]->total_discount_by_percent;
                        $new_price = (float)$data_issue_trans[$i]->sub_total_amount;
                    }
                }
                else {
                    $qty_discount = 0; //(float)$data_issue_trans[$i]->total_discount_by_percent;
                    $new_price = (float)$data_issue_trans[$i]->sub_total_amount;
                }

                if($qty_discount > 0) {
                    $new_price = 0; //(float)$data_issue_trans[$i]->total_amount_with_discount;
                }

                if($client->is_member > 0) {
                    $discount = 15;
                    $discount_plus = $discount / 100;
                    $total_member_discount = $new_price * $discount_plus;
                    $new_price = $new_price - $total_member_discount;
                }

                $total_item_amount += $new_price;
            }

            // $discount_line = $total_item_amount * $line_discount; // line items discount
            // $sub_total = $total_item_amount - $discount_line; // subtract line items discount
            // $sub_total = $sub_total - $discretionary; // subtract discretionary discount


            // $discretionary_discount = KPAHelper::get_discount($trans_num, 1);
            // if(count($discretionary_discount) > 0) {
            //     $discounted_added = (float)$discretionary_discount[0]->discount_percent;
            //     $discounted_plus = $discounted_added / 100;
            //     $discretionary_discount_added = $sub_total * $discounted_plus;
            //     $sub_total = $sub_total - $discretionary_discount_added;
            // }

            //$bill_to_id = $booking_sales["Data"][0]->agency_id != null ? $booking_sales["Data"][0]->agency_id : 0;
            //$total_taxes = KPAHelper::get_taxes($bill_to_id);
            //$total_taxes_ = $sub_total * $total_taxes;
            $total_ = $total_item_amount;
        }

        return array(
            "Total" => $total_,
            "Sales_Commission" => $total_ * 0.15,
        );
    }

    public function issue_digital_report($trans_num, $issue) {

        $d = explode("-", $issue);

        $booking_sales = KPAHelper::get_booking_digital_report($trans_num);
        if($booking_sales["Status"] == 404) {
            return array(
                "Total" => 0
            );
        }
        $issue_trans = KPAHelper::get_digital_transactions($booking_sales["Data"][0]->Id);
        if(count($issue_trans["Data"]) == 0) {
            return array(
                "Total" => 0
            );
        }

        $data = $booking_sales["Data"];

        $trans_num = $data[0]->trans_num;

        $bill_to_id = $data[0]->agency_id != null ? $data[0]->agency_id : 0;

        $data_issue_trans = $issue_trans["Data"];

        $total_taxes = 0; //KPAHelper::get_taxes($bill_to_id);

        $new_total = 0;

        $issue_month = (int)$d[0];
        $issue_week = (int)$d[1];

        for($i = 0; $i < count($data_issue_trans); $i++) {

            $amount = 0;
            if($issue_month != 0 && $issue_month != 0) {
                if($data_issue_trans[$i]->month_id == $issue_month) {
                    if($data_issue_trans[$i]->week_id == $issue_week) {
                        $amount = $data_issue_trans[$i]->amount;
                    }
                    else {
                        $amount = $data_issue_trans[$i]->amount;
                    }
                }
            }
            else {
                $amount = $data_issue_trans[$i]->amount;
            }

            $item_discount = KPAHelper::get_digital_discount_per_item($trans_num, $data_issue_trans[$i]->Id);

            $item_discount = $amount * ($item_discount / 100);

            $total_amount = $amount - $item_discount;

            $new_total += $total_amount;
        }

        $new_total = $new_total + ($new_total * $total_taxes);

        return array(
            "Total" => $new_total,
            "Sales_Commission" => $new_total * 0.15,
        );
    }
}
