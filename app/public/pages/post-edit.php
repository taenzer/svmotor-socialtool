<?php 
require_once("../inc/class-post.php");
define("PAGE_TITLE", "Post bearbeiten");

$post = new SocialPost(isset($_GET["pid"]) ? $_GET["pid"] : false);
if(!empty($_POST)){
    $post->formAction($_POST);
}
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="/assets/js/post-edit.js"></script>
<h3 class="page-title">Neuen Post erstellen</h3>
<form action="" method="POST">
    <div class="form-group">
        <p class="form-group-title">Allgemeine Optionen</p>
        <label for="title">Post-Titel (intern)</label>
        <input type="text" name="title" id="title" value="<?php echo $post->get("title"); ?>">
        <label for="dtFrom">Startdatum</label>
        <input type="date" name="dtStart" id="dtStart" value="<?php echo $post->get("dtStart"); ?>">
        <label for="dtTo">Enddatum</label>
        <input type="date" name="dtEnd" id="dtEnd" value="<?php echo $post->get("dtEnd"); ?>">
    </div>
    
    <div class="form-group">
        <p class="form-group-title">Post Inhalte</p>
        <div id="eventWrap">
            <?php 
                $events = $post->get("events");
                foreach($events as $id => $event){
                    echo $post->getEventHtml($id, $event);
                }
            ?>
        </div>
        <a href="#" class="add-event" id="add-event">Inhalt hinzufügen</a>
    </div>
    <div class="form-group">
        <p class="form-group-title">Aktionen</p>
        <input type="submit" name="save_post" value="Post speichern">
    </div>
</form>