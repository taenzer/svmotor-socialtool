<?php
function resp($data, $code = 200){
  echo json_encode($data);
  http_response_code($code);
  exit();
}
if(!isset($_GET["nonce"]) || $_GET["nonce"] != "nonce"){
  resp("Error: Invalid Request!", 400);
}
if(!isset($_GET["action"])){
  resp("Error: No action provided!", 400);
}

switch ($_GET["action"]) {
  case 'postNewEvent':
    require_once("../inc/class-post.php");
    $id = uniqid();
    $html = SocialPost::getEventHtml($id);
    resp($html);
    break;
}

