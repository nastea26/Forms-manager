<?php 
// reuqired for using .env vars
require_once '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable('../');
$dotenv->load();

class Connect{
    protected String $host;
    protected String $database;
    protected String $username;
    protected String $password;
    protected $conn;
    
    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->database = $_ENV['DB_DATABASE'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->conn = mysqli_connect($this->host,$this->username,$this->password,$this->database);
    }
}