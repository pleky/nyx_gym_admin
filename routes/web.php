<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// owner route
Route::middleware(['auth', 'active', 'role:OWNER'])->group(function () {
   Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
   Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
   Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
});

// staff route
Route::middleware(['auth','active', 'role:STAFF'])->group(function () {
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
