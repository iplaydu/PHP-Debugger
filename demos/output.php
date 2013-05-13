<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );

//Set output between HTML, JSON and TEXT
Debug::output('json')->printData( $_SERVER );
?>