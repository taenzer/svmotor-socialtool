<?php
class User extends DB{
    public $exist = false;
    private $userId = null;
    private $userData = array();
    private $active = false;

    function __construct($userId){
        global $lan;
        parent::__construct();
        // Postid angegeben, Daten aus Datenbank ziehen
        if($userId !== false){
          if($this->userExistInDb($userId)){
            $this->exist = true;
            $this->userId = $userId;
            $this->loadFromDatabase();
          }else{
            $lan->addError("Es wurde kein User mit dieser ID gefunden!");
          }
        }else{ // Falls keine Rechnungsid angegeben wurde soll eine neue Rechnung erstellt werdn
            $this->loadDefaults();
        }
    }

    public function isActive(){
        return $this->active;
    }
    private function userExistInDb($userId){
        $sql = $this->mysqli->prepare("SELECT COUNT(1) AS userExist FROM users WHERE userId = ?");
        $sql->bind_param("i", $userId);
        $sql->execute();
        $result = $sql->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
        return $result[0]["userExist"] == 1;
    }
    private function loadDefaults(){
        $this->userData = array(
            "vorname" => "",
            "nachname" => "",
            "email" => "",
            "abteilung" => "",
            "permissions" => array()
        );
    }
    private function loadFromDatabase(){
        $sql = $this->mysqli->prepare("SELECT * FROM users WHERE userId = ?");
        $sql->bind_param("i", $this->userId);
        $sql->execute();
        $result = $sql->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
        $result = isset($result[0]) ? $result[0] : array();

        if(isset($result["passwort"], $result["login"])){
            $this->active = true;
            unset($result["passwort"], $result["login"]);
        }
        $this->userData = $result;
        $this->userData["permissions"] = json_decode($this->userData["permissions"], true);
    }
    private function updateUserData($input){
        foreach ($input as $key => $value) {
          if(isset($this->userData[$key]) && $this->userData[$key] != $value){
            $this->userData[$key] = $value;
          }
        }
        return $this->userData;
    }

    private function updateDatabase($input = array()){
        $this->updateUserData($input);
        $permissions = json_encode($this->userData["permissions"]);
        $sql = $this->mysqli->prepare("UPDATE users SET  vorname = ?, nachname = ?,email = ?,abteilung = ?,permissions = ? WHERE userId = ?");
        echo($this->mysqli->error);
        $sql->bind_param("sssssi",
            $this->userData["vorname"],
            $this->userData["nachname"],
            $this->userData["email"],
            $this->userData["abteilung"],
            $permissions,
            $this->userId
        );
        $sql->execute();
        echo($this->mysqli->error);
    }

    private function updateData($input){

        global $lan;
    
        // Validate all input data
        if(false && !$this->validateInputData($input)){
          return false;
        }
  
        $userData = $this->updateUserData($input);
    
        // Check if post exist
        if(!$this->exist){
          $uid = $this->createUser($userData);
          $lan->addSuccess("User erstellt", "/users/edit?userId=$uid");
        }
    
        // Update Database
        $this->updateDatabase($input);
        // Update Local Data
        $this->loadFromDatabase();
    
        $lan->addSuccess("User aktualisiert", "/users/edit?userId=$this->userId");
    }   

    // TODO: Check if user can/should be deleted
    private function delete(){
        global $lan;
        $sql = $this->mysqli->prepare("DELETE FROM users WHERE userId = ?");
        $sql->bind_param("i", $this->userId);
        $sql->execute();
        if(empty($this->mysqli->error)){
          $lan->addSuccess("User wurde gelöscht!");
          header("Location: /users");
          die();
        }else{
          $lan->addError("Der User konnte nicht gelöscht werden: ".$this->mysqli->error);
          return false;
        }
    }

    public function formAction($data){
        global $lan;
        if(isset($data["save_user"])){
            $this->updateData($data);
        }else if(isset($data["delete_user"])){
            $this->delete();
        }
        $lan->addError("Ungültige Aktion", $_SERVER["REQUEST_URI"]);
    }

