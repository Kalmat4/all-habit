<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DependencyController;
use App\Http\Controllers\TrackerController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('tracker.index'));
    Route::get('/tracker', [TrackerController::class, 'index'])->name('tracker.index');
    Route::get('/tracker/impulses', [TrackerController::class, 'impulses'])->name('impulses.list');
    Route::post('/tracker/impulses', [TrackerController::class, 'store'])->name('impulses.store');
    Route::delete('/tracker/impulses/{impulse}', [TrackerController::class, 'destroy'])->name('impulses.destroy');

    Route::post('/tracker/dependencies', [DependencyController::class, 'store'])->name('dependencies.store');
    Route::delete('/tracker/dependencies/{dependency}', [DependencyController::class, 'destroy'])->name('dependencies.destroy');
});