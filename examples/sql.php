<?php
include_once( dirname(dirname( __FILE__ )) . '/src/Debugger.class.php');
$Debugger = new Debugger;



mysql_connect( 'localhost' , 'root' , '' );
mysql_select_db("test");
mysql_select_db("SELECT * FROM test.test LIMIT 3");

?>