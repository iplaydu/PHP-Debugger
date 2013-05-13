<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );


//Set lines
$lines = 10;
Debug::lines( $lines );
/*   1
*	2
*	3
*	4
*	5
*	6
*	7
*	8
*	9
*	10 */
Debug::alert( "Show beetwen :lines: lines", array( 'lines' => $lines ));
/*   1
*	2
*	3
*	4
*	5
*	6
*	7
*	8
*	9
*	10 */

?>