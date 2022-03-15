<?php

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    include_once '../config/database.php';
    include_once '../entities/user.php';

    $database = new Database();
    $db = $database->getConnection();
    $items = new User($db);
    $stmt = $items->getUsers();
    $itemCount = $stmt->rowCount();

    if($itemCount > 0){
        
        $userArray = array();
        $userArray["payload"] = array();
        $userArray["userCount"] = $itemCount;

        $databaseResult = $stmt->fetchAll();
        foreach($databaseResult as $result){
            extract($result);
            $e = array(
                "id" => $id,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $email,
                "password" => $password,
                "age" => $age,
                "gender" => $gender,
                "created_at" => $created_at,
                "updated_at" => $updated_at
            );
            array_push($userArray["payload"], $e);
        }
        echo json_encode($userArray);
    }
    else{
        http_response_code(200);
        echo json_encode(
            array("message" => "No record found.")
        );
    }
?>