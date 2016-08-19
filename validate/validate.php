<?php

session_start();

require_once ('error_handler.php');
require_once ('validate.class.php');

$validator = new Validate();
$validationType = '';

if(isset($_POST['validationType'])){
    $validationType = $_POST['validationType'];
}
if($validationType == 'php'){
    header('Location:' . $validator->ValidatePHP());
} else {
    $response = array('result' => $validator->ValidateAJAX
    ($_POST['inputValue'],$_POST['fieldID']),
        'fieldID' => $_POST['fieldID'] );

    if(ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>