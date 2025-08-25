<?php

use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search', SearchController::class);


use App\Http\Controllers\Api\BenefitsController;

Route::get('/benefits', BenefitsController::class);