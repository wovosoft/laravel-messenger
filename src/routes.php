<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

//MAPPING_AREA_FOR_CRUD_DO_NOT_REMOVE_OR_EDIT_THIS_LINE_USE_AREA//


Route::name('Wovosoft.')
    ->prefix('backend')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Wovosoft\LaravelMessenger\Http\Controllers\MessagesController::routes();
        //MAPPING_AREA_FOR_CRUD_DO_NOT_REMOVE_OR_EDIT_THIS_LINE//
    });
Broadcast::channel('my-channel', function ($user) {
    return Auth::check();
});
