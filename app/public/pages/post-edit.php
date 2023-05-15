<?php 
require_once("../inc/class-post.php");
$post = new SocialPost(isset($_GET["pid"]) ? $_GET["pid"] : false);
$ptitle = $post->exist ? "Post bearbeiten" : "Neuen Post erstellen";
define("PAGE_TITLE", $ptitle);


if(!empty($_POST)){
    $post->formAction($_POST);
}
?>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="/assets/js/post-edit.js"></script>
<link rel="stylesheet" href="/assets/css/post.css">
<form class="edit-post-form" method="POST">
    <div class="form-group general">
        <p class="form-group-title">Allgemeine Optionen</p>
        <label for="title">Post-Titel (intern)</label>
        <input type="text" name="title" id="title" value="<?php echo $post->get("title"); ?>">
        <label for="dtFrom">Startdatum</label>
        <input type="date" name="dtStart" id="dtStart" value="<?php echo $post->get("dtStart"); ?>">
        <label for="dtTo">Enddatum</label>
        <input type="date" name="dtEnd" id="dtEnd" value="<?php echo $post->get("dtEnd"); ?>">
    </div>
    
    <div class="form-group events">
        <p class="form-group-title">Post Ereignisse</p>
        <div id="eventWrap">
            <?php 
                $events = $post->get("events");
                foreach($events as $id => $event){
                    echo $post->getEventHtml($id, $event);
                }
            ?>
        </div>
        <a href="#" class="add-event" id="add-event"><span class="icon">add</span> Inhalt hinzufügen</a>
    </div>
    <div class="form-group preview">
        <p class="form-group-title">Vorschau</p>
        <div class="post-preview"></div>
    </div>
    <div class="form-group actions">
        <p class="form-group-title">Aktionen</p>
        <input type="submit" name="save_post" value="Post speichern">
        <input type="submit" name="delete_post" value="Post löschen">
    </div>

</form>
