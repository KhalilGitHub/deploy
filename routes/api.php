<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/person', function() {
    $person = [
        "First_Name" => "Khalil",
        "Last_Name" => "Hisseine",
        "Message" => "It is workinggit commit -am ",
    ];
    return $person;
});