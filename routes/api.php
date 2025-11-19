<?php

use App\Http\Controllers\Superadmin\AttandanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/kasbon/employee/{id}', function($id) {
//     $kasbon = \App\Models\CashAdvance::where('employee_id', $id)
//         ->where('status', 'ongoing')
//         ->orderBy('start_month')
//         ->get();

//     return response()->json([
//         'employee' => optional($kasbon->first()->employee)->first_name . ' ' . optional($kasbon->first()->employee)->last_name,
//         'kasbon' => $kasbon
//     ]);
// });

// Route::get('/attandance/state', [AttandanceController::class, 'getAttendanceState'])
//             ->name('attandance.getState')
//             ->middleware('permission:attandance.scan');
