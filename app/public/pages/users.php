<?php
define("PAGE_TITLE", "Benutzerverwaltung"); ?>

<link rel="stylesheet" href="/assets/css/users.css">
<a href="/users/edit" title="Neuen Benutzer erstellen">Neuen Benutzer erstellen</a>
<div class="user-list">

    <?php 
    $users = $db->getUsers();
    foreach ($users as $user) { ?>
        <a href="/users/edit?userId=<?php echo $user->get("userId"); ?>">
            <div class="user-list-item <?php echo $user->isActive() ? "active" : "not-active"; ?>">
                <div class="uli-pid">
                    <p><span class="icon">account_circle</span><span class="icon active">check</span><span class="icon not-active">more_horiz</span></p>
                </div>
                <div class="uli-main">
                    <p class="uli-name"><?php echo "{$user->get("vorname")} {$user->get("nachname")}"; ?></p>
                    <p><?php echo "{$user->get("email")}"; ?></p>
                </div>
                <div class="uli-abteilung">
                <p><?php echo "Abteilung {$user->get("abteilungName")}"; ?></p>
                </div>
            </div>
      </a>
    <?php } ?>

</div>