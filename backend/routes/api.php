<?php

use App\Http\Controllers\Api\KanbanController;
use Illuminate\Support\Facades\Route;

Route::get('/boards', [KanbanController::class, 'boards']);
Route::post('/boards', [KanbanController::class, 'storeBoard']);
Route::get('/boards/{board}', [KanbanController::class, 'showBoard']);
Route::put('/boards/{board}', [KanbanController::class, 'updateBoard']);
Route::delete('/boards/{board}', [KanbanController::class, 'destroyBoard']);

Route::post('/lists', [KanbanController::class, 'storeList']);
Route::put('/lists/{list}', [KanbanController::class, 'updateList']);
Route::delete('/lists/{list}', [KanbanController::class, 'destroyList']);

Route::get('/cards/{card}', [KanbanController::class, 'showCard']);
Route::post('/cards', [KanbanController::class, 'storeCard']);
Route::put('/cards/{card}', [KanbanController::class, 'updateCard']);
Route::delete('/cards/{card}', [KanbanController::class, 'destroyCard']);
Route::patch('/cards/{card}/move', [KanbanController::class, 'moveCard']);

Route::get('/tags', [KanbanController::class, 'tags']);
Route::post('/tags', [KanbanController::class, 'storeTag']);
Route::post('/cards/{card}/tags', [KanbanController::class, 'assignTag']);
Route::delete('/cards/{card}/tags/{tag}', [KanbanController::class, 'removeTag']);

Route::get('/members', [KanbanController::class, 'members']);
Route::post('/members', [KanbanController::class, 'storeMember']);
Route::post('/cards/{card}/members', [KanbanController::class, 'assignMember']);
Route::delete('/cards/{card}/members/{member}', [KanbanController::class, 'removeMember']);