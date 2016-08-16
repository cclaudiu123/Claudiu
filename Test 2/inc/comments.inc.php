<?php
include_once 'db.inc.php';

/**
 * Class Comments
 */
class Comments
{
    public $db;
    public $comments;

    /**
     * Comments constructor.
     */
    public function __construct()
    {
        $this->db = new PDO(DB_INFO, DB_USER, DB_PASS);
    }

    /**
     * Displays the comment form
     * @param $blog_id
     * @return string
     */
    public function showCommentForm($blog_id)
    {
        $errors = array(
            1 => '<p class="error">Something went wrong while'
            . ' saving your comment. Please try again!</p>',
            2 => '<p class="error">Please provide a valid'
            . ' email address!</p>',
            3 => '<p class="error">Please answer the anti-spam'
            . ' question correctly!</p>',
            4 => '<p class="error">Please provide a name!</p>',
            5 => '<p class="error">Please provide a comment!</p>'
        );

        if(isset($_SESSION['error'])){
            $error = $errors[$_SESSION['error']];
        } else {
            $error = NULL;
        }

        if(isset($_SESSION['c_name'])){
            $n = $_SESSION['c_name'];
        } else {
            $n = NULL;
        }

        if(isset($_SESSION['c_email'])){
            $e = $_SESSION['c_email'];
        } else {
            $e = NULL;
        }

        if(isset($_SESSION['c_comment'])){
            $c = $_SESSION['c_comment'];
        } else {
            $c = NULL;
        }

        if(!$this->verifyName($_SESSION['c_name'])){
            $classN = "errorfield";
        }
        if(!$this->validateEmail($_SESSION['c_email'])){
            $classE = "errorfield";
        }
        if(!$this->verifyComment($_SESSION['c_comment'])){
            $classC = "errorfield";
        }

        $challenge = $this->generateChallenge();


        return <<<FORM
<form action="/inc/update.inc.php" method="post" id="comment-form">
<fieldset>
    <legend>Post a Comment</legend>$error
    <label>Name
        <input class="$classN" type="text" name="name" maxlength="75" value="$n"/>
    </label>
    <label>Email
        <input class="$classE" type="text" name="email" maxlength="150" value="$e"/>
    </label>
    <label>Comment
        <textarea class="$classC" rows="10" cols="45" name="comment">$c</textarea>
    </label>$challenge
    <input type="hidden" name="blog_id" value="$blog_id" />
    <input type="submit" name="submit" value="Post Comment" />
    <input type="submit" name="submit" value="Cancel" />
</fieldset>
</form>
FORM;
    }

    /**
     * Saves the comment
     * @param $p
     */
    public function saveComment($p)
    {
        $_SESSION['c_name'] = htmlentities($p['name'], ENT_QUOTES);
        $_SESSION['c_email'] = htmlentities($p['email'], ENT_QUOTES);
        $_SESSION['c_comment'] = htmlentities($p['cmnt'], ENT_QUOTES);
        //Displays an error if email is not a valid
        if($this->validateEmail($p['email']) === FALSE){
            $_SESSION['error'] = 2;
            return;
        }
        //Displays an error if the challange is not answered correct
        if(!$this->verifyResponse($p['s_q'], $p['s_1'], $p['s_2'])){
            $_SESSION['error'] = 3;
            return;
        }
        //Displays an error if name field is empty
        if($this->verifyName($p['name']) === FALSE){
            $_SESSION['error'] = 4;
            return;
        }
        //Displays an error if comment field is empty
        if($this->verifyComment($p['comment']) === FALSE){
            $_SESSION['error'] = 5;
            return;
        }

        $blog_id = htmlentities(strip_tags($p['blog_id']), ENT_QUOTES);
        $name = htmlentities(strip_tags($p['name']), ENT_QUOTES);
        $email = htmlentities(strip_tags($p['email']), ENT_QUOTES);
        $comment = htmlentities(strip_tags($p['comment']), ENT_QUOTES);
        //Removes the whitespace from comments
        $comment = nl2br(trim($comment));

        $sql = "INSERT INTO comments (blog_id, name, email, comment)
                VALUES (?, ?, ?, ?)";

        if($stmt = $this->db->prepare($sql)){
            $stmt->execute(array($blog_id, $name, $email, $comment));
            $stmt->closeCursor();

            unset(
                $_SESSION['c_name'], $_SESSION['c_email'],
                $_SESSION['c_comment'], $_SESSION['error']
            );

        } else {
            $_SESSION['error'] = 1;
        } return;
    }

