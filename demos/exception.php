<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );
//Catch
Debug::register();

//Create a Exception
throw new Exception( "Hi, i am a Exception PHP" );
?>
