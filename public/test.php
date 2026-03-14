<?php
echo "PHP is working!";
echo "<br>";
echo "Current directory: " . __DIR__;
echo "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Not set';
echo "<br>";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] ?? 'Not set';
echo "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] ?? 'Not set';
echo "<br>";
if (file_exists('../vendor/autoload.php')) {
    echo "Autoload found: YES";
} else {
    echo "Autoload found: NO";
}
?>
