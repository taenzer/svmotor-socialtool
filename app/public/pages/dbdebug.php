<?php
define("PAGE_TITLE", "Datenbank Fehlerbehebung");
$url = $_SERVER['REQUEST_URI'];
$query = parse_url($url, PHP_URL_QUERY);

// Returns a string if the URL has parameters or NULL if not
if ($query) {
    $url .= '&install';
} else {
    $lan->addError("Ungültiger Link");
}

if($db->validateDebugParams($_GET["id"], $_GET["secret"])){
    if(isset($_GET["install"])){
        $db->install();
        $lan->addInfo("Die Datenbank Reparatur ist abgeschlossen.", "/");
    }else{
        ?>
        <p>Es scheint, als wäre die Datenbank beschädigt. Repariere sie über den folgenden Link:</p>
        <a href="<?php echo $url; ?>">Datenbank reparieren</a>
        <?php 
    }

} else {
    $lan->addError("Ungültiger Link", "/");
}

