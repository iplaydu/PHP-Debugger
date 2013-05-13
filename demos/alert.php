<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );

//SET ALERT
Debug::alert("This is my :text:", array(
	"text"	=>	"alert"
))
?>
