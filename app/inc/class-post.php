<?php 
require_once('../inc/class-database.php');
class SocialPosts extends DB{
    function __construct(){
        parent::__construct();
    }
  
    public function display(){
      $posts = $this->getFromDb();
      //var_dump($rechnungen);
      ?>
      <link rel="stylesheet" href="/assets/css/post.css">
      <div class="post-list">

        <?php
        foreach ($posts as $post) { ?>
          <a href="/post/edit?pid=<?php echo $post->get("pid"); ?>">
            <div class="post-list-item">
                <div class="pli-pid"><p>PID: <?php echo $post->get("pid"); ?></p></div>
                <div class="pli-main">
                <p class="pli-title"><?php echo !empty($post->get("title")) ? $post->get("title") : "Post ohne Titel"; ?></p>
                    <p class="pli-dtRange"><?php echo $post->get("dtStart", true)->format("d.m.Y")."-".$post->get("dtEnd", true)->format("d.m.Y"); ?></p>
                </div>
                <div class="pli-events">
                    <p class="pli-eventcnt"><?php echo count($post->get("events")); ?> <span class="icon">event</span></p>
                </div>
                <div class="pli-created">
                    <p class="pli-dateCreated"><?php echo $post->get("created", true)->format("d.m.Y H:i")." Uhr"; ?></p>
                    <p class="pli-userCreated">von System</p>
                </div>
                <div class="pli-edited">
                    <p class="pli-dateEdited"><?php echo $post->get("edited", true)->format("d.m.Y H:i")." Uhr"; ?></p>
                    <p class="pli-userEdited">von System</p>
                </div>
                <div class="pli-actions">
                    <p class="pli-action preview"><span class="icon">visibility</span> Vorschau</p>
                    <p class="pli-action download"><span class="icon">download</span> Herunterladen</p>
                </div>
            </div>
          </a>
        <?php } ?>
      </div>
      <?php
    }
  
    public function getFromDb(){
      try {
        $sql = $this->mysqli->prepare("SELECT pid FROM posts;");
      } catch (\mysqli_sql_exception $err) {
        global $lan;
        $debugUrl = $this->getDbDebugLink();
        $lan->addError("Datenbankfehler: ".$err->getMessage(), $debugUrl);
        return array();
      }
      
      $sql->execute();
      $result = $sql->get_result();
      $result = $result->fetch_all(MYSQLI_ASSOC);
  
      $posts = array();
      foreach ($result as $res) {
        $posts[] = new SocialPost($res["pid"]);
      }
      return $posts;
    }
  }

class SocialPost extends DB {
    public $exist = false;
    private $pid = null;
    private $postData = array();

    function __construct($pid){
        global $lan;
        parent::__construct();
        // Postid angegeben, Daten aus Datenbank ziehen
        if($pid !== false){
          if($this->postExistInDb($pid)){
            $this->exist = true;
            $this->pid = $pid;
            $this->loadFromDatabase();
          }else{
            $lan->addError("Es wurde kein Post mit dieser ID gefunden!", "/");
          }
        }else{ // Falls keine Rechnungsid angegeben wurde soll eine neue Rechnung erstellt werdn
            $this->loadDefaults();
        }
    }
    private function postExistInDb($pid){
        $sql = $this->mysqli->prepare("SELECT COUNT(1) AS postExist FROM posts WHERE pid = ?");
        $sql->bind_param("i", $pid);
        $sql->execute();
        $result = $sql->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
    
        return $result[0]["postExist"] == 1;
    }
    private function validateInputData($input){
        return true;
        }   
    public function get($key, $dateTimeObj = false){
        if(!isset($this->postData[$key])){
            return "";
        }
        if(in_array($key, array("dtStart", "dtEnd", "edited", "created")) && $dateTimeObj){
            $dt = new DateTime($this->postData[$key]);
            return $dt;
        }
        return $this->postData[$key];
    }
    private function loadDefaults(){
        $this->postData = array(
          "pid" => null,
          "title" => "",
          "dtStart" => date("Y-m-d"),
          "dtEnd" => date("Y-m-d"),
          "createdBy" => 0,
          "created" => date("Y-m-d H:i:s"),
          "editedBy" => 0,
          "edited" => date("Y-m-d H:i:s"),
          "events" => array()
        );
      }
    private function loadFromDatabase(){
        $sql = $this->mysqli->prepare("SELECT * FROM posts WHERE pid = ?");
        $sql->bind_param("i", $this->pid);
        $sql->execute();
        $result = $sql->get_result();
        $result = $result->fetch_all(MYSQLI_ASSOC);
    
        $this->postData = $result[0];
        $this->postData["events"] = json_decode($this->postData["events"], true);
    
      }
    private function updatePostData($input){
        foreach ($input as $key => $value) {
          if(isset($this->postData[$key]) && $this->postData[$key] != $value){
            $this->postData[$key] = $value;
          }
        }
        return $this->postData;
        }
    private function updateDatabase($input = array()){
        $this->updatepostData($input);
        $events = json_encode($this->postData["events"]);
        $sql = $this->mysqli->prepare("UPDATE posts SET title = ?, dtStart = ?, dtEnd = ?,createdBy = ?,created = ?, editedBy = ?, edited = ?, events = ? WHERE pid = ?");
        echo($this->mysqli->error);
        $sql->bind_param("sssisissi",
            $this->postData["title"],
            $this->postData["dtStart"],
            $this->postData["dtEnd"],
            $this->postData["createdBy"],
            $this->postData["created"],
            $this->postData["editedBy"],
            $this->postData["edited"],
            $events,
            $this->pid
        );
        $sql->execute();
        echo($this->mysqli->error);
        }

