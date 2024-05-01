<?php
include '../db_conn.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET');

$server_method = $_SERVER['REQUEST_METHOD'];

if ($server_method == 'GET') {
    $sql = "SELECT * FROM `tbl_categories`";
    $result = $conn->query($sql);
    $category_list = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($category_list);
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method not allowed',
    ];
    header('HTTP/1.0 405 Method not allowed');
    echo json_encode($data);
}
