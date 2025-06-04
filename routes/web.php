<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AreaController;
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

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function () {
    return view('mapbox');
});


Route::get('/mapbox3d', function () {
    return view('mapbox3d');
});

Route::get('/mapboxdraw', function () {
    return view('mapboxdraw');
});

Route::post('/saveLocation', [LocationController::class, 'store']);
Route::post('/areas', [AreaController::class, 'store']);