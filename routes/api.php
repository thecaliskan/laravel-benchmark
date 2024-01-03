<?php

use App\Services\HttpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

Route::get('/health-check', fn (Request $request) => Response::noContent())->name('health-check');
Route::get('/static', fn (Request $request) => Response::json(['status' => true]))->name('static');
Route::get('/http-request', fn (Request $request, HttpService $httpService) => Response::json(json_decode($httpService->get('http://whoami/api'))))->name('http-request');
