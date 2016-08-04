
<?php
function retrieveEntries($db, $page, $url=NULL){
    if(isset($url)) {
        $sql = "SELECT id, page, title, entry
                FROM entries
                WHERE url=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($url));
        $e = $stmt->fetch();
        $fulldisp = 1;
    } else {
        $sql = "SELECT id, page, title, entry, url
                FROM entries
                WHERE page=?
                ORDER BY created DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($page));
        $e = NULL;
        while($row = $stmt->fetch()) {
            $e[]=$row;
            $fulldisp = 0;
        }
        /*$rows=$db->query($sql); //sa nu mai dea erroare in query
        if (!$rows) {
            $rows = array();
        }
        foreach($rows as $row){ //aici
            $e[] = array(
                'id'=>$row['id'],
                'title'=>$row['title']);
            }
        */

        if(!is_array($e)) {
            $fulldisp = 1;
            $e = array('title' => 'No Entries Yet',
                        'entry' => 'This page does not have an entry yet!');
                        //'entry' => '<a href="/admin.php>"Post an entry!</a>');
            }
    }
    array_push($e, $fulldisp);
    return $e;
}
function sanitizeData($data){
    if (!is_array($data)) {
        return strip_tags($data, "<a>");
    } else {
        return array_map('sanitizeData', $data);
    }
}
function makeUrl($title){
    $patterns = array('/\s+/','/(?!-)\W+/');
    $replacements = array('-','');
    return preg_replace($patterns,$replacements,strtolower($title));
}

function adminLinks($page, $url){
    $editURL ="/admin/$page/$url";
    $deleteURL ="/admin/delete/$url";

    $admin['edit'] = "<a href=\$deleteURL\">delete</a>";
    return $admin;
}
?>


