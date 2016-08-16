
<?php
function retrieveEntries($db, $page, $url=NULL)
{
    if(isset($url)) {
        $sql = "SELECT id, page, title, image, entry, longitudine, latitudine, 
                        created
                FROM entries
                WHERE url=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($url));
        $e = $stmt->fetch();
        $fulldisp = 1;
    } else {
        $sql = "SELECT id, page, title, image, entry, longitudine, latitudine,
                        url, created
                FROM entries
                WHERE page=?
                ORDER BY created DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($page));
        $e = NULL;
        //store the result
        while($row = $stmt->fetch()) {
            if($page=='blog'){
                $e[]=$row;
                $fulldisp=0;
            } else {
                $e = $row;
                $fulldisp = 1;
            }
        }
        if(!is_array($e)) {
            $fulldisp = 1;
            $e = array('title' => 'No Entries Yet',
                        'entry' => '<a href="/admin/about>"Post an entry!</a>');
            }
    }
    array_push($e, $fulldisp);
    return $e;
}

function sanitizeData($data)
{
    if (!is_array($data)) {
        return strip_tags($data, "<a>");
    } else {
        return array_map('sanitizeData', $data);
    }
}

function makeUrl($title)
{
    $patterns = array('/\s+/','/(?!-)\W+/');
    $replacements = array('-','');
    return preg_replace($patterns, $replacements, strtolower($title));
}

function adminLinks($page, $url)
{
    $editURL ="/admin/$page/$url";
    $deleteURL ="/admin/delete/$url";

    $admin['edit'] = "<a href=\"$editURL\">edit</a>";
    $admin['delete'] = "<a href=\"$deleteURL\">delete</a>";
    return $admin;
}

function confirmDelete($db,$url)
{
    $e = retrieveEntries($db,'',$url);
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

function deleteEntry($db,$url)
{
    $sql = "DELETE FROM entries
            WHERE url=?
            LIMIT 1";
    $stmt = $db->prepare($sql);
    return $stmt->execute(array($url));
}

function deleteImage($db, $url)
{
    $sql = "SELECT image 
            FROM entries 
            WHERE url=? 
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($url));
    $path = $stmt->fetch(PDO::FETCH_ASSOC);//pt path
    return $path['image'];
}

function formatImage($img=NULL, $alt=NULL)
{
    if(isset($img)){
        return '<img src="'.$img.'"alt="'.$alt.'"/>';
    } else {
        return NULL;
    }
}

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

function shortenUrl($url)
{
    $api = 'http://api.bit.ly/shorten';
    $param = 'version=2.0.1&longUrl='.urlencode($url).'&login=phpfab'
        . '&apiKey=R_7473a7c43c68a73ae08b68ef8e16388e&format=xml';

    $uri = $api . "?" . $param;
    $response = file_get_contents($uri);

    $bitly = simplexml_load_string($response);
    return $bitly->results->nodeKeyVal->shortUrl;
}

function postToTwitter($title)
{
    $full = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $short = shortenUrl($full);
    $status = $title . ' ' . $short;
    return 'http://twitter.com/?status='.urlencode($status);
}

?>








