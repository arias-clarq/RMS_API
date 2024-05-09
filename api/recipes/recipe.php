<?php
include '../db_conn.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, PUT, DELETE, POST');

$server_method = $_SERVER['REQUEST_METHOD'];

if ($server_method == 'GET') {

    $sql = "SELECT *, (CASE WHEN favorite = 1 THEN 1 ELSE 0 END) AS is_favorite 
    FROM `tbl_recipes` 
    INNER JOIN tbl_categories ON tbl_recipes.category_id = tbl_categories.category_id 
    ORDER BY is_favorite DESC";
    $result = $conn->query($sql);
    
    // Initialize an empty array to hold the hierarchical data
    $recipe_lists = array();

    // Loop through the query result
    while ($row = $result->fetch_assoc()) {
        // Create a new recipe object
        $recipe = array(
            'recipe_id' => $row['recipe_id'],
            'recipe_name' => $row['name'],
            'description' => $row['description'],
            'instructions' => $row['instructions'],
            'ingredients' => $row['ingredients'],
            'favorite' => $row['favorite'],
            'image' => $row['image_url']
        );

        // Add the category data to the recipe object
        $recipe['category'] = array(
            'category_id' => $row['category_id'],
            'category_name' => $row['cat_name']
        );

        // Push the recipe object to the recipe_lists array
        $recipe_lists[] = $recipe;
    }

    // Encode the hierarchical data to JSON
    echo json_encode($recipe_lists);

} 

else if ($server_method == 'POST') {

    // Check if required fields are present
    if (!isset($_POST['recipe_name'])) {
        return error422('recipe_name not found in the request');
    }
    if (!isset($_POST['description'])) {
        return error422('description not found in the request');
    }
    if (!isset($_POST['ingredients'])) {
        return error422('ingredients not found in the request');
    }
    if (!isset($_POST['instructions'])) {
        return error422('instructions not found in the request');
    }
    if (!isset($_POST['category_id'])) {
        return error422('category_id not found in the request');
    }

    // Get form data
    $recipe_name = $conn->real_escape_string($_POST['recipe_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $ingredients = $conn->real_escape_string($_POST['ingredients']);
    $instructions = $conn->real_escape_string($_POST['instructions']);
    $category_id = $conn->real_escape_string($_POST['category_id']);

    // Check if image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES["image"]["tmp_name"];
        $name = basename($_FILES["image"]["name"]);
        move_uploaded_file($tmp_name, "images/$name");

        // Your database insert code here including the image file name
        $sql = "INSERT INTO `tbl_recipes`(`name`, `description`, `ingredients`, `instructions`, `category_id`, `favorite`, `image_url`) 
                VALUES ('$recipe_name','$description','$ingredients','$instructions','$category_id',0,'$name')";
        $result = $conn->query($sql);

        if ($result !== true) {
            return error422('Failed to add recipe');
        } else {
            $response = [
                'status' => 200,
                'message' => 'Recipe Added Successfully',
            ];
        }
    } else {
        return error422('Image upload failed');
    }
    echo json_encode($response);
}

else if ($server_method == 'PUT') {

    $putData = file_get_contents('php://input');
    $data = json_decode($putData, true);

    if (!isset($data['recipe_id'])) {
        return error422('Recipe id not found');
    }
    if (!isset($data['recipe_name'])) {
        return error422('recipe_name not found');
    }
    if (!isset($data['description'])) {
        return error422('description not found');
    }
    if (!isset($data['ingredients'])) {
        return error422('ingredients not found');
    }
    if (!isset($data['instructions'])) {
        return error422('instructions not found');
    }
    if (!isset($data['category_id'])) {
        return error422('category_id not found');
    }

    $recipe_id = $conn->real_escape_string($data['recipe_id']);
    $recipe_name = $conn->real_escape_string($data['recipe_name']);
    $description = $conn->real_escape_string($data['description']);
    $ingredients = $conn->real_escape_string($data['ingredients']);
    $instructions = $conn->real_escape_string($data['instructions']);
    $category_id = $conn->real_escape_string($data['category_id']);

    $sql = "UPDATE `tbl_recipes` 
    SET `name`='$recipe_name',
    `description`='$description',
    `ingredients`='$ingredients',
    `instructions`='$instructions',
    `category_id`='$category_id'
    WHERE `recipe_id` = $recipe_id";
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

else if ($server_method == 'DELETE') {

    if (!isset($_GET['recipe_id'])) {
        return error422('Recipe id not found in the URL');
    }

    $id = $_GET['recipe_id'];

    $sql = "DELETE FROM `tbl_recipes` WHERE `recipe_id` = '{$id}'";
    $result = $conn->query($sql);
    if ($result !== true) {
        return error422('Failed to delete recipe');
    }else{
        $response = [
            'status' => 200,
            'message' => 'Recipe Deleted Successfully',
        ];
    }
    echo json_encode($response);
}

function error422($message)
{
    $response = [
        'status' => 422,
        'message' => $message,
    ];
    header('HTTP/1.0 422 Invalid Entity');
    echo json_encode($response);
    exit();
}