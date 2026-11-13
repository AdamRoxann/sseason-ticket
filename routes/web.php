<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\TicketWarController;
use App\Http\Controllers\Admin\AdminController;
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
    Route::post('/waiting-room/enter', [TicketWarController::class, 'enter'])->name('waiting-room.enter');
    Route::get('/waiting-room/{entry}', [TicketWarController::class, 'waitingRoom'])->name('waiting-room.show');
    Route::get('/waiting-room/{entry}/checkout', [TicketWarController::class, 'checkoutForm'])->name('waiting-room.checkout-form');
    Route::post('/waiting-room/{entry}/checkout', [TicketWarController::class, 'checkout'])->middleware('throttle:5,1')->name('waiting-room.checkout');
    Route::get('/my-tickets', [TicketWarController::class, 'myTickets'])->name('tickets.index');
    Route::get('/my-tickets/{ticket}', [TicketWarController::class, 'ticket'])->name('tickets.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
    Route::get('/events', [AdminController::class, 'events'])->name('events');
    Route::get('/events/create', [AdminController::class, 'createEvent'])->name('events.create');
    Route::post('/events', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{event}/edit', [AdminController::class, 'editEvent'])->name('events.edit');
    Route::put('/events/{event}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::delete('/events/{event}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
    Route::post('/events/{event}/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/events/{event}/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/events/{event}/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/scan', [TicketWarController::class, 'scanPage'])->name('scan');
    Route::post('/scan/check-in', [TicketWarController::class, 'checkIn'])->middleware('throttle:120,1')->name('scan.check-in');
});
