Debug PHP
=============

A complete php debugger class, with support for Exception, Errors, Alerts( from user ), code lines and highlight flags.


Author	:	Olaf Erlandsen

Contact	:	olaftriskel@gmail.com

Version	:	1.0

Date	:	13.05.2013

license	:	http://opensource.org/licenses/GPL-3.0




Example #1: Simple debug
------------
```php
    <?php
    	include( dirname(dirname(__FILE__))  . '/src/Debug.php' );
		//Catch all
		Debug::register();
		
		//Generate an errors
		if( this_function_does_not_exists( ) )
		{
			return false;
		}
    ?>
```