    private function updateData($input){

        global $lan;
    
        // Validate all input data
        if(!$this->validateInputData($input)){
          return false;
        }
  
        $postData = $this->updatepostData($input);
    
        // Check if post exist
        if(!$this->exist){
          $pid = $this->createPost($postData);
          $lan->addSuccess("Post erstellt", "/post/edit?pid=$pid");
        }
    
        // Update Database
        $this->updateDatabase($input);
        // Update Local Data
        $this->loadFromDatabase();
    
        $lan->addSuccess("Post aktualisiert", "/post/edit?pid=$this->pid");
        }

    private function delete(){
        global $lan;
        if($this->postData["status"] !== "entwurf"){
          $lan->addError("Diese Rechnung wurde bereits abgeschlossen und kann nicht mehr gelöscht werden!");
          return false;
        }
        $sql = $this->mysqli->prepare("DELETE FROM rechnungen WHERE pid = ?");
        $sql->bind_param("i", $this->pid);
        $sql->execute();
        if(empty($this->mysqli->error)){
          $lan->addSuccess("Rechnung wurde gelöscht!");
          header("Location: /rechnungen");
          die();
        }else{
          $lan->addError("Die Rechnung konnte nicht gelöscht werden: ".$this->mysqli->error);
          return false;
        }
        }

    public function formAction($data){
        global $lan;
        if(isset($data["save_post"])){
            $this->updateData($data);
        }
        $lan->addError("Ungültige Aktion");
        }
    
    public static function getAbteilungen(){
        $abteilungen = array(
            "tt" => "Tischtennis",
            "fb" => "Fußball",
            "ws" => "Wintersport"
        );
        return $abteilungen;
    }
    public static function getEventHtml($id, $eventData = array()){
        ob_start();
        ?>
            <div class="event" data-eventtype="<?php echo isset($eventData["art"]) ? $eventData["art"] : "match";?>">
              <div class="event-general">
                <p class="input-wrap">
                  <label>Wann?</label>
                  <input type="datetime-local" name="events[<?php echo $id; ?>][start]" placeholder="Startzeit" value="<?php echo isset($eventData["start"]) ? $eventData["start"] : ""; ?>">
                </p>
                <p class="input-wrap">
                  <label>Wo?</label>
                  <input type="text" name="events[<?php echo $id; ?>][location]" placeholder="Location" value="<?php echo isset($eventData["location"]) ? $eventData["location"] : ""; ?>">
                </p>
              </div> 
              <div class="event-detail">
                <p class="input-wrap">
                  <label>Abteilung</label>
                  <select name="events[<?php echo $id; ?>][abteilung]" id="abteilung">
                    <?php
                    foreach(SocialPost::getAbteilungen() as $key => $abteilung){
                        $selected = isset($eventData["abteilung"]) && $eventData["abteilung"] == $key ? "selected" : "";
                        echo("<option value='{$key}' {$selected}>{$abteilung}</option>");
                    }
                    ?>
                  </select>
                </p>
                <p class="input-wrap">
                  <label>Veranstaltungsart</label>
                  <select class="eventType" name="events[<?php echo $id; ?>][art]" id="art">
                    <option value="match" <?php echo isset($eventData["art"]) && $eventData["art"] == "match" ? "selected" : "";?>>Spiel</option>
                    <option value="date" <?php echo isset($eventData["art"]) && $eventData["art"] == "date" ? "selected" : "";?>>Termin</option>
                  </select>
                </p>
              </div> 
              <div class="event-info">
                <p class="input-wrap">
                  <label>Veranstaltungstitel</label>
                  <input type="text" name="events[<?php echo $id; ?>][title]" id="" placeholder="Veranstaltungstitel" value="<?php echo isset($eventData["title"]) ? $eventData["title"] : ""; ?>">
                </p>
                <!-- MATCH Content -->
                <div class="event-type-match">
                  <div class="team">
                    <label>Heim</label>
                    <input type="text" class="heim" name="events[<?php echo $id; ?>][heim]" id="" placeholder="Heim-Mannschaft" value="<?php echo isset($eventData["heim"]) ? $eventData["heim"] : ""; ?>">
                  </div>  
                  <div class="team">
                    <label>Gast</label>
                    <input type="text" class="gegner" name="events[<?php echo $id; ?>][gegner]" placeholder="Gast-Mannschaft" value="<?php echo isset($eventData["gegner"]) ? $eventData["gegner"] : ""; ?>">
                  </div>               
                </div>
              </div>
              <a href="#" class="eventDel"><span class="icon">delete</span></a>
            </div>
        <?php
        return ob_get_clean(); 
        }
    private function createPost($data){
        $sql = $this->mysqli->prepare("INSERT INTO posts (title, dtStart,dtEnd, createdBy, created,editedBy, edited, events) VALUES (?,?,?,?,?,?,?,?)");
        $events = json_encode($data["events"]);
        $sql->bind_param("sssisiss",
            $data["title"],
            $data["dtStart"],
            $data["dtEnd"],
            $data["createdBy"],
            $data["created"],
            $data["editedBy"],
            $data["edited"],
            $events
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


?>