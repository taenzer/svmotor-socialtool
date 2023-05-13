<?php 
$user = new User(isset($_GET["userId"]) ? $_GET["userId"] : false);
$ptitle = $user->exist ? "User bearbeiten" : "Neuen User erstellen";
define("PAGE_TITLE", $ptitle);


if(!empty($_POST)){
    $user->formAction($_POST);
}

?>
<form method="POST">
    <div class="form-group">
        <p class="form-group-title">Kontaktdaten</p>
        <label for="vorname">Vorname*</label>
        <input type="text" name="vorname" id="vorname" value="<?php echo $user->get("vorname"); ?>" required>
        <label for="nachname">Nachname</label>
        <input type="text" name="nachname" id="nachname" value="<?php echo $user->get("nachname"); ?>">
        <label for="email">Email*</label>
        <input type="text" name="email" id="email" required value="<?php echo $user->get("email"); ?>">
        <label for="abteilung">Abteilung</label>
        <select name="abteilung" id="abteilung">
            <?php
            foreach(User::getAbteilungen() as $key => $abteilung){
                $selected = $user->get("abteilung") == $key ? "selected" : "";
                echo("<option value='{$key}' {$selected}>{$abteilung}</option>");
            }
            ?>
        </select>
    </div>
    <?php if(!$user->isActive()){ ?>
        <div class="form-group">
            <p class="form-group-title">Account Aktivierung</p>
            <label for="activation-link">Aktivierungslink</label>
            <input type="text" name="activation-link" disabled value="<?php echo $auth->getActivationLink($user->get("email"), $user->get("userId")); ?>">
            <p>Sende zur Account Aktivierung diesen Link an den Nutzer. Beim Klick auf den Link kann ein Benutzername sowie ein Passwort vergeben werden.</p>
        
        </div>
    <?php } ?>
    <div class="form-group">
        <p class="form-group-title">Berechtigungen</p>
        
    </div>
    <div class="form-group">
        <p class="form-group-title">Aktionen</p>
        <input type="submit" name="save_user" id="save_user" value="Daten speichern">
        <input type="submit" name="delete_user" id="delete_user" value="Benutzer löschen">
    </div>
</form>
