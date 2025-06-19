<?php
session_start();
session_destroy();

header("Location: formulaireconnexion.php");
exit;
?>