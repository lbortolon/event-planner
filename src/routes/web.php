<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Per il frontend React "qualunque URL arrivi, servi sempre la stessa pagina HTML". Poi React Router prende il controllo e mostra il componente giusto.
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');