<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/siswa', function () {
    $json = Storage::get('siswa_full.json');
    return response()->json(json_decode($json, true));
});
