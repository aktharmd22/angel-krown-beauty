<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Invoice PDF (auth checked inside the controller)
Route::get('/admin/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
    ->name('invoices.pdf');
