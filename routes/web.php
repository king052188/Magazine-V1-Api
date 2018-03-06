<?php

Route::get('/', function () {
    return view('welcome');
});


Route::any('/kpa/work-v2/flat-plan/create-channel', 'FlatPlanController@create_channel');

Route::any('/kpa/work-v2/flat-plan/save', 'FlatPlanController@insert_order');

Route::any('/kpa/work-v2/flat-plan/go', 'FlatPlanController@update_order');

Route::any('/kpa/work-v2/flat-plan/get-placeholder', 'FlatPlanController@placeholder');

Route::any('/kpa/work-v2/flat-plan/del/{id}', 'FlatPlanController@remove_order');

Route::get('/kpa/work-v2/get-flat-plan/{flat_id}', 'FlatPlanController@populates');

Route::get('/kpa/work-v2/flat-plan/{magazine_id}/{reference?}', 'FlatPlanController@init');



//

Route::any('/kpa/work-v2/flat-planning/populate/publication', 'FlatPlanController@populate_publication');

//
// populate



// API FOR OFFLINE MODE / SYNCHER

Route::get('/kpa/work/get-clients-upload/{uid}/{type}', 'ContractClass@get_root_folder');

Route::get('/kpa/work/get-clients-info', 'ContactClass@get_client_info');

Route::get('/kpa/work/update-client-sync/{contact_id?}', 'ContactClass@update_client_sync');

// insertion order to pdf

Route::get('/kpa/work/scanned-contract/{trans}', 'ImageController@get_contract_cloud');

Route::get('/kpa/work/transaction/generate/pdf/{trans_num}/{download?}', 'ContractClass@generate_contract_pdf');

Route::get('/kpa/work/transaction/generate/insertion-order-contract/{trans_num}/{type?}', 'ContractClass@init_insertion_order');

Route::get('/kpa/work/generate/insertion-order/{trans_num}', 'InsertionClass@Init');

Route::get('/kpa/work/generate/insertion-order-pdf/{trans_num}', 'InsertionClass@Generate_PDF');

Route::get('/kpa/work/generate/insertion-digital-pdf/{trans_num}', 'insertionDigitalClass@Generate_PDF');

//

Route::get('/kpa/work/generate/insertion-digital-order/{trans_num}', 'insertionDigitalClass@Init');

// invoice to pdf

//Route::get('/kpa/work/transaction/invoice-order/{invoice}/{proposal?}/{paid?}', 'InvoiceController@init_invoice_order');

Route::get('/kpa/work/transaction/invoice-order/{invoice}/{is_digital?}/{proposal?}/{paid?}', 'InvoiceV2Class@init_invoice_order');

Route::get('/kpa/work/transaction/invoice-order-v2/{invoice}/{proposal?}/{paid?}', 'InvoiceV2Class@init_invoice_order');

Route::get('/kpa/work/transaction/generate/invoice-order/{invoice}', 'InvoiceController@do_invoice');

Route::get('/kpa/work/transaction/generate/invoice-order-v2/{invoice}', 'InvoiceV2Class@do_invoice');

Route::get('/kpa/work/transaction/generate/invoice-order-v2/download/{invoice}', 'InvoiceV2Class@Generate_PDF');

Route::get('/kpa/work/transaction/generate/invoice-digital-order/{invoice}', 'InvoiceDigitalController@do_invoice');

Route::get('/kpa/work/transaction/generate/invoice-digital-order/download/{invoice}', 'InvoiceDigitalController@Generate_PDF');


// API Invoice

Route::get('/kpa/work/invoice-transaction-list/{invoice}/{proposal?}', 'ContractClass@get_invoice_transaction');

// sales reports

Route::get('/kpa/work/booking-sales-report/{value?}', 'BookingSalesClass@report');

Route::get('/kpa/work/magazine-issue-lists/{id}', 'BookingSalesClass@issue_report');

Route::get('/kpa/work/magazine-digital-lists/{id}/{month?}/{week?}', 'BookingSalesClass@digital_report');

Route::get('/kpa/work/magazine-digital-discount-item/{id}', 'BookingSalesClass@digital_item_discount');

//

Route::get('/kpa/work/booking/report/print/{trans_id}/{issue}/{sales?}', 'ReportController@issue_report');

Route::get('/kpa/work/booking/report/digital/{trans_id}/{issue}', 'ReportController@issue_digital_report');


Route::get('/kpa/work/goal/report/{mag_id}/{issue}/{year}/{sales}', 'ReportController@get_goal_amount');

// Notifications

Route::get('/kpa/work/notification-list/{role}', 'NotificationClass@get_all_notification');

Route::get('/kpa/work/notification-read/{id}', 'NotificationClass@read_notification');

// image resizer

Route::get('/kpa/work/image/manager', 'ImageController@resize_image_logo');

Route::get('/kpa/work/send/email/{bill}/{trans}/{subject}', 'NotificationClass@sendBookingInvoice');

Route::get('/kpa/work/password/{value?}', function($value = null){

    $password = $value;
    if($value == null) {
        $password = "123456";
    }
    return array(
      "Password" => $password,
      "Hash" => md5("ABC12abc" . $password)
    );

});

//Route::get('/kpa/work/{type}/{value}', function($type, $value){
//    $data = \App\Http\Controllers\KPAHelper::do_hash_value($value, $type);
//    return $data;
//});
//
//Route::get('/kpa/work/kpa-class-js', function(\Illuminate\Http\Request $request){
//    $user_info = $request["user"];
//    $data = \App\Http\Controllers\KPAHelper::do_hash_value($user_info, "EN");
//    return $data;
//});

///

Route::get('/kpa/work/send-bulk-email-for-invoice/{type?}/{email?}', 'BulkEmailController@send_invoices');
