<?php
// Simple PHP info page to test
echo "<h1>PHP Test Page</h1>";
echo "<p>If you can see this, PHP is working!</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Current script: " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<hr>";
phpinfo();
?>
