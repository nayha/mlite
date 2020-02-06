<?php

require_once "vendor/autoload.php";

define("APP_DESC", "MailerLite API");

require_once "config/constants.php";
require_once "config/database.php";

require_once "classes/fetch.php";
require_once "classes/subscriber.php";
require_once "classes/field.php";

header("Content-Type: application/json;charset=utf-8");

$requestType = filter_input(INPUT_GET, "type");
$requestMethod = filter_input(INPUT_SERVER, "REQUEST_METHOD");

switch ($requestType) {

    case "fields":
        
        $field = new Field;
        $fieldList = $field->getAll();
        
        exit(json_encode($fieldList));
        
        break;
    
    case "subscribers":
        
        $subscriber = new Subscriber;
        $subscriberList = $subscriber->getAll();
        
        exit(json_encode($subscriberList));
        
        break;
        
    case "subscriber-add":
        
        $inputData = json_decode(file_get_contents("php://input"), 1);
        
        $subscriber = new Subscriber;
        $response = $subscriber->add($inputData);
        
        exit(json_encode($response));

        break;

}

exit(json_encode(array(
    "error" => 1,
    "message" => "Invalid request"
)));
