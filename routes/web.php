<?php

use App\Http\Controllers\InvoicesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('{student}/invoice/generate', [InvoicesController::class, 'generatePdf'])->name('student.generate.invoice');
