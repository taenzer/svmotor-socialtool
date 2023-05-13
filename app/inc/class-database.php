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

    public function errorHandler($msg = ""){
      global $lan;
      $debugUrl = $this->getDbDebugLink();
      $lan->addError("Datenbankfehler: ".$msg, $debugUrl);
    }
    public function getActivationKey($userId, $email){
    	return hash("sha512", $userId.$email);
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


      $promts[] = "CREATE TABLE IF NOT EXISTS users (
        userId int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        login varchar(100),
        passwort text,
        vorname text NOT NULL,
        nachname text,
        email text NOT NULL,
        abteilung text,
        permissions longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
        PRIMARY KEY (userId))";

      $promts[] = "CREATE TABLE IF NOT EXISTS login_sessions (
        sid int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        skey text NOT NULL,
        userId int(11) UNSIGNED NOT NULL,
        expires datetime NOT NULL,
        PRIMARY KEY (sid),
        FOREIGN KEY (userId) REFERENCES users (userId) ON DELETE CASCADE
      )";

      $promts[] = "REPLACE INTO users (userId, email, login, passwort, vorname, permissions)
        VALUES (1,'', 'install', '2515dadeb2c76a0f93babe3bdeaebe11ec951a414ce49e3de7394605d376a3fd66fcdbc8a2f4ba927f82298e89519d57f762cd65cdf5f7d1cfe3afa247edb38a', 'DELETE ME', '[]')";

      foreach($promts as $promt){
        $sql = $this->mysqli->prepare(str_replace("\r\n","",$promt));
        $sql->execute();
      }
    }

    public function getUsers(){
      $sql = $this->mysqli->prepare("SELECT userId FROM users");
      $sql->execute();
      $results = $sql->get_result();
      $results = $results->fetch_all(MYSQLI_ASSOC);
      $users = array();
      foreach ($results as $result) {
        $users[] = new User($result["userId"]);
      }
      return $users;
    }
}