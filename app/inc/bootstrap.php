<?php
session_start();
require_once("class-logAndNotify.php");
$lan = new LogAndNotify();

require_once("class-database.php");
$db = new DB();

require_once("class-auth.php");
$auth = new Auth($db->getMySqli());