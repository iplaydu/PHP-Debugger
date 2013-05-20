<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );

//Catch all
Debug::register();
Debug::debug( false );//true = show erros; false hide errors


//generate error "Use of undefined constant a - assumed 'a'"
a;

//Count errors
if( Debug::count() > 0 )
{
	echo "Sorry, this code have a error. ";
}else{
	echo "This code is beautiful!";
}


?>
