<?php

use Dotenv\Dotenv;

// Prepare environment variables
require_once '../vendor/autoload.php';
$dotenv = Dotenv::createImmutable('../'); // Use the provided path
$dotenv->load();


// Pass the path to the root folder where .env is located

class Connect
{
    protected String $host;
    protected String $database;
    protected String $username;
    protected String $password;
    protected $conn;

    public function __construct()
    {
        // Load from environment variables
        $this->host = $_ENV['DB_HOST'];
        $this->database = $_ENV['DB_DATABASE'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];

        // Create the connection
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database);
    }
}
