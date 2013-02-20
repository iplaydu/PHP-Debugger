<?php
include_once( dirname(dirname( __FILE__ )) . '/src/Debugger.class.php');
$Debugger = new Debugger;

if( array_key_exists( 'debug' , $_GET ) )
{
	$Debugger->debug( true);
}else{
	$Debugger->debug( false );
}

$Debugger->print( $_SERVER );

?>
<a href="mode.php?debug">Show Debug</a>
<a href="mode.php">Hide Debug</a>