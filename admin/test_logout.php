<?php
session_start();
session_destroy();
echo "Logged out successfully. <a href='login.php'>Click here to login again</a>";
?>