    /**
     * Retrieves the comments from the database
     * @param $blog_id
     */
    public function retrieveComments($blog_id)
    {
      $sql = "SELECT id, name, email, comment, date
              FROM comments
              WHERE blog_id=?
              ORDER BY date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($blog_id));

        while($comment = $stmt->fetch()){
            $this->comments[] = $comment;
        }

        if(empty($this->comments)){
            $this->comments[] = array(
                'id'      => NULL,
                'name'    => NULL,
                'email'   => NULL,
                'comment' => "There are no comments on this entry.",
                'date'    => NULL
            );
        }
    }

    /**
     * Displays the comments
     * @param $blog_id
     * @return null|string
     */
    public function showComments($blog_id)
    {
        $display = NULL;
        $this->retrieveComments($blog_id);

        foreach($this->comments as $c){
            //Formats the date and name
            if(!empty($c['date']) && !empty($c['name'])){
                $format = "F j, Y \a\\t g:iA";
                $date = date($format, strtotime($c['date']));
                $byline = "<span><strong>$c[name]</strong>
                            [Posted on $date]</span>";
                //If the admin is logged displays the delete comments button
                if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1){
                $admin = "<a href=\"/inc/update.inc.php"
                    . "?action=comment_delete&id=$c[id]\""
                    . "class=\"admin\">delete</a>";
                } else {
                    $admin = NULL;
                }
            } else {
                $byline = NULL;
                $admin = NULL;
            }
            $display .= "<p class=\comment\">$byline$c[comment]$admin</p>";
        }

        return $display;
    }

    /**
     * Displays a delete confirmation form for the comments
     * @param $id
     * @return string
     */
    public function confirmDelete($id)
    {
        if(isset($_SERVER['HTTP_REFERER'])){
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = '../';
        }
    return <<<FORM
<html>
<head>
<title>Please Confirm Your Decision</title>
<link rel="stylesheet" type="text/css" href="/css/stylesheet.css" />
</head>
<body>
<form action="/inc/update.inc.php" method="post">
    <fieldset>
        <legend>Are You Sure?</legend>
        <p>
            Are you sure you want to delete this comment?
        </p>
        <input type="hidden" name="id" value="$id" />
        <input type="hidden" name="action" value="comment_delete" />
        <input type="hidden" name="url" value="$url" />
        <input type="submit" name="confirm" value="Yes" />
        <input type="submit" name="confirm" value="No" />
    </fieldset>
</form>
</body>
</html>
FORM;
    }

    /**
     * Deletes the comment from the database
     * @param $id
     * @return bool
     */
    public function deleteComment($id)
    {
        $sql = "DELETE FROM comments
                WHERE id=?
                LIMIT 1";
            if($stmt = $this->db->prepare($sql)){
               $stmt->execute(array($id));
               $stmt->closeCursor();

               return TRUE;
            } else {
               return FALSE;
            }
    }

    /**
     * Verifies if the email has a valid form
     * @param $email
     * @return bool
     */
    public function validateEmail($email)
    {
        $p = '/^[\w-]+(\.[\w-]+)*@[a-z0-9-]+'
            . '(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';

        return (preg_match($p, $email)) ? TRUE : FALSE;
    }

    /**
     * Creates the spam protection challenge
     * @return string
     */
    private function generateChallenge()
    {
        $numbers = array(mt_rand(1,4), mt_rand(1,4));
        $_SESSION['challenge'] = $numbers[0] + $numbers[1];
        $converted = array_map('ord', $numbers);

        return"
        <label>&#87;&#104;&#97;&#116;&#32;&#105;&#115;&#32;
                &#$converted[0];&#32;&#43;&#32;&#$converted[1];&#63;
                <input type=\"text\" name=\"s_q\" />
        </label>";
    }

    /**
     * Verifies if the challenge was answered correct
     * @param $resp
     * @return bool
     */
    private function verifyResponse($resp)
    {
        $val = $_SESSION['challenge'];
        unset($_SESSION['challenge']);

        return $resp == $val;
    }

    /**
     * Verifies if a name was inserted in the text field
     * @param $name
     * @return bool
     */
    public function verifyName($name)
    {
        if(!empty($name)){
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Verifies if a comment was inserted in the text field
     * @param $comment
     * @return bool
     */
    public function verifyComment($comment)
    {
        if(!empty($comment)){
            return TRUE;
        } else {
            return FALSE;
        }
    }
}