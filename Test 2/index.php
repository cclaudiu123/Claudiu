<?php

    session_start();

	include_once 'inc/functions.inc.php';
	include_once 'inc/db.inc.php';

	$db = new PDO(DB_INFO,DB_USER,DB_PASS);
    //Selects the default page
    if(isset($_GET['page'])) {
        $page=htmlentities(strip_tags($_GET['page']));
    } else {
        $page='blog';
    }

    $url = (isset($_GET['url'])) ? $_GET['url'] : NULL;
	$e = retrieveEntries($db,$page,$url);
	$fulldisp = array_pop($e);
	$e = sanitizeData($e);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<link rel="stylesheet" href="/css/stylesheet.css" type="text/css"/>
	<link rel="alternate" href="/feeds/rss.php" type="application/rss+xml"
          title = "My Simple Blog - RSS 2.0"/>
	<title> Simple Blog </title>
</head>
<body>

	<h1> Simple Blog Application </h1>
	<ul id="menu">
		<li> <a href="/">Blog</a></li>
		<li> <a href="/about">About the Author</a></li>
        <li> <a href="/contact">Contact us</a></li>
	</ul>
    <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==1): ?>
    <p id="control_panel">
        You are logged in!
        <a href="/inc/update.inc.php?action=logout">Log out</a>
    </p>
    <?php endif; ?>
	<div id="entries">
<?php

    //Displays the entry
	if($fulldisp == 1) {
        if(isset($_GET['url'])){
            $url = htmlentities(strip_tags($_GET['url']));
        } else {
            $url = $e['url'];
        }
        //Displays edit and delete buttons if admin is logged in
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1){
            $admin = adminLinks($page, $url);
        } else {
            $admin = array('edit'=>NULL, 'delete'=>NULL);
        }

        $img = formatImage($e['image'], $e['title']);
        //Displays the existing comments and a form for creating new comments
        if($page == 'blog') {
            include_once 'inc/comments.inc.php';
            $comments = new Comments();
            $comment_disp = $comments->showComments($e['id']);
            $comment_form = $comments->showCommentForm($e['id']);
            $twitter = postToTwitter($e['title']);
        } else {
            $comment_form = NULL;
            $twitter = NULL;
        }
?>
        <h2><?php echo $e['title']?></h2>
        <p><?php echo $img, $e['entry']?></p>

        <p>
			<?php echo $admin['edit']?>
			<?php if($page == 'blog') echo $admin['delete']?>
		</p>

        <?php if ($page == 'blog'): ?>
		    <p class="backlink">
                <a href="<?php echo $twitter?>">Post to Twitter</a><br />
		    	<a href="./">Back to the Latest Entries</a>
		    </p>
        <h3> Comments for This Entry</h3>
        <?php echo $comment_disp, $comment_form; endif;?>
<?php
	} else {
		foreach($e as $entry) {
?>
			<p>
				<a href="/<?php echo $entry['page']?>/
						<?php echo $entry['url']?>">
						<?php echo $entry['title']?>
				</a>
			</p>
<?php
		}
	}
?>
        <p class="backlink">
            <?php //If admin is logged in displays the new entry button
            if($page == 'blog' && isset($_SESSION['loggedin'])
                                     && $_SESSION['loggedin'] == 1):
            ?>
            <a href="/admin.php?page=<?php echo $page ?>">
                Post a New Entry
            </a>
            <?php endif; ?>
        </p>
        <p>
            <a href="/feeds/rss.php">Subscribe via RSS!</a>
        </p>
        <!-- If there are existing coordonates display the google map -->
        <?php if($e['longitudine'] != NULL && $e['latitudine'] != NULL){  ?>
        <div id="map"></div>
        <script>
            function initMap() {
                var myLatLng = {lat: <?php echo $e['latitudine']?>, lng: <?php echo $e['longitudine']?>};

                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 4,
                    center: myLatLng
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    title: 'Here I am!'
                });
            }
        </script>
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDoasT53tvTjkb9-E7g8JxvBXtbdplFbsg&callback=initMap">
        </script>



        <?php } ?>
	</div>
</body>
</html>

