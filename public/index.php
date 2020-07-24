<?php


// Initial Setup
$_ROOT = $_SERVER["DOCUMENT_ROOT"];


// Required includes
include("../requires/Log.php");
include("../requires/Utils.php");
include("../requires/Route.php");
include("../vendor/autoload.php");
include("../requires/Database.php");


// Setup
Utils::set_error_handler();
$_HOME = Utils::set_home(Utils::get_real_path("../"));


// Database Setup - Access Database using 'DB::$DB'
// DB::setup(new PDO("mysql:host=localhost;dbname=test", "username", "password"));


// Include routes header
include("../content/routes/routes.header.php");


// Run the Router
Route::run();
