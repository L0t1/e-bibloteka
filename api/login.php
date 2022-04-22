<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once '../config/database.php';
include_once '../entities/user.php';
    $db = new Database();
    $db = $db->getConnection();
    $user = new User($db);
    $data = json_decode(file_get_contents("php://input"));

    print_r("data email");
    $user->login($data->email, $data->password);
?>