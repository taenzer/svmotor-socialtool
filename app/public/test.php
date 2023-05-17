<?php
//ini_set("memory_limit", "200M");
require_once("../inc/class-contentCreator.php");

$content = new ContentCreator();

$content->addEvent();
$content->addEvent();
$content->addEvent();
$content->addEvent();
//$content->test(); return;
$content->create();
$content->output();