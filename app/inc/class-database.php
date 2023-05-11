<?php
class DB{
    private $c = array(
        "db" => "svmotor-social-posts",
        "host" => "mysql",
        "user" => "svmotor-socialtool",
        "pass" => "9jy@S7Kwq7BtjM]g"
      );

    protected $mysqli;
  
    function __construct(){
      $this->mysqli = new mysqli($this->c["host"], $this->c["user"], $this->c["pass"], $this->c["db"]);
      $this->mysqli->set_charset("UTF8");
      if($this->mysqli->connect_errno){
        throw new \Exception("Fehler bei der Verbindung zur Datenbank", 1);
      }
    }
    public function __debugInfo(){
      $properties = get_object_vars($this);
      unset($properties['c']);
      return $properties;
    }
}