    public function get($key){
        if($key == "abteilungName" && isset($this->userData["abteilung"])){
            return isset($this->getAbteilungen()[$this->userData["abteilung"]]) ? $this->getAbteilungen()[$this->userData["abteilung"]] : "?";
        }
        return isset($this->userData[$key]) ? $this->userData[$key] : "";
      }

    // TODO: Save & Manage Abteilungen in Database
    public static function getAbteilungen(){
        $abteilungen = array(
            "tt" => "Tischtennis",
            "fb" => "Fußball",
            "ws" => "Wintersport"
        );
        return $abteilungen;
    }

    private function createUser($data){
        var_dump($data);
        $sql = $this->mysqli->prepare("INSERT INTO users (vorname,nachname, email, abteilung, permissions) VALUES (?,?,?,?,?)");
        $permissions = json_encode($data["permissions"]);
        $sql->bind_param("sssss",
            $data["vorname"],
            $data["nachname"],
            $data["email"],
            $data["abteilung"],
            $permissions
        );
        $sql->execute();
    
        if(!empty($this->mysqli->error)){
            global $lan;
            $lan->addError("Fehler beim anlegen des Datensatzes: ".$this->mysqli->error, "/");
        }
        return $this->mysqli->insert_id;
    }
    public function __debugInfo(){
        $properties = get_object_vars($this);
        return $properties;
    }
}


class Auth extends DB{

  protected $mysqli;
  private $noredir = array("/", "/login", "/logout");

  function __construct($mysqli){
    $this->mysqli = $mysqli;
  }

  public function check(){
    if(!$this->isUserLoggedIn()){
      $redirect = $_SERVER['REQUEST_URI'];
      $redirect = !in_array($redirect, $this->noredir) ? $redirect : "";
      $this->goToLogin($redirect);
    }
  }

  public function goToLogin($redTo = ""){
    $header = "Location: /login";
    if(!empty($redTo)){
      $header.= "?".http_build_query(array("redirect_to" => $redTo));
    }
    header($header);
    die();
  }


  public function login($user, $passw){
    if($this->isUserLoggedIn()){
      return false;
    }
    $pwcheck = $this->checkPassword($user, $passw); 
    if($pwcheck !== false){
      $this->newSession($pwcheck);
      return true;
    }else{
        global $lan;
        $lan->addError("Benutzername oder Passwort falsch.", "/login");
      return false;
    }
  }

  /**
   * Checks if Username and Password Matches and returns False (no match) or userId (match)
   * @param $user - Username
   * @param $passw - Passwort
   * @return False if no match and userId if match
   */
  public function checkPassword($user, $passw){
    
    try{
        $sql = $this->mysqli->prepare("SELECT passwort, userId FROM users WHERE login = ? LIMIT 1");
    } catch (\mysqli_sql_exception $err) {
        global $db;
        $db->errorHandler($err->getMessage());
        return false;
    }  
    $sql->bind_param("s", $user);
    $sql->execute();
    $result = $sql->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);

    if(count($result) != 1){
      return false;
    }

    $userId = $result[0]["userId"];

    // Salt & Hash Password
    $passw .= $userId."ßVAUMQt0R)";
    $passw = hash("sha512", $passw);

