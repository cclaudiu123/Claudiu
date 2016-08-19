<?php

require_once('error_handler.php');
header('Content-Type: text/xml');

$firstNumber = $_GET['firstNumber'];
$secondNumber = $_GET['secondNumber'];
$result = $firstNumber / $secondNumber;

$dom = new DOMDocument();

$response = $dom->createElement('response');
$dom->appendChild($response);

$responseText = $dom->createTextNode($result);
$response->appendChild($responseText);

$xmlString = $dom->saveXML();
echo $xmlString;

?>