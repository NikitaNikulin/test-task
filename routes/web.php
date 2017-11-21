<?php

Route::get('balance', 'UsersController@getBalance');
Route::post('deposit', 'UsersController@deposit');
Route::post('withdraw', 'UsersController@withdraw');
Route::post('transfer', 'UsersController@transfer');
