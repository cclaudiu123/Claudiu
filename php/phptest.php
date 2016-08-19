<?php

header('Content-Type: text/xml');
$dom = new DOMDocument();

$response = $dom->createElement('response');
$dom->appendChild($response);

$books = $dom->createElement('books');
$response->appendChild($books);

$title = $dom->createElement('title');
$titleText = $dom->createTextNode ('AJAX and PHP: Building Modern Web Applications, 2nd Ed');
$title->appendChild($titleText);

$isbn = $dom->createElement('isbn');
$isbnText = $dom->createTextNode('978-1904817726');
$isbn->appendChild($isbnText);

$book = $dom->createElement('book');
$book->appendChild($title);
$book->appendChild($isbn);
$books->appendChild($book);

$xmlString = $dom->saveXML();
echo $xmlString;

?>