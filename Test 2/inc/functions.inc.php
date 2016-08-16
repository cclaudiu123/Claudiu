<?php

/**
 * Retrieves the entries
 * @param $db
 * @param $page
 * @param null $url
 * @return array|null
 */
function retrieveEntries($db, $page, $url=NULL)
{
    //If an entry is URL was supplied, load the associated entry
    if(isset($url)) {
        $sql = "SELECT id, page, title, image, entry, longitudine, latitudine, created
                FROM entries
                WHERE url=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($url));

        $e = $stmt->fetch();
        $fulldisp = 1;
    } else {
        //If no entry id was supplied, load all entry titles
        $sql = "SELECT * FROM entries
                WHERE page=?
                ORDER BY created DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($page));

        $e = NULL;
        //Loop through results and store them as an array
        while($row = $stmt->fetch()) {
            if($page == 'blog'){
                $e[] = $row;
                $fulldisp = 0;
            } else {
                $e = $row;
                $fulldisp = 1;
            }
        }
        if(!is_array($e)) {
            $fulldisp = 1;
            $e = array(
                'title' => 'No Entries Yet',
                'entry' => '<a href="/admin/about>"Post an entry!</a>'
            );
        }
    }
    array_push($e, $fulldisp);

    return $e;
}

/**
 * Performs basic data sanitation
 *
 * @param $data
 * @return array|string|sanitizeData
 */
function sanitizeData($data)
{
    if (!is_array($data)) {
        return strip_tags($data, "<a>");
    } else {
        return array_map('sanitizeData', $data);
    }
}

/**
 * Makes the title into an url
 * @param $title
 * @return mixed
 */
function makeUrl($title)
{
    $patterns = array('/\s+/', '/(?!-)\W+/');
    $replacements = array('-', '');

    return preg_replace($patterns, $replacements, strtolower($title));
}

/**
 * Creates the edit and delete links
 * @param $page
 * @param $url
 * @return mixed
 */
function adminLinks($page, $url)
{
    $editURL = "/admin/$page/$url";
    $deleteURL = "/admin/delete/$url";

    $admin['edit'] = "<a href=\"$editURL\">edit</a>";
    $admin['delete'] = "<a href=\"$deleteURL\">delete</a>";

    return $admin;
}

/**
 * Displays a delete confirmation form
 * @param $db
 * @param $url
 * @return string
 */
function confirmDelete($db, $url)
{
    $e = retrieveEntries($db, '', $url);

    return <<<FORM
<form action="/admin.php" method="post">
    <fieldset>
        <legend>Are you sure?</legend>
        <p>Are you sure you want to delete the entry "$e[title]"?</p>
        <input type="submit" name="submit" value="Yes"/>
        <input type="submit" name="submit" value="No"/>
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="url" value="$url"/>
    </fieldset>
</form>
FORM;
}

/**
 * Deletes an entry
 * @param $db
 * @param $url
 * @return mixed
 */
function deleteEntry($db, $url)
{
    $sql = "DELETE FROM entries
            WHERE url=?
            LIMIT 1";

    $stmt = $db->prepare($sql);

    return $stmt->execute(array($url));
}

/**
 * Deletes an image
 * @param $db
 * @param $url
 * @return mixed
 */
function deleteImage($db, $url)
{
    $sql = "SELECT image 
            FROM entries 
            WHERE url=? 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute(array($url));

    $path = $stmt->fetch(PDO::FETCH_ASSOC);

    return $path['image'];
}

/**
 * Displays the image
 * @param null $img
 * @param null $alt
 * @return null|string
 */
function formatImage($img = NULL, $alt = NULL)
{
    if($img != NULL){
        return '<img src="' . $img . '"alt="' . $alt . '"/>';
    } else {
        return NULL;
    }
}

/**
 * Creates a form for creating new users
 * @return string
 */
function createUserForm()
{
    return <<<FORM
<form action="/inc/update.inc.php" method="post">
    <fieldset>
        <legend>Create a New Administrator</legend>
        <label>Username
            <input type="text" name="username" maxlength="75" />
        </label>
        <label>Password
            <input type="password" name="password" />
        </label>
        <input type="submit" name="submit" value="Create" />
        <input type="submit" name="submit" value="Cancel" />
        <input type="hidden" name="action" value="createuser" />
    </fieldset>
</form>
FORM;
}

/**
 * Shortens the url
 * @param $url
 * @return mixed
 */
function shortenUrl($url)
{
    $api = 'http://api.bit.ly/shorten';
    $param = 'version=2.0.1&longUrl=' . urlencode($url) . '&login=phpfab'
        . '&apiKey=R_7473a7c43c68a73ae08b68ef8e16388e&format=xml';

    $uri = $api . "?" . $param;
    $response = file_get_contents($uri);

    $bitly = simplexml_load_string($response);

    return $bitly->results->nodeKeyVal->shortUrl;
}

/**
 * Posts the url to twitter
 * @param $title
 * @return string
 */
function postToTwitter($title)
{
    $full = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $short = shortenUrl($full);
    $status = $title . ' ' . $short;

    return 'http://twitter.com/?status=' . urlencode($status);
}

?>








