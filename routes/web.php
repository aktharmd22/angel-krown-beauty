<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Alternate cinematic home — scroll-scrubbed background video
Route::get('/home2', function () {
    return Inertia::render('Home2');
})->name('home2');

// Duplicate of the original home
Route::get('/home3', function () {
    return Inertia::render('Home3');
})->name('home3');

// Invoice PDF (auth checked inside the controller)
Route::get('/admin/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
    ->name('invoices.pdf');
