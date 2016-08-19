<?php
require_once ('config.php');

class Validate
{
    private $mMysqli;

    function __construct()
    {
        $this->mMysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    }
    function __destruct()
    {
        $this->mMysqli->close();
    }

    public function ValidateAJAX($inputValue, $fieldID)
    {
        switch($fieldID){
            case 'txtUsername':
                return $this->validateUserName($inputValue);
            break;
            case 'txtName':
                return $this->validateName($inputValue);
            break;
            case 'selGender':
                return $this->validateGender($inputValue);
            break;
            case 'selBthMonth':
                return $this->validateBirthMonth($inputValue);
            break;
            case 'txtBthDay':
                return $this->validateBirthDay($inputValue);
            break;
            case 'txtBthYear':
                return $this->validateBirthYear($inputValue);
            break;
            case 'txtEmail':
                return $this->validateEmail($inputValue);
            break;
            case 'txtPhone':
                return $this->validatePhone($inputValue);
            break;
            case 'chkReadTerms':
                return $this->validateReadTerms($inputValue);
            break;
        }
    }

    public function ValidatePHP()
    {
        $errorsExist = 0;
        if(isset($_SESSION['errors']))
            unset($_SESSION['errors']);

        $_SESSION['errors']['txtUsername'] = 'hidden';
        $_SESSION['errors']['txtName'] = 'hidden';
        $_SESSION['errors']['selGender'] = 'hidden';
        $_SESSION['errors']['selBthMonth'] = 'hidden';
        $_SESSION['errors']['txtBthDay'] = 'hidden';
        $_SESSION['errors']['txtBthYear'] = 'hidden';
        $_SESSION['errors']['txtEmail'] = 'hidden';
        $_SESSION['errors']['txtPhone'] = 'hidden';
        $_SESSION['errors']['chkReadTerms'] = 'hidden';

        if(!$this->validateUsername($_POST['txtUsername'])){
            $_SESSION['errors']['txtUsername'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validateName($_POST['txtName'])){
            $_SESSION['errors']['txtName'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validateGender($_POST['selGender'])) {
            $_SESSION['errors']['selGender'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validateBirthMonth($_POST['selBthMonth'])){
            $_SESSION['errors']['selBthMonth'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validateBirthDay($_POST['txtBthDay'])){
            $_SESSION['errors']['txtBthDay'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validateBirthYear($_POST['selBthMonth'] . '#' .
                                     $_POST['txtBthDay'] . '#' .
                                     $_POST['txtBthYear'])){
            $_SESSION['errors']['txtBthYear'] = 'errors';
            $errorsExist = 1;
        }
        if(!$this->validateEmail($_POST['txtEmail'])){
            $_SESSION['errors']['txtEmail'] = 'error';
            $errorsExist = 1;
        }
        if(!$this->validatePhone($_POST['txtPhone'])){
            $_SESSION['errors']['txtPhone'] = 'error';
            $errorsExist = 1;
        }
        if(!isset($_POST['chkReadTerms']) ||
            !$this->validateReadTerms($_POST['chkReadTerms'])){
            $_SESSION['errors']['chkReadTerms'] = 'error';
            $_SESSION['values']['chkReadTerms'] = '';
            $errorsExist = 1;
        }
        if($errorsExist == 0){
            return 'allok.php';
        } else {
            foreach($_POST as $key => $value){
                $_SESSION['values']['key'] = $_POST[$key];
            }
            return 'index.php';
        }
    }

    private function validateUserName($value)
    {
        $value = $this->mMysqli->real_escape_string(trim($value));

        if($value = null)
            return 0;

        $query = $this->mMysqli->query('SELECT user_name FROM users
                                        WHERE user_name="' . $value . '"');

        if($this->mMysqli->affected_rows > 0) {
            return '0';
        } else {
            return '1';
        }

    }

    private function validateName($value)
    {
        $value = trim($value);

        if($value){
            return 1;
        } else {
            return 0;
        }
    }

    private function validateGender($value)
    {
        return ($value == '0') ? 0 : 1;
    }

    private function validateBirthMonth($value)
    {
        return ($value == '' || $value > 12 || $value < 1) ? 0 : 1;
    }

    private function validateBirthDay($value)
    {
        return ($value == '' || $value > 31 || $value < 1) ? 0 : 1;
    }

    private function validateBirthYear($value)
    {
        $date = explode('#', $value);
        if(!$date[0])
            return 0;
        if(!$date[1] || !is_numeric($date[1]))
            return 0;
        if(!$date[2] || !is_numeric($date[2]))
            return 0;
        return(checkdate($date[0], $date[1], $date[2])) ? 1 : 0;
    }

    private function validateEmail($value)
    {
        $p = '/^[\w-]+(\.[\w-]+)*@[a-z0-9-]+'
            .'(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';
        return (preg_match($p, $value)) ? TRUE : FALSE;
    }

    private function validatePhone($value)
    {
        return (!preg_match('/^[0-9]{3}-*[0-9]{3}-*[0-9]{4}$/',
            $value)) ? 0 : 1;
    }

    private function validateReadTerms($value)
    {
        return ($value == 'true' || $value == 'on') ? 1 : 0;
    }

}
?>