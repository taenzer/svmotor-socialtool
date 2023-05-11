<?php
define("PAGE_TITLE", "Startseite");
require_once("../inc/class-post.php");
$posts = new SocialPosts();
?>

<h3 class="page-title">Deine Posts</h3> 
<a href="/post/edit">Neuen Post erstellen</a>
<?php $posts->display(); ?>