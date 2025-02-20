<?php

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DataController::class, 'index'])->name('home');
Route::get('/users', [DataController::class, 'getAllUser'])->name('get.all.users');
Route::post('/users/create', [DataController::class, 'createUser'])->name('create.user');
Route::put('/users/{id}', [DataController::class, 'updateUser'])->name('update.user');
Route::delete('/users/{id}', [DataController::class, 'deleteUser'])->name('delete.user');
