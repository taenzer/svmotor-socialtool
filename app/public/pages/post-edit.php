<?php 
require_once("../inc/class-post.php");
define("PAGE_TITLE", "Post bearbeiten");
?>
<h3 class="page-title">Neuen Post erstellen</h3>
<form action="">
    <div class="form-group">
        <p class="form-group-title">Allgemeine Optionen</p>
        <label for="title">Post-Titel (intern)</label>
        <input type="text" name="title" id="title">
        <label for="dtFrom">Startdatum</label>
        <input type="date" name="dtFrom" id="dtFrom">
        <label for="dtTo">Enddatum</label>
        <input type="date" name="dtTo" id="dtTo">
    </div>
    
    <div class="form-group">
        <p class="form-group-title">Post Inhalte</p>
        <a href="#" class="add-event">Inhalt hinzufügen</a>
    </div>
    <div class="event">
        <input type="datetime-local" name="start" id="" placeholder="Startzeit">
        <select name="abteilung" id="abteilung">
            <option value="tischtennis">Tischtennis</option>
            <option value="fussball">Fussball</option>
        </select>
        <input type="text" name="location" placeholder="Location">
        <select name="art" id="art">
            <option value="match">Spiel</option>
            <option value="termin">Termin</option>
        </select>

        <input type="text" name="desc" id="" placeholder="Veranstaltungstitel">

        <!-- MATCH Content -->
        <input type="text" name="heim" id="" placeholder="Heimmannschaft">
        <input type="text" name="gegner" placeholder="Gastmannschaft">

    </div>
</form>