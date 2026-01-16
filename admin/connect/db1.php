<?php
class connect{
     public $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "abcoedtech_vksolar";

    public function dbconnect()
    {
        $db = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        if (!$db) {
            die("Connection failed: " . mysqli_connect_error());
        }
        return $db;
    }
}
?>