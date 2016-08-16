<?php

class ToyRobot
{
    private $_name;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function writeName()
    {
        echo 'My name is', $this->_name, '.<br />';
    }
}
$tom = ToyRobot("Tom");
$tom->writeName();

$jim = ToyRobot("Jim");
$jim->writeName();

?>