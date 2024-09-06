<?php
session_start();
session_destroy();
header("Location: ../Page/home.php");
exit;
?>
