
<?php
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

       // bind data
       // bindParam func accepts 2 arguments 1. the placeholder of query, 2. The real data that we want to add
       $stmt->bindParam(":first_name", $this->first_name); 
       $stmt->bindParam(":last_name", $this->last_name);
       $stmt->bindParam(":email", $this->email); 
       $stmt->bindParam(":password", $this->password);
       $stmt->bindParam(":age", $this->age);
       $stmt->bindParam(":gender", $this->gender); 

       try{
           //this is the body of try
           $stmt->execute();
           print_r("succes is already executed");
           return true;
           
       } catch (PDOException $error){
           $errorCode = json_decode($error->errorInfo[1]);
           return print_r($errorCode);

           //check if the error is duplicate
           if($errorCode == 1062){
               //if yes, tell the user that the email already exists
               http_response_code(400); //set the status code to 400, client error
               return print_r(['status' => 'false', 'message' => 'Email is already in use ']);
           } else {
              
            return print_r(['status' => 'false', 'message' => 'error']);
           }
       }

   }
   
}
