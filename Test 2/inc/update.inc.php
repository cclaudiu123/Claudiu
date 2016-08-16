<?php

session_start();

include_once 'functions.inc.php';
include_once 'images.inc.php';

//Creates a new entry
if(($_SERVER['REQUEST_METHOD']=="POST") && $_POST['submit'] == "Save Entry"
    && !empty($_POST['page'])
    && !empty($_POST['title'])
    && !empty($_POST['entry'])){

    $url = makeUrl($_POST['title']);

    if(strlen($_FILES['image']['tmp_name']) > 0){
        try{
            $img = new ImageHandler("/images/");
            $img_path = $img->processUploadedImage($_FILES['image']);
        }catch(Exception $e){
            die($e->getMessage());
        }
    } else {
        $img_path = NULL;
    }

    include_once 'db.inc.php';
    $db = new PDO(DB_INFO, DB_USER, DB_PASS);

    if (!empty($_POST['id'])) {
        $sql = "UPDATE entries
                SET title=?,image=?,entry=?,url=?,longitudine=?,latitudine=?
                WHERE id=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
                $_POST['title'],
                $img_path,
                $_POST['entry'],
                $url,
                $_POST['latitudine'],
                $_POST['longitudine'],
                $_POST['id']
            ));

        $stmt->closeCursor();
    } else {
        $sql = "INSERT INTO entries (page,title,image,entry,url,longitudine,latitudine) 
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
                $_POST['page'],
                $_POST['title'],
                $img_path,
                $_POST['entry'],
                $url,
                $_POST['longitudine'],
                $_POST['latitudine'],
            ));
        //Verifies is there are urls with the same name
        $sql = "SELECT COUNT(url) AS dup 
                FROM entries
                WHERE url='$url'
                LIMIT 1;";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $val = $stmt->fetch();
        $stmt->closeCursor();

        //If there are urls with the same name add id to url
        if($val['dup'] > 1 ){
            $id_obj = $db->query("SELECT LAST_INSERT_ID()");
            $id = $id_obj->fetch();
            $id_obj->closeCursor();
            $url = $id[0].$url;
            $p = $id[0];
            $sql = "UPDATE entries
                    SET url='$url'
                    WHERE id='$p'
                    LIMIT 1;";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $stmt->closeCursor();
        }
    }

    $page = htmlentities(strip_tags($_POST['page']));
    header('Location: /'.$page.'/'.$url);

    exit;

 //If there are errors display custom error message
} else if(($_SERVER['REQUEST_METHOD'] == "POST") && $_POST['submit'] == "Save Entry"
            && ((empty($_POST['page'])) || empty($_POST['title']) || empty($_POST['entry']))){

    if(empty($_POST['title']) && empty($_POST['entry'])) {
        $_SESSION['error'] = 9;
    } else if(empty($_POST['entry'])){
        $_SESSION['error'] = 8;
    } else if(empty($_POST['title'])){
        $_SESSION['error'] = 7;
    }

    $page = htmlentities(strip_tags($_POST['page']));
    header("Location:/admin/$page");

    //Saves the comment
    } else if($_SERVER['REQUEST_METHOD'] == 'POST'
        && $_POST['submit'] == 'Post Comment') {

        include_once 'comments.inc.php';
        $comments = new Comments();

        $comments->saveComment($_POST);
            if(isset($_SERVER['HTTP_REFERER'])) {
                $loc = $_SERVER['HTTP_REFERER'];
            } else {
              $loc = '../';
            }
            header('Location: '.$loc .'#comment-form'); //pt pagejump
            exit;

        } else if($_GET['action'] == 'comment_delete'){
            include_once 'comments.inc.php';
            $comments = new Comments();
            echo $comments->confirmDelete($_GET['id']);
            exit;

} else if($_SERVER['REQUEST_METHOD'] == 'POST'
        && $_POST['action'] == 'comment_delete'){

    $loc = isset($_POST['url']) ? $_POST['url'] : '../'
    ;
    if($_POST['confirm'] == 'Yes') {
        include_once 'comments.inc.php';
        $comments = new Comments();

        if($comments->deleteComment($_POST['id'])) {
            header('Location:' . $loc);
        } else {
            exit('Could not delete comment');
        }
    } else {
        header('Location:' . $loc);
        exit;
    }
/*Verifies if fields are empty
   and logs the user in
*/
} else if($_SERVER['REQUEST_METHOD'] == 'POST'
            && $_POST['action'] == 'login'
            && !empty($_POST['username'])
            && !empty($_POST['password'])) {

     include_once 'db.inc.php';
     $db = new PDO(DB_INFO, DB_USER, DB_PASS);

     $sql = "SELECT COUNT(*) AS num_users
             FROM admin
             WHERE username=?
             AND password=SHA1(?)";
     $stmt = $db->prepare($sql);
     $stmt->execute(array($_POST['username'], $_POST['password']));

     $response = $stmt->fetch();

     if ($response['num_users'] > 0) {
         $_SESSION['loggedin'] = 1;
     } else {
         $_SESSION['loggedin'] = NULL;
     }
     header('Location: /');
     exit;
//Display error for login
 }else if($_SERVER['REQUEST_METHOD'] == 'POST'
         && $_POST['action'] == 'login'
         && (empty($_POST['username'])
         || empty($_POST['password']))){

   if(empty($_POST['username']) && empty($_POST['password'])) {
     $_SESSION['error'] = 6;
    } else if(empty($_POST['username'])){
     $_SESSION['error'] = 10;
    } else if(empty($_POST['password'])){
     $_SESSION['error'] = 11;
    }

$page = htmlentities(strip_tags($_POST['page']));
header("Location:/admin/$page");

//Creates login account
} else if($_SERVER['REQUEST_METHOD'] == 'POST'
    && $_POST['action'] == 'createuser'
    && !empty($_POST['username'])
    && !empty($_POST['password'])){

    include_once 'db.inc.php';
    $db = new PDO(DB_INFO, DB_USER, DB_PASS);

    $sql = "INSERT INTO admin (username, password)
            VALUES(?, SHA1(?))";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($_POST['username'], $_POST['password']));

    header('Location: /');
    exit;
    } else if($_GET['action'] == 'logout'){
        session_destroy();
        header('Location: ../');
    exit;
    } else {
        unset($_SESSION['c_name'], $_SESSION['c_email'],
              $_SESSION['c_comment'], $_SESSION['error']);

        header('Location:../');
    exit;
    }
