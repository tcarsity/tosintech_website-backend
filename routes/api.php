<?php


use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\front\ContactController;
use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\ProjectController;
use App\Http\Controllers\front\ProjectController as FrontProjectController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\TempImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/admin/login', [AuthController::class, 'authenticate']);



Route::post('authenticate', [AuthenticationController::class, 'authenticate']);
Route::get('get-projects', [FrontProjectController::class, 'index']);
Route::get('get-latest-projects', [FrontProjectController::class, 'latestProjects']);
Route::post('contact-now', [ContactController::class, 'index']);


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['middleware' => ['auth:sanctum']], function(){
   // Protected Routes

   //Dashboard Route
   Route::get('dashboard',[DashboardController::class, 'index']);
   Route::get('logout', [AuthController::class, 'logout']);

   Route::post('temp-images', [TempImageController::class, 'store']);

   //Project Routes
   Route::post('projects',[ProjectController::class, 'store']);
   Route::get('projects',[ProjectController::class, 'index']);
   Route::put('projects/{id}',[ProjectController::class, 'update']);
   Route::get('projects/{id}',[ProjectController::class, 'show']);
   Route::delete('projects/{id}',[ProjectController::class, 'destroy']);
});
