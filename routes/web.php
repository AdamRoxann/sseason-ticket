<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\TicketWarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TicketWarController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/ticket-war', [TicketWarController::class, 'show'])->name('ticket-war');
    Route::get('/scan', [TicketWarController::class, 'scanPage'])->name('scan.index');
    Route::post('/scan/check-in', [TicketWarController::class, 'checkIn'])->middleware('throttle:60,1')->name('scan.check-in');
    Route::post('/waiting-room/enter', [TicketWarController::class, 'enter'])->middleware('throttle:3,1')->name('waiting-room.enter');
    Route::get('/waiting-room/{entry}', [TicketWarController::class, 'waitingRoom'])->name('waiting-room.show');
    Route::get('/waiting-room/{entry}/checkout', [TicketWarController::class, 'checkoutForm'])->name('waiting-room.checkout-form');
    Route::post('/waiting-room/{entry}/checkout', [TicketWarController::class, 'checkout'])->middleware('throttle:5,1')->name('waiting-room.checkout');
    Route::get('/my-tickets', [TicketWarController::class, 'myTickets'])->name('tickets.index');
    Route::get('/my-tickets/{ticket}', [TicketWarController::class, 'ticket'])->name('tickets.show');
});
