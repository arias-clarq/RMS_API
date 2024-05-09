<?php
include '../db_conn.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');

$server_method = $_SERVER['REQUEST_METHOD'];

if ($server_method == 'PUT') {
    $putData = file_get_contents('php://input');
    $data = json_decode($putData, true);

    if (!isset($data['recipe_id'])) {
        return error422('Recipe id not found');
    }
    
    if (!isset($data['favorite'])) {
        return error422('favorite not found');
    }

    $recipe_id = $conn->real_escape_string($data['recipe_id']);
    $favorite = $conn->real_escape_string($data['favorite']);

    $sql = "UPDATE `tbl_recipes` SET `favorite` = '$favorite' WHERE `recipe_id` = $recipe_id";
    $result = $conn->query($sql);

    if ($result !== true) {
        return error422('Failed to update recipe');
    }else{
        $response = [
            'status' => 200,
            'message' => 'Recipe Updated Successfully',
        ];
    }
    echo json_encode($response);
}