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

    public function getMysqli(){
      return $this->mysqli;
    }

    protected function getDbDebugLink(){
      $id = uniqid();
      $secret = hash("md5", $id);
      return "/dbdebug?id={$id}&secret={$secret}";
    }

    public function validateDebugParams($id, $secret){
      return hash("md5", $id) == $secret;
    }
    public function install(){
      // Create Posts Table
      $promts[] = "CREATE TABLE IF NOT EXISTS posts (
          pid int(11) NOT NULL AUTO_INCREMENT,
          title text DEFAULT NULL,
          dtStart date DEFAULT NULL,
          dtEnd date DEFAULT NULL,
          createdBy int(11) NOT NULL,
          created datetime NOT NULL DEFAULT current_timestamp(),
          editedBy int(11) DEFAULT NULL,
          edited datetime DEFAULT NULL,
          events longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
          PRIMARY KEY (pid)
        )
      ";
      
      $sql = $this->mysqli->prepare(str_replace("\r\n","",$promt));
      $sql->execute();

    }
}