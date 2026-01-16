<?php
class connect{
    public $servername = "localhost";
    private $username = "atriprints_vksolar";
    private $password = "atriprints_vksolar";
    private $dbname = "atriprints_vksolar";

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