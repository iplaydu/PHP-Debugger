<?php
include( dirname(dirname(__FILE__))  . '/src/Debug.php' );


//Create classes
class ParentClass
{
	protected function name( $level )
	{
		//Set level backtrace( 0 = current, 1 = before this, ... )
		Debug::level( $level )->alert("This is my alert on level :level: on method :method: on class :class:",array(
			'level'	=>	$level,
			'class'	=>	get_class( $this ),
			'method'	=>	__METHOD__,
		));
	}
}

class ChildrenClass extends ParentClass
{
	public function __construct( $level )
	{
		echo $this->name( $level );
	}
}

//Start alert with level
$child = new ChildrenClass( 1 );
?>