<?php
header('Content-Type: application/rss+xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>

<?php

include_once '../inc/functions.inc.php';
include_once '../inc/db.inc.php';

$db = new PDO(DB_INFO,DB_USER,DB_PASS);

$e = retrieveEntries($db,'blog');
array_pop($e);
$e = sanitizeData($e);
?>
<rss version="2.0">
    <channel>
        <title>My Simple Blog</title>
        <link>http://test.local/blog/</link>
        <description>This blog is awesome.</description>
        <language>en-us</language>
        <?php
            foreach ($e as $row):
                $entry = htmlentities($row['entry']);
                $url = 'http://test.local/blog/' . $row['url'];
                $date = date(DATE_RSS, strtotime($e['created']));
        ?>
        <item>
            <title><?php echo $row['title']; ?></title>
            <description><?php echo $entry; ?></description>
            <link><?php echo $url; ?> </link>
            <guid><?php echo $url; ?></guid>
            <pubDate><?php echo $date; ?></pubDate>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
