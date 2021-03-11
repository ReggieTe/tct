<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForcastController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',[ForcastController::class , 'index']);
Route::get('/forcasts',[ForcastController::class , 'index']);
Route::prefix('/forcast')->group(function(){
    Route::get('/store',[ForcastController::class,'store']);
});