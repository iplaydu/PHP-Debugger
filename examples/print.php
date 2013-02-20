<?php
include_once( dirname(dirname( __FILE__ )) . '/src/debugger.class.php');
$Debugger = new Debugger;


$Debugger->print( $_SERVER );
?>