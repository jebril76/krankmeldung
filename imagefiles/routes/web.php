<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Mail;

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

Route::get('/m', function () { return view('meldung'); });
Route::get('/l', function () { return view('lehrer'); });
Route::get('/i', function () { return view('infoscreen'); });
Route::get('/c', [ReportController::class,'v_config'])->name('v_config');

Route::post('/m_search_sus',   [StudentController::class,'m_search_sus'])->name('m_search_sus');
Route::post('/c_trash_sus',    [StudentController::class,'c_trash_sus'])->name('c_trash_sus');
Route::post('/c_import_sus1',  [StudentController::class,'c_import_sus1'])->name('c_import_sus1');
Route::post('/c_import_sus2',  [StudentController::class,'c_import_sus2'])->name('c_import_sus2');

Route::post('/m_reported_sus', [ReportController::class,'m_reported_sus'])->name('m_reported_sus');
Route::post('/m_create_rep',   [ReportController::class,'m_create_rep'])->name('m_create_rep');
Route::post('/m_edit_days',    [ReportController::class,'m_edit_days'])->name('m_edit_days');
Route::post('/m_update_com',   [ReportController::class,'m_update_com'])->name('m_update_com');
Route::post('/m_destroy_rep',  [ReportController::class,'m_destroy_rep'])->name('m_destroy_rep');
Route::post('/c_restore_rep',  [ReportController::class,'c_restore_rep'])->name('c_restore_rep');
Route::post('/c_backup_rep',   [ReportController::class,'c_backup_rep'])->name('c_backup_rep');
Route::post('/c_trash_rep',    [ReportController::class,'c_trash_rep'])->name('c_trash_rep');
Route::post('/c_trash_all',    [ReportController::class,'c_trash_all'])->name('c_trash_all');
Route::post('/l_show_rep',     [ReportController::class,'l_show_rep'])->name('l_show_rep');