    return $passw === $result[0]["passwort"] ? $userId : false;
  }

  public function logout(){
    $this->clearSession();
  }

  public function activate($userId, $login, $password){
    global $lan;
    if($this->isUserLoggedIn()){
      return false;
    }
    // Check if User exists and is not activated already
    $sql = $this->mysqli->prepare("SELECT * FROM users WHERE userId = ? AND passwort IS NULL");
    $sql->bind_param("i", $userId);
    $sql->execute();
    $result = $sql->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);

    if(count($result) != 1){
        return false;
    }

    // Check if username already exists
    if(!$this->checkUsername($login)){
        $lan->addError("Username bereits vergeben!");
        return false;
    }

    // Salt & Hash Password
    $password .= $userId."ßVAUMQt0R)";
    $password = hash("sha512", $password);

    // Store Password in Database
    return $this->updateDbUser($userId, array("login" => $login, "passwort" => $password));

  }

  private function updateDbUser($userId, $args){

    $this->mysqli->begin_transaction();
    foreach ($args as $key => $value) {
      $sql = $this->mysqli->prepare("UPDATE users SET $key = ? WHERE userId = ?");
      $sql->bind_param("ss", $value, $userId);
      $sql->execute();
    }
    $this->mysqli->commit();
    return true;
  }

  /*
  * Checks if a username is already in use.
  * @return true if username can be used, false if not
  */
  private function checkUsername($uname){
    $sql = $this->mysqli->prepare("SELECT login FROM users WHERE login = ?");
    $sql->bind_param("s", $uname);
    $sql->execute();

    $result = $sql->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);

    if(count($result) != 0){
      return false;
    }else{
      return true;
    }
  }


  public function isUserLoggedIn(){

    $sid = isset($_SESSION["sid"]) ? $_SESSION["sid"] : false;
    $skey = isset($_SESSION["skey"]) ? $_SESSION["skey"] : false;

    if($sid === false || $skey === false){
      $sid = isset($_COOKIE["svm-session-sid"]) ? $_COOKIE["svm-session-sid"] : false;
      $skey = isset($_COOKIE["svm-session-skey"]) ? $_COOKIE["svm-session-skey"] : false;
    }

    if($sid === false || $skey === false){
      return false;
    }
    return $this->checkSession($sid, $skey);
  }


  private function newSession($userId){
    $this->clearSession();
    $skey = hash('sha256', uniqid()."svm#hifVurrV1CxP");
    $sid = $this->createLoginSession($skey, $userId);

    if($sid === false){
      new Exception("Session Error: New Session could not be initialized.");
    }
    $this->updateSessionData($sid, $skey, $userId);
  }

  private function clearSession(){
    $this->updateSessionData("","","");
    return true;
  }

  private function checkSession($sid, $skey){
    $session = $this->checkLoginSession($sid, $skey);

    if($session !== false){
      if(!headers_sent()){
        $this->updateSessionData($sid, $skey, $session);
      }
      return true;
    }else{
      return false;
    }
  }

  private function updateSessionData($sid, $skey, $uid){
    $_SESSION["sid"] = $sid;
    $_SESSION["skey"] = $skey;
    $_SESSION["userId"] = $uid;

    setcookie("svm-session-sid", $sid, time()+60*60*24*30, "/");
    setcookie("svm-session-skey", $skey, time()+60*60*24*30, "/");
  }

  private function getSessionData(){
    $data = array(
      "sid" => $_SESSION["sid"],
      "skey" => $_SESSION["skey"],
      "userId" => $_SESSION["userId"]
    );
    return $data;
  }


    public function createLoginSession($skey, $userId){
      $sql = $this->mysqli->prepare("INSERT INTO login_sessions (skey, userId, expires) VALUES (?,?,?)");
      $exp = new DateTime();
      $exp->modify("+30 days");
      $exp = $exp->format("Y-m-d H:i");
      $sql->bind_param("sis", $skey, $userId, $exp);
      $sql->execute();
      if($sql->errno == 0){
        return $this->mysqli->insert_id;
      }else{
        return false;
      }
    }

    public function checkLoginSession($sid, $skey){
        try{
            $sql = $this->mysqli->prepare("SELECT userId FROM login_sessions WHERE sid = ? AND skey = ? and expires > NOW()");
        } catch (\mysqli_sql_exception $err) {
            global $db;
            $db->errorHandler($err->getMessage());
            return array();
        }  
        $sql->bind_param("is", $sid, $skey);
        $sql->execute();
        $result = $sql->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);

        if(count($result) == 1){
            return $result[0]["userId"];
        }else{
            return false;
        }
    }


    //TODO:
    public function getLoggedInUser(){
        $sessionData = $this->getSessionData();
        $user = new User($sessionData["userId"]);
        return $user;
    }

    public function getActivationLink($email, $userId){
        global $db;
        $link = "https://{$_SERVER['HTTP_HOST']}/activate?email={$email}&userId={$userId}&key=";
        $link .= $db->getActivationKey($userId, $email);
        return $link;
    }




}

 ?>
