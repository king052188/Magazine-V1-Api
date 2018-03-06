<?php


/*
 *  Created         = Sat. November 12, 2016
 *  Developer       = King Paulo Aquino
 *  Position        = IT/Software Manager
 *  Contact         = me@kpa21.info / +63 917 771 5380 / www.kpa21.info
 *
 *  Library         = KPAHelper v1
 *  Published       = Sat. November 12, 2016
 *  Modified        = Sat. November 12, 2016
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Carbon\Carbon;

class KPAHelper extends Controller
{

    public static function set_access_control_allow_origin() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Content-Type: application/json');
    }

    public static function get_root_folder($uid, $type)
    {
        $root       = $_SERVER["DOCUMENT_ROOT"] . "/kpa-uploader-v1/basic-multiple/Storage/IMG-ORIGINAL/{$type}/". $uid;
        $url        = "/kpa-uploader-v1/storage/IMG-ORIGINAL/{$type}/". $uid;
        $details    = KPAHelper::read_folder($root, $url);

        $data       = array(
            "STATUS"    => 2,
            $type       => $details
        );

        return $data;
    }

    public static function read_folder($root, $url) {
        $img_list = \File::files($root);

        $details = array(
            "img_name"  => "no-logo-300-100",
            "img_path"  => $_SERVER["DOCUMENT_ROOT"] . "/kpa-assets/no-logo-300-100.png",
            "img_url"   => "kpa-assets/no-logo-300-100.png"
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

    /*
       get price by passing boolean value

       member = true
       non-member = false
   */

    public static function get_criteria($id) {
        $db = DB::select("SELECT * FROM price_criteria_table WHERE Id = {$id};");
        return $db;
    }

    public static function get_package($id) {
        $db = DB::select("SELECT * FROM price_package_table WHERE Id = {$id};");
        return $db;
    }

    public static function get_price_by_type($IsMember = false) {

        $IsMemberPayment = $IsMember ? 1 : 2;

        $prices_array = [];

        $get_criteria = DB::select("SELECT * FROM price_criteria_table WHERE status = 2;");

        if($get_criteria != null) {

            for( $i = 0; $i < count($get_criteria); $i++ ) {

                $packages_id = $get_criteria[$i]->Id;
                $packages_name = $get_criteria[$i]->name;
                $get_packages = DB::select("
                                    SELECT package.package_name, price.amount_x1, price.amount_x2_more
                                    FROM price_package_table AS package
                                    INNER JOIN price_table AS price
                                    ON package.Id = price.package_id
                                    WHERE price.criteria_id = {$packages_id}
                                    AND price.type = {$IsMemberPayment};");

                $prices_array[] = [
                    $packages_name => $get_packages
                ];

            }

            return $prices_array;

        }
    }

    public static function get_client_type($Id = 0) {

        if($Id == 0) {
            $data = DB::select("SELECT * FROM client_reference_table WHERE status = 2;");
        }
        else {
            $data = DB::select("SELECT * FROM client_reference_table WHERE status = {$Id};");
        }

        return $data;

    }

    public static function get_computation_qty($amount, $qty, $percent, $percent_cache) {

        $sub_amount = 0;
        $total_amount = 0;

        if($qty == 2) {
            if($percent > 0) {
                $percent_cache = $percent;
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount - ($sub_amount * $percent);
            }
            else {
                $percent_cache = 0;
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount;
            }
        }
        if($qty == 3) {
            if($percent > 0) {
                $percent_cache = $percent;
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount - ($sub_amount * $percent);
            }
            else {
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount - ($sub_amount * $percent_cache);
            }
        }
        if($qty == 4) {
            if($percent > 0) {
                $percent_cache = $percent;
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount - ($sub_amount * $percent);
            }
            else {
                $sub_amount = $amount * $qty;
                $total_amount = $sub_amount - ($sub_amount * $percent_cache);
            }
        }
        if($qty == 5) {
            if($percent > 0) {
                $percent_cache = $percent;
                $sub_amount = $amount * 5;
                $total_amount = $sub_amount - ($sub_amount * $percent);
            }
            else {
                $sub_amount = $amount * 5;
                $total_amount = $sub_amount - ($sub_amount * $percent_cache);
            }
        }

        return [
            "percent" => $percent_cache,
            "sub_amount" => $sub_amount,
            "total_amount" => $total_amount
        ];
    }

    public static function get_discount($trans_num, $type) {
        $sqlString = "SELECT * FROM discount_transaction_table WHERE trans_id = '{$trans_num}' AND type = {$type} AND status = 2;";
        return DB::select($sqlString);
    }


    public static function get_artwork($book_trans) {
        $sqlString = "SELECT * FROM artwork_table WHERE book_trans = '{$book_trans}'";
        return DB::select($sqlString);
    }

    public static function get_magazine_company_information($cid) {
        $c_id = (int)$cid;
        $sql = "SELECT * FROM magazine_company_table WHERE Id = {$c_id};";
        return DB::select($sql);
    }

    public static function get_magazine_information_v2($cid) {
        $c_id = (int)$cid;
        $sql = "SELECT trans.magazine_id
                FROM magazine_issue_transaction_table AS issue
                INNER JOIN magazine_transaction_table AS trans
                ON issue.magazine_trans_id = trans.Id
                WHERE issue.Id = {$c_id}";

        $sql_db = DB::select($sql);

        if(count($sql_db) > 0) {
            $sql = "SELECT * FROM magazine_table WHERE Id = {$sql_db[0]->magazine_id};";
            return DB::select($sql);
        }
        return null;
    }

    public static function get_magazine_information($mid) {
    $m_id = (int)$mid;
    $sql = "SELECT * FROM magazine_table WHERE Id = {$m_id};";
    return DB::select($sql);
}

    public static function get_booking_sales_details($trans_num) {
        $db = DB::select("SELECT * FROM booking_sales_table WHERE trans_num = '{$trans_num}';");
        return $db;
    }

    public static function get_invoice_details($invoice_number) {
        $db = DB::select("SELECT * FROM invoice_table WHERE invoice_num = '{$invoice_number}';");
        if( COUNT($db) == 0 ) {
            $db = DB::select("
                        SELECT invoice.*
                        FROM booking_sales_table AS book
                        INNER JOIN invoice_table AS invoice
                        ON book.trans_num = invoice.booking_trans
                        WHERE client_id = (
                            SELECT Id FROM client_table WHERE company_name = '$invoice_number'
                        );
                  ");
        }
        return $db;
    }

    public static function get_company_information($client_id, $contact_id) {
        $c1_id = (int)$client_id;
        $c2_id = (int)$contact_id;
//        $sql = "SELECT A.company_name, A.type, A.status, B.*
//                FROM client_table AS A
//                INNER JOIN client_contacts_table AS B
//                ON A.Id = B.client_id
//                WHERE A.Id = {$c_id} AND b.role = 3 OR b.role = 4;";
        $sql_1 = DB::select("SELECT * FROM client_table WHERE Id = {$c1_id};");
        $sql_2 = DB::select("SELECT A.company_name, A.type, A.status, B.*
                FROM client_table AS A
                INNER JOIN client_contacts_table AS B
                ON A.Id = B.client_id
                WHERE B.client_id = {$c1_id} AND b.Id = {$c2_id}");

        return array(
            "Company_Info" => $sql_1,
            "Contact_Info" => $sql_2
        );
    }

    public static function get_company_info($client_id) {
        $c_id = (int)$client_id;
        $sql = "SELECT * FROM client_table WHERE Id = {$c_id};";
        return DB::select($sql);
    }

    public static function get_contact_info($uid) {
        $c_id = (int)$uid;
        $sql = "SELECT * FROM client_contacts_table WHERE Id = {$c_id};";
        return DB::select($sql);
    }

    public static function get_sales_person($uid) {
        $u_id = (int)$uid;
        $sql = "SELECT * FROM user_account WHERE Id = {$u_id};";
        return DB::select($sql);
    }

    public static function get_taxes($client_id) {
        $c_id = (int)$client_id;
        $sql = "SELECT tax_amount FROM taxes_table WHERE province_code = (SELECT state FROM client_contacts_table WHERE Id = {$c_id} AND status = 2);";
        $data = DB::select($sql);
        if(count($data) > 0) {
            return (float)$data[0]->tax_amount;
        }
        return 0;
    }

    public static function cache_memory($key, $value, $IsRead = false){

        if(!$IsRead) {
            session([$key => $value]);
            return null;
        }
        return session($key);
    }

    public static function delete_cache_memory($key) {
        session()->forget($key);

        session()->flush();
    }

    public static function get_total_discount_amount($Id) {

        $sql = "
            SELECT (amount - (amount * (discount_percent / 100))) AS amount
            FROM discount_transaction_table
            WHERE trans_id =
                (
                    SELECT book.trans_num
                    FROM magazine_transaction_table AS trans
                    INNER JOIN booking_sales_table AS book
                    ON trans.transaction_id = book.Id
                    WHERE trans.Id = {$Id}
                )  AND status = 2;
        ";

        $data = DB::select($sql);

        if(count($data) > 0) {
            return (float)$data[0]->amount;
        }

        return 0;
    }

    public static function get_digital_discount_per_item($trans_number, $item_id) {

        $query = "SELECT SUM(discount_percent) AS total_discounts
                FROM magazine_digital_discount_transaction_table
                WHERE booking_trans_num = '{$trans_number}' AND item_id = {$item_id};";

        $data = DB::select($query);

        if( COUNT($data) > 0) {
            return (float)$data[0]->total_discounts;
        }

        return 0;
    }

    public static function get_total_issue_discount_v2($magazine_trans_id, $items) {

        $trans_id = (int)$magazine_trans_id;

        $trans_item = COUNT($items);

        for($i = $trans_item; $i > 1; $i--) {

            $db = DB::select("
                SELECT Id, percent, type AS qty FROM
                magazine_issue_discount_table
                WHERE magazine_id = (SELECT magazine_id FROM magazine_transaction_table WHERE Id = {$trans_id}) AND type = {$i} AND percent != 0 AND status = 2
            ");

            if( COUNT($db) > 0) {
                return array(
                    "Mag_TransID" => $trans_id,
                    "Issue_Total" => $trans_item,
                    "Issue_Percent" => (float)$db[0]->percent / 100,
                );
            }
        }

        return array(
            "Mag_TransID" => $trans_id,
            "Issue_Total" => $trans_item,
            "Issue_Percent" => 0,
        );
    }

    public static function get_total_issue_discount($magazine_trans_id) {

        $trans_id = (int)$magazine_trans_id;

        $sql = "SELECT
                    COUNT(*) AS Total_Issue,
                    CASE
                        WHEN COUNT(*) > (SELECT COUNT(*) AS t_type FROM
                                            magazine_issue_discount_table
                                            WHERE magazine_id = (SELECT magazine_id FROM magazine_transaction_table WHERE Id = magazine_trans_id) AND percent != 0) THEN
                            (
                                SELECT percent AS Discount
                                    FROM magazine_issue_discount_table
                                    WHERE magazine_id = (SELECT magazine_id FROM magazine_transaction_table WHERE Id = magazine_trans_id) AND percent != 0 AND status = 2
                                    AND type = (
                                SELECT (COUNT(*) + 1) AS t_type
                                    FROM magazine_issue_discount_table
                                    WHERE magazine_id = (SELECT magazine_id FROM magazine_transaction_table WHERE Id = magazine_trans_id) AND percent != 0 AND status = 2
                                )
                            )  / 100
                        WHEN COUNT(*) = 1 THEN
                            0
                        ELSE
                            (
                                SELECT percent AS Discount
                                    FROM magazine_issue_discount_table
                                    WHERE magazine_id = (SELECT magazine_id FROM magazine_transaction_table WHERE Id = magazine_trans_id) AND percent != 0 AND status = 2 AND type = COUNT(*)
                            ) / 100
                        END
                    AS Total_Issue_Discount
                FROM magazine_issue_transaction_table WHERE magazine_trans_id = {$trans_id};";

        if( count($sql) > 0 ) {
            return DB::select($sql);
        }
        return null;
    }

    public static function get_total_digital_discount($Id) {

        $sql = "SELECT *, (SELECT CONCAT(first_name, ' ', last_name) FROM user_account WHERE Id = sales_rep_id ) AS sales_rep_name
                FROM discount_transaction_table
                WHERE trans_id =
                    (
                        SELECT book.trans_num
                        FROM magazine_transaction_table AS trans
                        INNER JOIN booking_sales_table AS book
                        ON trans.transaction_id = book.Id
                        WHERE trans.Id = {$Id}
                    );
                ";
        return DB::select($sql);
    }

    public static function get_total_digital_discount_item($booking_number, $item_id) {
        $iid = (int)$item_id;

        $sql = "SELECT SUM(discount_percent) AS TOTAL_DISCOUNT FROM magazine_digital_discount_transaction_table WHERE booking_trans_num = '{$booking_number}' AND item_id = {$iid};";

        return DB::select($sql);
    }

    public static function get_booking_sales_report($trans = null, $sales = -1) {

        KPAHelper::set_access_control_allow_origin();

        $query = "";
        if($trans != null) {

          $query_sales = $sales != -1 ? " AND book_trans.sales_rep_code = {$sales}" : "";

          $query = "WHERE book_trans.Id = '{$trans}' OR book_trans.trans_num = '{$trans}'" . $query_sales;
        }
        else {

          $query_sales = $sales != -1 ? "book_trans.sales_rep_code = {$sales}" : "";

          $query = "WHERE {$query_sales}";
        }

        $data = DB::select("
        SELECT
            book_trans.*,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM user_account AS a
                WHERE Id = book_trans.sales_rep_code
            ) AS sales_rep_name,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM client_contacts_table AS a
                INNER JOIN client_table AS b
                ON a.client_id = b.Id
                WHERE a.Id = book_trans.client_id
                AND b.type = 1
            ) AS client_name,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM client_contacts_table AS a
                INNER JOIN client_table AS b
                ON a.client_id = b.Id
                WHERE a.Id = book_trans.agency_id
                AND b.type = 2
            ) AS agency_name,
            COUNT(*) AS number_of_issue,
            SUM(m_issue.amount) AS total_amount
        FROM
            magazine_issue_transaction_table AS m_issue
        INNER JOIN
            magazine_transaction_table AS m_trans
        ON
            m_issue.magazine_trans_id = m_trans.Id
        INNER JOIN
            booking_sales_table AS book_trans
        ON
            m_trans.transaction_id = book_trans.Id
        ". $query ."
        GROUP BY
            book_trans.Id, book_trans.trans_num, book_trans.sales_rep_code, book_trans.client_id, book_trans.agency_id, book_trans.status, book_trans.updated_at, book_trans.created_at, book_trans.group_id;

        ");

        $total_data = count($data);

        if($total_data <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        return array(
            "Status" => 200,
            "Message" => "Success.",
            "Count" => $total_data,
            "Data" => $data
        );
    }

    public static function get_booking_digital_report($trans = null) {

        KPAHelper::set_access_control_allow_origin();

        $query = "";
        if($trans != null) {
            $query = "WHERE book_trans.Id = '{$trans}' OR book_trans.trans_num = '{$trans}'";
        }

        $data = DB::select("
        SELECT
            book_trans.*,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM user_account AS a
                WHERE Id = book_trans.sales_rep_code
            ) AS sales_rep_name,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM client_contacts_table AS a
                INNER JOIN client_table AS b
                ON a.client_id = b.Id
                WHERE a.Id = book_trans.client_id
                AND b.type = 1
            ) AS client_name,
            (
                SELECT CONCAT(a.first_name, ' ', a.middle_name, ' ', a.last_name)
                FROM client_contacts_table AS a
                INNER JOIN client_table AS b
                ON a.client_id = b.Id
                WHERE a.Id = book_trans.agency_id
                AND b.type = 2
            ) AS agency_name,
            COUNT(*) AS number_of_issue,
            SUM(m_issue.amount) AS total_amount
        FROM
            magazine_digital_transaction_table AS m_issue
        INNER JOIN
            magazine_transaction_table AS m_trans
        ON
            m_issue.magazine_trans_id = m_trans.Id
        INNER JOIN
            booking_sales_table AS book_trans
        ON
            m_trans.transaction_id = book_trans.Id
        ". $query ."
        GROUP BY
            book_trans.Id, book_trans.trans_num, book_trans.sales_rep_code, book_trans.client_id, book_trans.agency_id, book_trans.status, book_trans.updated_at, book_trans.created_at;

        ");

        $total_data = count($data);

        if($total_data <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        return array(
            "Status" => 200,
            "Message" => "Success.",
            "Count" => $total_data,
            "Data" => $data
        );
    }

    public static function get_issue_transactions($trans_uid, $proposal_uid = null) {

        KPAHelper::set_access_control_allow_origin();

        $t_uid = (int)$trans_uid;

        $data_1 = DB::select("
                SELECT
                    t.*,
                    m.Id AS mid,
                    m.logo_uid,
                    m.company_id,
                    m.mag_code,
                    m.magazine_name,
                    m.magazine_year,
                    m.magazine_country,
                        CASE WHEN m.magazine_country = 1 THEN 'USA'
                        WHEN m.magazine_country = 2 THEN 'CANADA'
                        ELSE 'PHILIPPINES' END AS mag_country
                FROM magazine_transaction_table AS t
                INNER JOIN magazine_table AS m
                ON t.magazine_id = m.Id
                WHERE t.transaction_id = {$t_uid}
            ");

        $total_data = count($data_1);

        if($total_data <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        $mag_id = $data_1[0]->Id;

        $proposal_uid_query = null;
        if($proposal_uid != null) {
            if($proposal_uid != "all") {
                $proposal_uid_query = "AND issue.quarter_issued = " . (int)$proposal_uid;
            }
        }

        $data_2 =  DB::select("
                    SELECT issue.*,
                    (
                        SELECT name FROM price_criteria_table WHERE Id = issue.ad_criteria_id AND status = 2
                    ) AS ad_color,
                    (
                        SELECT package_name FROM price_package_table WHERE Id = issue.ad_package_id  AND status = 2
                    ) AS ad_size,
                    (
                        issue.amount * issue.line_item_qty
                    ) AS sub_total_amount,
                    (
                        CASE
                            WHEN issue.line_item_qty > (
                                                        SELECT COUNT(*) AS t_type FROM
                                                            magzine_discount_table
                                                            WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                                        ) THEN

                                (
                                    SELECT percent AS Discount
                                        FROM magzine_discount_table
                                        WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                        AND type = (
                                    SELECT (COUNT(*) + 1) AS t_type
                                        FROM magzine_discount_table
                                        WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                    )
                                ) * 100

                            WHEN issue.line_item_qty = 1 THEN
                                0
                            ELSE
                                (
                                    SELECT percent AS Discount
                                        FROM magzine_discount_table
                                    WHERE mag_price_id =
                                    (
                                        SELECT Id AS mag_price FROM magzine_price_table WHERE mag_id =
                                        (
                                            SELECT magazine_id FROM magazine_transaction_table WHERE Id = issue.magazine_trans_id
                                        )
                                     )
                                    AND percent != 0
                                    AND type = issue.line_item_qty
                                ) * 100
                        END
                    ) AS total_discount_by_percent,
                    (
                        CASE
                            WHEN issue.line_item_qty > (
                                                            SELECT COUNT(*) AS t_type FROM
                                                            magzine_discount_table
                                                            WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                                        ) THEN

                                (issue.amount * issue.line_item_qty) -
                                ((issue.amount * issue.line_item_qty) * (
                                    SELECT percent AS Discount
                                        FROM magzine_discount_table
                                        WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                        AND type = (
                                    SELECT (COUNT(*) + 1) AS t_type
                                        FROM magzine_discount_table
                                        WHERE mag_price_id = issue.mag_price_id AND percent != 0
                                    )
                                ))

                            WHEN issue.line_item_qty = 1 THEN
                                (issue.amount * issue.line_item_qty) - ((issue.amount * issue.line_item_qty) * 0)
                            ELSE
                                (issue.amount * issue.line_item_qty) -
                                ((issue.amount * issue.line_item_qty) * (
                                    SELECT percent AS Discount
                                        FROM magzine_discount_table
                                    WHERE mag_price_id =
                                     (
                                        SELECT Id AS mag_price FROM magzine_price_table WHERE mag_id =
                                        (
                                            SELECT magazine_id FROM magazine_transaction_table WHERE Id = issue.magazine_trans_id
                                        )
                                     )
                                    AND percent != 0
                                    AND type = issue.line_item_qty
                                ))
                        END
                    ) AS total_amount_with_discount

                    FROM magazine_issue_transaction_table AS issue

                    WHERE issue.magazine_trans_id = {$mag_id} {$proposal_uid_query} AND issue.status != 1

                    GROUP BY issue.id, issue.magazine_trans_id, issue.ad_criteria_id, issue.ad_package_id, issue.quarter_issued, issue.line_item_qty, issue.mag_price_id, issue.amount, issue.status, issue.updated_at, issue.created_at;
            ");

        $total_data = count($data_2);

        if($total_data <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        $mag_company = KPAHelper::get_magazine_company_information($data_1[0]->company_id);

        $total_discount = KPAHelper::get_total_discount_amount($data_1[0]->Id);

        $total_issue_discount = KPAHelper::get_total_issue_discount_v2($data_1[0]->Id, $data_2);

        $view_trans = array(
            "Status" => 200,
            "Message" => "Success.",
            "Count" => $total_data,
            "Id" => $data_1[0]->Id,
            "Transaction_id" => $data_1[0]->transaction_id,
            "logo_uid" => $data_1[0]->logo_uid,
            "Mag_Uid" => $data_1[0]->mid,
            "Mag_Code" => $data_1[0]->mag_code,
            "Magazine_Year" => $data_1[0]->magazine_year,
            "Magazine_Name" => $data_1[0]->magazine_name,
            "Mag_Country" => $data_1[0]->mag_country,
            "Mag_Country_Code" => $data_1[0]->magazine_country,
            "Modified" => $data_1[0]->updated_at,
            "Created" => $data_1[0]->created_at,
            "Discounted_Amount" => $total_discount,
            "Company_Information" => $mag_company,
            "Issue_Discounts" => $total_issue_discount,
            "Data" => $data_2
        );

        return $view_trans;
    }

    public static function get_digital_transactions($trans_uid, $month_id  = 0, $week_id = 0) {

        KPAHelper::set_access_control_allow_origin();

        $t_uid = (int)$trans_uid;

        $data_1 = DB::select("
                SELECT
                    t.*,
                    m.Id AS mid,
                    m.logo_uid,
                    m.company_id,
                    m.mag_code,
                    m.magazine_name,
                    m.magazine_year,
                    m.magazine_country,
                        CASE WHEN m.magazine_country = 1 THEN 'USA'
                        WHEN m.magazine_country = 2 THEN 'CANADA'
                        ELSE 'PHILIPPINES' END AS mag_country
                FROM magazine_transaction_table AS t
                INNER JOIN magazine_table AS m
                ON t.magazine_id = m.Id
                WHERE t.transaction_id = {$t_uid}
            ");

        $total_data = count($data_1);

        if($total_data <= 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        $mag_id = $data_1[0]->Id;

        $proposal_uid_query = null;
        if($month_id != 0) {
            if($week_id != 0) {
                $proposal_uid_query = "AND issue.month_id = {$month_id} AND issue.week_id = {$week_id}";
            }
            else {
                $proposal_uid_query = "AND issue.month_id = {$month_id}";
            }
        }

        $data_2 =  DB::select("
                    SELECT

                        issue.*,

                        ( SELECT magazine_name FROM magazine_table WHERE Id = issue.magazine_id ) AS mag_name,

                        ( SELECT CONCAT(ad_type, ' - ', ad_size) FROM magzine_digital_price_table WHERE Id = issue.position_id AND ad_status = 2 ) AS ad_size,

                        ( SELECT
                               ( SUM(discount_percent) / 100 )
                            FROM magazine_digital_discount_transaction_table
                            WHERE item_id = issue.Id AND status = 1
                        ) AS dollar_discount

                    FROM magazine_digital_transaction_table AS issue

                    WHERE issue.magazine_trans_id = {$mag_id} AND issue.status != 1 {$proposal_uid_query}


            ");

        $total_data = count($data_2);

        if($total_data == 0) {
            return array(
                "Status" => 404,
                "Message" => "No Data Record.",
                "Count" => $total_data,
                "Data" => []
            );
        }

        $mag_company = KPAHelper::get_magazine_company_information($data_1[0]->company_id);

        $total_discount = KPAHelper::get_total_discount_amount($data_1[0]->Id);

        $total_issue_discount = KPAHelper::get_total_digital_discount($data_1[0]->Id);

        $booking_details = DB::select("SELECT * FROM booking_sales_table WHERE Id = {$t_uid};");

        $view_trans = array(
            "Status" => 200,
            "Message" => "Success.",
            "Count" => $total_data,
            "Id" => $data_1[0]->Id,
            "Transaction_id" => $data_1[0]->transaction_id,
            "logo_uid" => $data_1[0]->logo_uid,
            "Mag_Uid" => $data_1[0]->mid,
            "Mag_Code" => $data_1[0]->mag_code,
            "Magazine_Year" => $data_1[0]->magazine_year,
            "Magazine_Name" => $data_1[0]->magazine_name,
            "Mag_Country" => $data_1[0]->mag_country,
            "Mag_Country_Code" => $data_1[0]->magazine_country,
            "Modified" => $data_1[0]->updated_at,
            "Created" => $data_1[0]->created_at,
            "Discounted_Amount" => $total_discount,
            "Company_Information" => $mag_company,
            "Issue_Discounts" => $total_issue_discount,
            "Data" => $data_2,
            "Bookings" => $booking_details
        );

        return $view_trans;
    }

    /*
        get price by passing boolean value

        digits = total length
    */

    public static function get_new_uid($digits = 16){
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return array(
            "new_uid" => (int)$pin
        );
    }

    public static function get_new_contract() {
        do {

            $n_uid = array("id" => date("Ymdms") ."". strtoupper(uniqid()));

            $data = DB::select("SELECT * FROM contract_table WHERE contract_num = '{$n_uid["id"]}';");

        }while( count($data) > 0 );

        return $n_uid;
    }

    public static function get_new_reference() {
        $n_uid = array("number" => date("Ymdms") ."". strtoupper(uniqid()));
        return $n_uid;
    }

    public static function get_random_password($value = null) {
        $random = mt_rand(10, 727379969);
        if($value == null) {
            $value = $random;
        }
        $result = md5('@BC12abc' . $value);
        return array("new_password" => $value, "hash_password" => $result);
    }

    public static function get_new_password($value = null) {
        $password = $value;
        if($value == null) {
            $password = "123456";
        }
        return array(
            "Password" => $password,
            "Hash" => md5("ABC12abc" . $password)
        );
    }

    public static function get_current_time_stamp() {
        $date_now = Carbon::now();
        $date_time_stamp = $date_now->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        return strtotime($date_time_stamp);
    }

    public static function do_parse_time_stamp($value) {
        $date_time_stamp = Carbon::createFromTimeStamp($value);
        //format('Y-m-d H:i:s')
        return $date_time_stamp;
    }

    public static function do_hash_value($value, $type, $timestamp = 0) {

        $append_string = "";

        $hash_key = "ABC12abc:";

        $hash_value = $value;

        $hash_time_stamp = null;

        if($type == "EN" || $type == "en") {

            $append_string = "Encrypted";

            $hash_value = $hash_key . $value;

            $hash_time_stamp = KPAHelper::get_current_time_stamp();
            if($timestamp > 0) {
                $hash_time_stamp = $timestamp;
            }

            $hash_value = encrypt($hash_value . $hash_time_stamp);
        }
        else if($type == "DE" || $type == "de") {
            $append_string = "Decrypted";

            $hash_value = decrypt($hash_value);

            $hash_split = explode(':', $hash_value);

            if($hash_split[0] .':' != $hash_key) {
                $hash_value = "Oops, hash key value.";
            }
            else {
                $hash_value = $hash_split[1];

                if (strpos($hash_value, '1480234107') !== false) {
                    $hash_time_stamp = 1480234107;
                    $hash_value = str_replace('1480234107', '', $hash_split[1]);

                    $hash_time_stamp = KPAHelper::do_parse_time_stamp(1480234107)->diffForHumans();
                }
                else {
                    $hash_time_stamp = 0;
                    $hash_value = "Oops, invalid time stamp.";
                }
            }
        }
        else {
            $hash_value = "Oops, invalid params.";
        }

        return [
            "Origin" => $value,
            "Value_". $append_string => $hash_value,
            "Time_Stamp" => $hash_time_stamp
        ];
    }

    /*
       check if manage client existing to un-manage or has 2 agents

       agentCode = pass agent code if logged in or selected

       clientCode = pass client code you want to check

       return function is boolean
   */

    public static function check_if_already_grad($agentCode, $clientCode) {

        $sql = "
            SELECT *
            FROM agency_transaction_table
            WHERE agency_code = '{$agentCode}'
            AND client_code = '{$clientCode}'
        ";

        $data = DB::select($sql);

        if( count($data) > 0 ) {
            return true;
        }
        return false;

    }

}
