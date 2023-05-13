<?php

define("PAGE_TITLE", "Account Aktivierung");

if(!$auth->isUserLoggedIn() && isset($_POST["activate"], $_POST["login"], $_POST["email"], $_POST["userId"], $_POST["pass"], $_POST["pass2"], $_POST["key"])){

  if($db->getActivationKey($_POST["userId"], $_POST["email"]) == $_POST["key"]){
    if($_POST["pass"] === $_POST["pass2"]){
      if($auth->activate($_POST["userId"], $_POST["login"], $_POST["pass"])){
        $lan->addSuccess("Konto erfolgreich aktiviert!", "/login");
      }else{
        $lan->addError("Fehler bei der Account Aktivierung", $_SERVER['REQUEST_URI']);
      }
    }else{
      $lan->addError("Passwörter stimmen nicht überein!", $_SERVER['REQUEST_URI']);
    }

  }else{
    $lan->addError("Aktivierungsschlüssel ungültig!", "/login");
  }

}

if($auth->isUserLoggedIn()){

  if(isset($_GET["redirect_to"])){
    header('Location: '.$_GET["redirect_to"]);
    die();
  }else if(isset($_POST["redirect_to"]) && !empty($_POST["redirect_to"])) {
    header('Location: '.$_POST["redirect_to"]);
    die();
  }
  header('Location: /');
  die();
}

 ?>
<form class="login" method="post">

    <p class="welcome" style="margin: 20px 0;">Hallo {{Vorname}}! Aktiviere jetzt deinen Account um das Eventverein Teamportal nutzen zu können.</p>

    <p><label for="login">Benutzername</label><br>
    <input type="text" name="login" id="login" maxlength="15" value="" required></p>

    <p><label for="pass">Passwort</label><br>
    <input type="password" name="pass" id="pass" value="" required></p>

    <p><label for="pass">Passwort wiederholen</label><br>
    <input type="password" name="pass2" id="pass2" value="" required></p>

    <input type="submit" name="activate" value="Konto aktivieren">

    <input type="hidden" name="key" value="<?php echo isset($_GET["key"]) ? $_GET["key"] : ""; ?>">
    <input type="hidden" name="userId"  value="<?php echo isset($_GET["userId"]) ? $_GET["userId"] : ""; ?>">
    <input type="hidden" name="email"  value="<?php echo isset($_GET["email"]) ? $_GET["email"] : ""; ?>">
</form>


