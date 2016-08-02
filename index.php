<?php
include_once 'inc/functions.inc.php'; // poate probleme aici
include_once 'inc/db.inc.php'; // si aici

$db = new PDO(DB_INFO,DB_USER,DB_PASS);
$id = (isset($_GET['id'])) ? (int) $_GET['id'] : NULL;

$e = retrieveEntries($db,$id);
$fulldisp = array_pop($e);
$e = sanitizeData($e);
?>


<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type"
          content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="/css/stylesheet.css" type="text/css" />
    <title> Simple Blog </title>
</head>
<body>
<h1> Simple Blog Application </h1>
<div id="entries">
    <?php
        if($fulldisp = 1){
    ?>
        <h2> <?php
            echo $e['title'] ?> </h2>
    <p> <?php echo $e['entry'] ?> </p>

        <p class="backlink">
        <a href="./"Back to Latest Entries</a>
        </p>

    <?php
        }

        else{
            foreach($e as $entry){
                ?>
    <p>
        <a href="?id=<?php echo $entry['id']?>">
            <?php echo $entry['title'] ?>
            </a> </p>
    </php>
            }
        }
    ?>

    <p class="backlink">
        <a href="/admin.php">Post a New Entry</a>
    </p>
</div>

</body>
</html>