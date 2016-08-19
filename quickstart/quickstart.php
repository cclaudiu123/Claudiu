<?php
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

echo '<response>';
$name = $_GET['name'];
$userNames = array('YODA', 'AUDRA', 'BOGDAN', 'CRISTIAN');

if(in_array(strtoupper($name), $userNames)){
    echo 'Hello, master ' . htmlentities($name) . '!';
} else if(trim($name) == ''){
        echo 'Stranger, please tell me your name!';
    } else {
        echo htmlentities($name) . ', I don\'t know you!';
}
echo '</response>';
?>