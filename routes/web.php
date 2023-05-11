<?php

use App\Http\Controllers\AmountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/table', [AmountController::class, 'table']);
Route::get('/form', [AmountController::class, 'form']);
Route::get('/download', [AmountController::class, 'download']);
Route::get('/upload', [AmountController::class, 'upload']);