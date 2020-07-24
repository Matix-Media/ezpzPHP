<?php

//* STRUCTURE YOUR ROUTES

Route::add("/", function () {
    Route::load_view("default.html", "home.php");
});

Route::add("/home", function () {
    Route::load_view("default.html", "home.php");
});

Route::add("/second", function () {
    Route::load_view("default.html", "test.php", "This is the second page.");
});

Route::add("/number/([0-9]*)/bar", function ($var1) {
    Route::load_view("default.html", "number.php", null, ["number" => $var1]);
});
