<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );

//Catch Errors
Debug::register();

//Generate an errors
if( this_function_does_not_exists( $and_this_var_does_not_exists ) )
{
	return $and_this_var_does_not_exists;
}

?>
