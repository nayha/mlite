<?php
defined("APP_DESC") OR exit('No direct script access allowed');

use Medoo\Medoo;

$database = new Medoo([
    "database_type" => "mysql",
    "database_name" => DB_NAME,
    "server"        => DB_HOST,
    "username"      => DB_USER,
    "password"      => DB_PASSW,
]);