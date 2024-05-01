<?php
header('Content-Type: application/json');
$server_method = $_SERVER['REQUEST_METHOD'];
if($server_method == 'GET'){
    $response = [
        "status" => 200,
        "message" => "Successfuly Validated"
    ];
}else{
    $response = [
        "status" => 405,
        "message" => "$server_method not allowed"
    ];
}

echo json_encode($response);