<?php
require_once("../inc/class-contentCreator.php");

$content = new ContentCreator();

$content->addEvent();
$content->addEvent();
$content->create();
$content->output();