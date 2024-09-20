<?php 
// reuqired for using .env vars
use Dotenv\Dotenv;
require_once '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../');
$dotenv->load();

$host = getenv('DB_HOST');
$database = getenv('DB_DATABASE');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

$conn=mysqli_connect($host,$username,$password,$database)or die("an error occured while connecting to the database");
