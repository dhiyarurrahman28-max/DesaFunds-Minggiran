<?php
session_start();
session_destroy();
header("Location: index.php?logout_success=1");
exit();
?>