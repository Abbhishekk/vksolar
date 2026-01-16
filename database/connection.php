<?php 

class connection
{
     public $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "abcoedtech_vksolar";
    public $conn;

    public function my_connect()
    {
        $this->conn = new mysqli($this->servername,$this->username,$this->password,$this->database);
        return $this->conn;
    }
}

?>