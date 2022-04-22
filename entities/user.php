<?php

use Firebase\JWT\JWT;
require "../vendor/autoload.php";

class User
{
    // Connection
    private $conn;
    // Table
    private $db_table = "users";
    // Columns
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $age;
    public $gender;
    public $created_at;
    public $updated_at;
    // Db connection
    public function __construct($db)
    {
        // accepts parameter that creates a connection and
        // assigns it to the $conn property
        $this->conn = $db;
    }


    // GET ALL Users
    public function getUsers()
    {

        // create a query to fetch data from the database
        $sqlQuery = "SELECT id, first_name, last_name, 
        email, password, age, gender, created_at, updated_at 
        FROM " . $this->db_table . "";

        // prepare the database connection 
        $stmt = $this->conn->prepare($sqlQuery);
        // execute the statement to the database
        $stmt->execute();
        // return whatever the execution of statement returns
        return $stmt;
    }

    // get user by id
    public function getUserById($id){
        $sqlQuery = "SELECT
                    id, 
                    first_name, 
                    last_name, 
                    email, 
                    password, 
                    age, 
                    gender, 
                    created_at, 
                    updated_at 
                  FROM
                    ". $this->db_table ."
                WHERE 
                   id = $id
                LIMIT 0,1";
        
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->first_name = $dataRow['first_name'];
        $this->last_name = $dataRow['last_name'];
        $this->email = $dataRow['email'];
        $this->password = $dataRow['password'];
        $this->age = $dataRow['age'];
        $this->gender = $dataRow['gender'];
    }        

    
    // CREATE
    public function registerUser()
    {
        $sqlQuery = "INSERT INTO
                                " . $this->db_table . "
                            SET
                                first_name = :first_name, 
                                last_name = :last_name, 
                                email = :email, 
                                password = :password, 
                                age = :age,
                                gender = :gender";

        $stmt = $this->conn->prepare($sqlQuery);

        // sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->age = htmlspecialchars(strip_tags($this->age));
        $this->gender = htmlspecialchars(strip_tags($this->gender));


        //hash the password before inserting into database
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // bind data
        // bindParam func accepts 2 arguments 1. the placeholder of query, 2. The real data that we want to add
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":age", $this->age);
        $stmt->bindParam(":gender", $this->gender);

        //$message = $stmt->execute();


        try{
            // this is the body of try
            $stmt->execute();
            return print_r(['status' => 'true', 'message' => 'User registered successfully!']);

        } catch(PDOException $error){
           $errorCode = json_encode($error->errorInfo[1]);
           // check if the error is duplicate
            if($errorCode == 1062){
                // if yes , tell the user that the email already exists
                http_response_code(400); // set the status code to 400, it is client error
                return print_r(['status' => 'false', 'message' => 'Email is already in use.']);
            } else {
                http_response_code(500);
                return print_r(['status' => 'false', 'message' => 'error!']);
            }
        }
    }

    


    public function login($email, $password){

        $findUserQuery =
        "SELECT * FROM ". $this->db_table ." WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($findUserQuery);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        $rowNumbers = $stmt->rowCount();
        
        // if the row number is bigger than 0 it means that a user with this email exists in our database
        if($rowNumbers > 0){

            // get the data from database from the associated row
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $dataRow['id'];
            $this->first_name = $dataRow['first_name'];
            $this->last_name = $dataRow['last_name'];
            $this->email = $dataRow['email'];
            $this->password = $dataRow['password'];
            $this->age = $dataRow['age'];
            $this->gender = $dataRow['gender'];

            if($this->password == $password){
                $e = array(
                    "id" => $this->id,
                    "first_name" => $this->first_name,
                    "last_name" => $this->last_name,
                    "email" => $this->email,
                    "age" => $this->age,
                    "gender" => $this->gender,
                    );


                // token stuff 
                    // token data
                 $secret_key = "tokenSecret";
                 $issuer_claim = "e-bibloteka"; // this can be the servername
                 $audience_claim = "THE_AUDIENCE";
                 $issuedat_claim = time(); // issued at
                 $notbefore_claim = $issuedat_claim + 10; //not before in seconds
                 $expire_claim = $issuedat_claim + 60; // expire time in seconds
                 $token = array(
                     "iss" => $issuer_claim,
                     "aud" => $audience_claim,
                     "iat" => $issuedat_claim,
                     "nbf" => $notbefore_claim,
                     "exp" => $expire_claim,
                     "data" => array(
                         "id" => $this->id,
                         "firstname" => $this->first_name,
                         "lastname" => $this->last_name,
                         "email" => $this->email
                 ));
                 // enconde JWT DATA from $token datas and secret KEY
                 $jwt = JWT::encode($token, $secret_key, 'HS256');

                 $token_data =  array(
                     "token" => $jwt,
                     "expires_at" => date('H:i:s', $expire_claim)
                 );
                 array_push($e);
                return print_r(['payload'=> $e, 'token_data' => $token_data]);
            } else {
                http_response_code(401);
                return print_r(['status'=> 'false', 'message'=> 'Wrong credentials!']);
            }

        } else {
            http_response_code(401);
            return print_r(['status'=> 'false', 'message'=> 'Wrong credentials!']);
        }
    }







}
