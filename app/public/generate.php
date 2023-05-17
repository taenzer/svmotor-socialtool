<?php
require_once("../inc/bootstrap.php");

$auth->check();
if(!isset($_GET["pid"]) || empty($_GET["pid"])){
    $lan->addError("Ungültiger Link!", "/");
}

require_once("../inc/class-post.php");
$post = new SocialPost($_GET["pid"]);

require_once("../inc/class-contentCreator.php");
$content = new ContentCreator();

$content->setHeading($post->get("title"));
$content->setDtStart($post->get("dtStart", true));
$content->setDtEnd($post->get("dtEnd", true));

//$content->test();
$content->create();
$content->output();
