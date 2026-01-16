<?php 

class connection
{
    public $hostname = 'localhost';
    public $username = 'root';
    public $pswd = '';
    public $databaseName = 'vk_solar';
    public $conn;

    public function my_connect()
    {
        $this->conn = new mysqli($this->hostname,$this->username,$this->pswd,$this->databaseName);
        return $this->conn;
    }
}

?>