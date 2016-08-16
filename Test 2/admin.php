<?php

session_start();
//Verifies if the admin is logged in
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1):

include_once 'inc/functions.inc.php';
include_once 'inc/db.inc.php';

$db = new PDO(DB_INFO,DB_USER,DB_PASS);
    //Selects the default page
    if(isset($_GET['page'])) {
        $page = htmlentities(strip_tags($_GET['page']));
    } else {
       $page = 'blog';
    }
    /**
     * If delete was selected and the confirmation
     * form was YES deletes the image and entry
     */
    if(isset($_POST['action']) && $_POST['action'] == 'delete'){
        if($_POST['submit'] == 'Yes'){
            $url = htmlentities(strip_tags($_POST['url']));

            $p = deleteImage($db,$url);
            //Deletes the entry and redirects to the default page
            if(deleteEntry($db,$url)){
                $path = $_SERVER['DOCUMENT_ROOT'].$p;
                unlink($path);

                header("Location: /");
                exit;
            }else{
                exit("Error deleting the entry!");
            }
        }else{
            header("Location: /blog/$url");
            exit;
        }
    }
    //Retrieves the url
    if(isset($_GET['url'])){
        $url = htmlentities(strip_tags($_GET['url']));

        if($page == 'delete'){
           $confirm = confirmDelete($db, $url);
        }
        //If edit was selected displays the entry in the form
        $legend = "Edit This Entry";
        $e = retrieveEntries($db, $page, $url);

        $id    = $e['id'];
        $title = $e['title'];
        $entry = $e['entry'];
        $lat   = $e['latitudine'];
        $long  = $e['longitudine'];

    } else {
        //Creates a new user
        if($page == 'createuser'){
           $create = createUserForm();
        }
        $legend = "New Entry Submission";

        $id    = NULL;
        $title = NULL;
        $entry = NULL;
        $lat   = NULL;
        $long  = NULL;
    }
    //Custom error messages
    $errorMessage = false;

    if(isset($_SESSION['error'])) {
        switch($_SESSION['error']) {
            case 7:
                $errorMessage = "Title cannot be empty";
                break;
            case 8:
                $errorMessage = "Entry cannot be empty";
                break;
            case 9:
                $errorMessage = "Both fields are empty";
                break;
            default:
                $errorMessage = false;
                break;
        }
        unset($_SESSION['error']);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <link rel="stylesheet" href="/css/stylesheet.css" type="text/css"/>
        <title> Simple Blog </title>
    </head>
    <body>
        <h1> Simple Blog Application</h1>

        <?php if($page == 'delete'):
        {
            echo $confirm;
        } elseif ($page == 'createuser'):{
            echo $create;
        } else:
            ?>
        <form method="post" action="/inc/update.inc.php"
            enctype="multipart/form-data">
            <fieldset>
                <!-- Displays the custom error messages -->
                <?php if($errorMessage): ?>
                    <p class="error"><?php echo $errorMessage; ?></p>

                <?php endif; ?>

                <legend><?php echo $legend ?></legend>
                <label>Title
                    <input <?php if($errorMessage == "Title cannot be empty" || $errorMessage == "Both fields are empty"){?> class="errorfield"<?php }?>
                            type="text" name="title" maxlength="150"
                           value="<?php echo htmlentities($title)?>"/>
                </label>
                <label>Image
                    <input type="file" name="image"/>
                </label>
                <label>Entry
                    <textarea <?php if($errorMessage == "Entry cannot be empty" || $errorMessage == "Both fields are empty"){?> class="errorfield"<?php }?>
                                name="entry" cols="45" rows="10"><?php echo sanitizeData($entry)?></textarea>
                </label>
                <label>Longitudine
                    <input type="number" name="longitudine" value="<?php sanitizeData($long)?>" />
                </label>
                <label>Latitudine
                    <input type="number" name="latitudine" value="<?php sanitizeData($lat)?>" />
                </label>
                <input type="hidden" name="id" value="<?php echo $id?>"/>
                <input type="hidden" name="page" value="<?php echo $page ?>"/>
                <input type="submit" name="submit" value="Save Entry"/>
                <input type="submit" name="submit" value="Cancel"/>
            </fieldset>
        </form>
    <?php endif; ?>
    </body>
</html>
    <?php else: ?>
<!-- If the admin is not logged in -->
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type"
          content="text/html;charset=utf-8" />
    <link rel="stylesheet"
          href="/css/stylesheet.css" type="text/css" />
    <title> Please Log In </title>
</head>
<body>
<form method="post"
      action="/inc/update.inc.php"
      enctype="multipart/form-data">
    <fieldset>
        <?php
        //Custom error messages for the login form
        $errorAdmin = false;
        if(isset($_SESSION['error'])){
          switch($_SESSION['error']){
            case 10:
                $errorAdmin = "Invalid username";
                break;
            case 11:
                $errorAdmin = "Invalid password";
                break;
            case 6:
                $errorAdmin = "Both fields are empty";
                break;
            default:
                $errorAdmin = false;
                break;
          }
            unset($_SESSION['error']);
        }?>
        <!-- Displays the admin error -->
        <?php if($errorAdmin): ?>
            <p class="error"><?php echo $errorAdmin; ?></p>
        <?php endif; ?>

        <legend>Please Log In To Continue</legend>
        <label>Username
            <input type="text" name="username" maxlength="75" />
        </label>
        <label>Password
            <input type="password" name="password" maxlength="150" />
        </label>
        <input type="hidden" name="action" value="login" />
        <input type="submit" name="submit" value="Log In" />
    </fieldset>
</form>
</body>
</html>
<?php endif; ?>
