<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/user',function(){
    return"Bienvenue";
});
Route::redirect('/','/user');



