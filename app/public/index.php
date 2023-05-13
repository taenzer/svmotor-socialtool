<?php
require_once("../inc/bootstrap.php");

define("VERSION", "BETA v.0.1");
define("URL_BASE", "/");
define("PATH_PAGES", __DIR__ . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR);
define("PUBLIC_PAGES", array("login", "activate", "logout", "404", "join", "dbdebug"));

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if (substr($path, 0, strlen(URL_BASE)) == URL_BASE) {
  $path = substr($path, strlen(URL_BASE));
}
$path = explode("/", rtrim($path, "/\\"));
if (count($path)==1) {
  $file = $path[0]=="" ? "index.php" : $path[0] . ".php";
} else {
  $file = implode("-", $path) . ".php";
}



ob_start();

if(file_exists(PATH_PAGES . $file)){
  if(!in_array($path[0], PUBLIC_PAGES)){
    $auth->check();
    if($auth->isUserLoggedIn()){
      $_USER = $auth->getLoggedInUser();
    }else{
      $_USER = array();
    }
  }
  $messagesHtml = $lan->printMessages();
  require PATH_PAGES . $file;
}else{
    require PATH_PAGES . "404.php";
    http_response_code(404);
    $messagesHtml = $lan->printMessages();
}

$content = ob_get_clean();

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SV Motor Social Media Tool</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/fonts.css">
</head>
<body>
    <div class="content-wrapper" id="content-wrapper">
        <section id="header">
            <div class="page-title">
                <a href="/" class="home" title="Startseite"><h1>SV "Motor" Social Media Tool <span class="version"><?php echo defined("VERSION")? VERSION : "undef."; ?></span></h1></a>
                <?php echo defined("PAGE_TITLE") ? "<h2>".PAGE_TITLE."</h2>" : ""; ?>
            </div>
            <div class="header-actions">
              <?php if(!in_array($path[0], PUBLIC_PAGES) && $auth->isUserLoggedIn()){ ?>
              <a href="/settings"><div class="settings"><span class="icon">settings</span></div></a>
              <a href="/users"><div class="user"><span class="icon">account_circle</span> <?php echo $_USER->get("vorname"); ?></div></a>
              <a href="/logout"><div class="logout"><span class="icon">logout</span></div></a>
              <?php } ?>
            </div>
        </section>
        <section id="content">
          <h2 class="page-title"><?php echo defined("PAGE_TITLE") ? PAGE_TITLE : ""; ?></h2>
          <div class="notifications">
            <?php echo($messagesHtml); ?>
          </div>
            <?php 
                echo $content;   
            ?>
        </section>
        <section id="footer"><p class="copyright">&copy; TNZ Dienstleistungen</p></section>
    </div>
</body>
</html>