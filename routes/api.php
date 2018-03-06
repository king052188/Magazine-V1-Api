<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/kpa/work/booking-sales-report/{value?}', 'BookingSalesClass@report');

Route::get('/kpa/work/magazine-issue-lists/{id}', 'BookingSalesClass@issue_report');

Route::get('/kpa/work/contract/generate/pdf', 'ContractClass@generate_contract_pdf');
