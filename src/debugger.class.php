<?php
/**
*	Debugger Class
*
*	@author		Olaf Erlandsen C. [Olaf Erlandsen]
*	@author		olaftriskel@gmail.com
*
*	@package	Debugger
*	@copyright	Copyright 2012, Olaf Erlandsen
*	@copyright	Dual licensed under the MIT or GPL Version 2 licenses.
*	@copyright	http://www.othalsys.com/license
*	@version	0.1.4.6
*
*/
class Debugger
{
	private $version = '0.1.4.6';
	
	private	$events		=	array();
	private $zIndex		=	99;
	private $between	=	2;
	private $backtrace	=	false;
	private $eventBackTraceLevel = null;
	private $debug = true;
	private $start = 0;
	private $marker = 0;
	private $escapeCodes = array();
	private $className;
	
	public function __construct()
	{
		ini_set('display_errors',1);
		error_reporting(-1);
		ini_set('error_reporting',-1);
		if( !defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) )
		{define('DEBUG_BACKTRACE_IGNORE_ARGS',False);}
		if( !defined( 'E_ALL' ))
		{define('E_ALL',32767);}
		if(!defined('E_STRICT'))
		{define( 'E_STRICT' , 2048 );}
		register_shutdown_function	( array( $this , 'onShutdown' ) , true );
		set_exception_handler		( array( $this , 'onException' ) );
		set_error_handler			( array( $this , 'onError' ) , E_ALL  );
		
	}
	/**
	*	@method	null	__call( string $method , array $args )
	*	@param	string	$method
	*	@param	array	$args
	*/
	public function __call ( $method , $args )
	{
		$method = strtolower( $method );
		$method = str_replace('_','',$method);
		if( method_exists( $this , $method ) )
		{
			$this->eventBackTraceLevel(2);
			return call_user_func_array( array( $this ,$method ) , $args );
		}else{
			if(in_array($method,array('echo','print','printr','printdata','printdataarray','printvar','dump','dumpvar','vardump','result')))
			{
				$this->eventBackTraceLevel(3);
				return call_user_func_array( array( $this , 'eventPrintData' ) , $args );
			}
			else if(in_array($method,array('sql','mysql','sqlite','printsql','dumpsql','sqldump')))
			{
				$this->eventBackTraceLevel(3);
				return call_user_func_array( array( $this , 'eventSQL' ) , $args );
			}
			else if(in_array($method,array('events','spot','point','trigger','error','warning','notice')))
			{
				$this->eventBackTraceLevel(3);
				return call_user_func_array( array( $this , 'event' ) , $args );
			}
			else if(in_array($method,array('call','exec','execute','commands','cmd','tail','terminal','console')))
			{
				$this->eventBackTraceLevel(3);
				return call_user_func_array( array( $this , 'event' ) , $args );
			}else{
				$this->eventBackTraceLevel(1)->event( "Debugger method &quot;". htmlentities( $method ) ."&quot; dont exists." , "Debugger Error" );
			}
		}
	}
	/**
	*	@method	object	ignoreErrorCodes( mixed $errorNumber [ , mixed $... ] )
	*	@param	mixed	$errorNumber
	*/
	public function ignoreErrorCodes( )
	{
		$codes = func_get_args();
		if( count( $codes ) > 0 )
		{
			foreach( $codes AS $code )
			{
				if( is_numeric( $code ) OR is_string( $code ) OR is_float( $code ) )
				{
					$this->escapeCodes[] = $code;
				}else{
					$this->eventBackTraceLevel(2)->event( "Invalid code. Only text and numbers." , "Escape Code Error");
				}
			}
		}
	}
	public function debug( $mode = true)
	{
		$this->debug = ( $mode === true ) ? true : false ;
	}
	/**
	*	@method	object	eventSQL( string $query [ , mixed $error , [ mixed $errorNumber ] ] )
	*/
	public function eventSQL( $query , $error = null, $errorNumber = null )
	{
		if( !empty( $query ) )
		{
			$save = array(
				'type'		=>	"SQL Error". ( (is_numeric($errorNumber) ) ? " [".$errorNumber."]" :null ) ."",
				'message'	=>	$error,
				'query'		=>	$query,
				'number'	=>	$errorNumber,
				'file'		=>	null,
				'line'		=>	null,
				'time'		=>	time(),
			);
			if( is_numeric( $this->eventBackTraceLevel ) )
			{
				$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
				if( array_key_exists( $this->eventBackTraceLevel , $backtrace ) )
				{
					$save['file'] = $backtrace[ $this->eventBackTraceLevel ]['file'];
					$save['line'] = $backtrace[ $this->eventBackTraceLevel ]['line'];;
				}
			}
			$this->events[ md5( strval($query).strval($error).strval($errorNumber) ) ] = $save;
		}
		
	}
	public function className( $class )
	{
		if( is_object( $class ) AND !is_null( $class ) )
		{
			$this->className = get_class( $class );
		}else if( !empty( $class ) ){
			$this->className = $class;
		}
		return $this;
	}
	
	public function eventBackTraceLevel( $level = null )
	{
		if( is_numeric( $level ) )
		{
			$this->eventBackTraceLevel = $level;
		}
		return $this;
	}
	public function eventPrintData( $data , $title = null, $file = null , $line = null )
	{
		if( empty( $title ) )
		{
			$title = "Print Data";
		}
		$save = array(
			'type'		=>	htmlentities($title)." [". ( (is_null($data) ) ? "NULL" : strtoupper(gettype($data)) )."]",
			'message'	=>	null,
			'print'		=>	print_r($data,true),
			'number'	=>	null,
			'file'		=>	null,
			'line'		=>	null,
			'time'		=>	time(),
			'method'	=>	null,
			'class'		=>	null,
		);
		if( is_numeric( $this->eventBackTraceLevel ) )
		{
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
			if( array_key_exists( $this->eventBackTraceLevel , $backtrace ) )
			{
				$save['file'] = $backtrace[ $this->eventBackTraceLevel ]['file'];
				$save['line'] = $backtrace[ $this->eventBackTraceLevel ]['line'];;
			}
		}
		else if( !empty($file) AND is_numeric($line) )
		{
			$save['file'] = $file;
			$save['line'] = $line;
			
		}else{
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
			if( array_key_exists( 0 , $backtrace ) )
			{
				$save['file'] = $backtrace[0]['file'];
				$save['line'] = $backtrace[0]['line'];
				
				if( !empty( $this->className ) )
				{
					$save['class'] = $this->className;
				}else if( array_key_exists( 'class' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{	
					$save['class'] =  $backtrace[ $this->eventBackTraceLevel ]['class'];
				}
				
				if( array_key_exists( 'function' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{
					$save['function'] = $backtrace[$this->eventBackTraceLevel]['function'];
					if( $save['function'] == '__call' )
					{
						if( array_key_exists( ($this->eventBackTraceLevel+1) , $backtrace ))
						{
							$save['function'] = $backtrace[($this->eventBackTraceLevel+1)]['function'];
						}
					}
				}
			}
		}
		if( $this->backtrace === true )
		{
			$save['backtrace'] = null;
		}
		$this->events[ md5( strval($save['print']).strval($title).strval($file).strval($line) ) ] = $save;
		$this->_clear();
	}
	public function event( $message , $title = null, $file = null , $line = null )
	{
		if( empty( $title ) )
		{
			$title = "Event";
		}
		$save = array(
			'type'		=>	htmlentities($title),
			'message'	=>	$message,
			'number'	=>	null,
			'file'		=>	null,
			'line'		=>	null,
			'time'		=>	time(),
			'method'	=>	null,
			'class'		=>	null,
		);
		
		if( is_numeric( $this->eventBackTraceLevel ) )
		{
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
			if( array_key_exists( $this->eventBackTraceLevel , $backtrace ) )
			{
				$save['file'] = $backtrace[ $this->eventBackTraceLevel ]['file'];
				$save['line'] = $backtrace[ $this->eventBackTraceLevel ]['line'];
				
				if( !empty( $this->className ) )
				{
					$save['class'] = $this->className;
				}
				else if( array_key_exists( 'class' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{
					$save['class'] = $backtrace[ $this->eventBackTraceLevel ]['class'];
				}
				
				if( array_key_exists( 'function' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{
					$save['function'] = $backtrace[$this->eventBackTraceLevel]['function'];
					if( $save['function'] == '__call' )
					{
						if( array_key_exists( ($this->eventBackTraceLevel+1) , $backtrace ))
						{
							$save['function'] = $backtrace[($this->eventBackTraceLevel+1)]['function'];
						}
					}
				}
			}
		}
		else if( !empty($file) AND is_numeric($line) )
		{
			$save['file'] = $file;
			$save['line'] = $line;
		}
		if( $this->backtrace === true )
		{
			$save['backtrace'] = null;
		}
		$this->events[ md5( strval($message).strval($title).strval($file).strval($line) ) ] = $save;
		$this->_clear();
	}
	public function command(  $message = null , $commands , $show = false , &$reference  = null )
	{
		if( !empty( $commands ) )
		{
			$result= array();
			$exec = exec( $commands , $result);
			$reference = $result;
			
			if( $show === true )
			{
				$commands = preg_replace('/(\s+(\-[a-z0-9]+))/i',"\n\t$2",$commands);
				$commands = preg_replace('/\s+(\&+)/i',"\n$1",$commands);
				
				$save = array(
					'type'		=>	"Command Line",
					'message'	=>	$message,
					'commands'	=>	$commands,
					'result'	=>	$result,
					'number'	=>	null,
					'file'		=>	null,
					'line'		=>	null,
					'time'		=>	time(),
				);
				if( !is_numeric( $this->eventBackTraceLevel) )
				{
					$this->eventBackTraceLevel(0);
				}
				if( is_numeric( $this->eventBackTraceLevel ) )
				{
					$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
					if( array_key_exists( $this->eventBackTraceLevel , $backtrace ) )
					{
						$save['file'] = $backtrace[ $this->eventBackTraceLevel ]['file'];
						$save['line'] = $backtrace[ $this->eventBackTraceLevel ]['line'];;
					}
				}
				else if( !empty($file) AND is_numeric($line) )
				{
					$save['file'] = $file;
					$save['line'] = $line;
				}
				if( $this->backtrace === true )
				{
					$save['backtrace'] = null;
				}
				$this->events[ md5( strval($commands).strval($save['line']).strval($save['file']) ) ] = $save;
				$this->eventBackTraceLevel = null;
			}
			return $exec;
		}
		
		
		
	}
	private function onException( $Exception )
	{
		$save = array(
			'type'		=>	"Exception",
			'message'	=>	$Exception->getMessage(),
			'number'	=>	$Exception->getCode(),
			'file'		=>	$Exception->getFile(),
			'line'		=>	$Exception->getLine(),
			'time'		=>	time(),
			'method'	=>	null,
			'class'		=>	null,
		);
		
		if( is_numeric( $this->eventBackTraceLevel ) )
		{
			$backtrace = $Exception->getTrace();
			if( array_key_exists( $this->eventBackTraceLevel , $backtrace ) )
			{
				$save['file'] = $backtrace[ $this->eventBackTraceLevel ]['file'];
				$save['line'] = $backtrace[ $this->eventBackTraceLevel ]['line'];
				if( array_key_exists( 'class' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{
					$save['class'] = $backtrace[ $this->eventBackTraceLevel ]['class'];
				}
				if( array_key_exists( 'function' , $backtrace[ $this->eventBackTraceLevel ] ) )
				{
					$save['function'] = $backtrace[$this->eventBackTraceLevel]['function'];
					if( $save['function'] == '__call' )
					{
						if( array_key_exists( ($this->eventBackTraceLevel+1) , $backtrace ))
						{
							$save['function'] = $backtrace[($this->eventBackTraceLevel+1)]['function'];
						}
					}
				}
			}
		}
		else if( !empty($file) AND is_numeric($line) )
		{
			$save['file'] = $file;
			$save['line'] = $line;
		}
		if( $this->backtrace === true )
		{
			$save['backtrace'] = null;
		}
		$this->events[md5( $Exception->getFile() . $Exception->getLine() )] = $save;
		$this->eventBackTraceLevel = null;
	}
	private function onError( $number , $message , $file , $line , $context = null )
	{
		$save = array(
			'type'	=>	$this->errorType($number),
			'message'	=>	$message,
			'number'	=>	$number,
			'file'		=>	$file,
			'line'		=>	$line,
			'time'		=>	time(),
		);
		if( $this->backtrace === true )
		{
			$save['backtrace'] = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		}
		$this->events[ md5($file.$line) ] = $save;
		$this->eventBackTraceLevel = null;
	}
	public function errorType( $number )
	{
		switch( $number )
		{
			case E_ERROR:	return 'Fatal Error';break;
			case E_WARNING:	return 'Warning';break;
			case E_PARSE:	return 'Error in the parser';break;
			case E_NOTICE:	return 'Notice';break;
			case E_CORE_ERROR:	return 'Fatal error in the PHP installation';break;
			case E_CORE_WARNING:	return 'Warnings resulting form an error in the PHP installation';break;
			case E_COMPILE_ERROR:	return 'Fatal error that occurs when script is compiled';break;
			case E_USER_ERROR:	return 'Error generated by programmer\'s code';break;
			case E_USER_WARNING:	return 'Warning generated by programmer\'s code';break;
			case E_USER_NOTICE:	return "Notice generated by programmer's code";break;
			case E_STRICT:	return "Notices that occur during script run-time";break;
			case E_RECOVERABLE_ERROR:	return "Fatal error that the script may recover from";break;
			case E_ALL:	return "Generic error";break;
			default:return 'Unknown';break;
		}
	}
	private function extractLinesFromFile( $file , $line )
	{
		$linesFromFile = array();
		if( file_exists( $file ) AND is_file( $file ) )
		{
			if( is_readable( $file ) )
			{
				$file = file( $file );
				$limit = (count($file)-1);
				$this->start = $start = ($line-$this->between)-1;
				if( $start < 0 )
				{
					$this->start = $start = 0;
				}
				$end = ($this->between*2)+1;
				if( $end > (count( $file ) -1) XOR $end < 0 )
				{
					$end = (count( $file ));
				}
				$linesFromFile = array_slice($file,$start,$end);
				unset($file);
			}
		}
		return $linesFromFile;
	}
	public function onShutdown()
	{
		if( $this->debug === false )
		{
			return false;
		}
		
		$getError = error_get_last();
		if( is_array( $getError ) )
		{
			if( count( $getError ) > 0 )
			{
				$this->onError( $getError['type'] , $getError['message'], $getError['file'],$getError['line']);
			}
		}
		if( count($this->events) > 0 )
		{
			/**
			*	CSS
			*/
			echo '<style type="text/css">';
			echo '.debugger-container,.debugger-backdrop{margin:0;padding:0;font-family:arial,sans-serif,tahoma;font-size:12px;}';
			echo '.debugger-backdrop{z-index:3;width:100%;height:100%;padding:0;margin:0;position:fixed;top:0;left:0;display:block;background-color:#E5E5E5;opacity:0.7;}';
			echo '.debugger-container{width:72.7%;max-height:90%;min-height:1%;text-align:left;z-index:4;position:absolute;top:0.5%;left:13.7%;box-shadow:0 0 2px #999999;-moz-box-shadow:0 0 2px #999999;-webkit-box-shadow:0 0 2px #999999;}';
			echo '.debugger-container .debugger-heading{background-color: #597DA3;border: 1px solid;padding: 0px;font-weight: bold;border-color: #45688E #43658A;border-top-left-radius:2px;border-top-right-radius:2px;position:relative;}';
			echo '.debugger-container .debugger-heading .debugger-close{text-decoration: none;float: none;position: absolute;right: 0.5%;top: 0%;color: #9DB7D4;font-size: 215%;font-family: arial, sans-serif, tahoma;text-shadow: 1px 1px 0px #608AB9;}';
			echo '.debugger-container .debugger-heading .debugger-close:hover{color:#FFFFFF;}';
			echo '.debugger-container .debugger-heading b{border-top:1px solid #648CB7;display:block;padding:6px 10px 8px 10px;color:#FFFFFF;font-size:13px;padding-right:20px;}';
			echo '.debugger-container .debugger-heading b span{position:relative;color:#ADBDD1;}';
			echo '.debugger-container .debugger-heading b span sup{font-size:10px;position:absolute;top:0;left:105%;}';
			echo '.debugger-container .debugger-content{padding:5px 12px;margin:0;border:1px solid #999999;border-top:0;display:block;background-color:#FFFFFF;border-bottom-right-radius:2px;border-bottom-left-radius:2px;margin:0 auto;height:100%;padding-bottom:10px;}';
			echo '.debugger-container .debugger-content p{padding: 5px 7px;color: #777777;margin: 0;}';
			
			echo '.debugger-container .debugger-content .label{font-size:11.844px;font-weight:bold;line-height:14px;color: white;text-shadow:0 -1px 0 rgba(0,0,0,0.25);white-space:nowrap;vertical-align:baseline;background-color:#999;padding: 1px 4px 2px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;}';
			echo '.debugger-container .debugger-content .label.info{background-color: #3A87AD;}';
			echo '.debugger-container .debugger-content .label.important{background-color: #B94A48;}';
			echo '.debugger-container .debugger-content .fileinfo{font-style:italic;display:block;width:99.2%;margin:10px auto 0 auto;padding:7px 5px;border:1px solid rgba(0,0,0,0.05);background: #808f9e;background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIxJSIgc3RvcC1jb2xvcj0iIzgwOGY5ZSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiM2ZTg3YTAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);background: -moz-linear-gradient(top, #808f9e 1%, #6e87a0 100%);background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#808f9e), color-stop(100%,#6e87a0));background: -webkit-linear-gradient(top, #808f9e 1%,#6e87a0 100%);background: -o-linear-gradient(top, #808f9e 1%,#6e87a0 100%);background: -ms-linear-gradient(top, #808f9e 1%,#6e87a0 100%);background: linear-gradient(to bottom, #808f9e 1%,#6e87a0 100%);filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#808f9e\',endColorstr=\'#6e87a0\',GradientType=0);border-radius:3px;font-size:13px;color:#FFFFFF;text-shadow:0 -1px 0 rgba(0,0,0,0.25);}';
			echo '.debugger-container .debugger-content .debugger-lines{position:relative;background-color: #F7F7F9;margin: 0 auto;display: block;border: 1px solid rgba(0, 0, 0, 0.05);border-radius: 4px;padding: 5px;box-shadow: inset 40px 0 0 #FBFBFC, inset 41px 0 0 #ECECF0;-moz-box-shadow: inset 40px 0 0 #FBFBFC, inset 41px 0 0 #ECECF0;-webkit-box-shadow: inset 40px 0 0 #FBFBFC, inset 41px 0 0 #ECECF0;margin-top:30px;}';
			echo '.debugger-container .debugger-content .debugger-lines.separate{margin-bottom:20px;}';
			echo '.debugger-container .debugger-content .debugger-lines h3{margin:0;padding:0;position:absolute;top:-20px;color: #8686A4;font-size: 14px;font-weight: bold;}';
			echo '.debugger-container .debugger-content .debugger-lines pre{overflow:auto;margin:0;padding:0;}';
			echo '.debugger-container .debugger-content .debugger-lines pre ol{list-style:decimal;padding-left: 45px;position:relative;}';
			echo '.debugger-container .debugger-content .debugger-lines pre ol li{list-style:decimal;line-height: 18px;color: #7C7C7C;}';
			echo '.debugger-container .debugger-content .debugger-lines pre ol li:hover{background-color: #F3E2A2;color: #4F4F4F;}';
			echo '.debugger-container .debugger-content .debugger-lines pre ol li.marker{color: #FB1813;}';
			echo '.debugger-container .debugger-content .debugger-lines pre ol li.marker span{color:white;text-shadow:1px 1px 3px #B00000;font-size: 14px;background-color: #FD8F8C;padding: 3px 0;}';
			echo 'object,embed{z-index:0;position:relative;}';
			echo '</style>';
			/**
			*	HTML
			*/
			$couter = 0;
			foreach( $this->events AS $token => $event )
			{
				if( !in_array( $event['number'], $this->escapeCodes ) )
				{
					echo '<div class="debugger-container"'.( ( $couter == 0 ) ?' style="display:block"':' style="display:none"').'>';
						echo '<div class="debugger-heading">';
							echo '<b>'.$event['type'].' <span>'.((count( $this->events ) > 1)?" #" . ($couter+1):null).' - Debugger <sup>'. $this->version .'</sup></span></b>';
							echo '<a href="#" class="debugger-close">&times;</a>';
						echo '</div>';
						echo '<div class="debugger-content">';
						
							if( !empty( $event['message'] ) )
							{
								/**
								*	IMPORTANT([**TEXT**])
								*/
								$event['message'] = preg_replace( '/(\[\*\*([\w\d\s\W\D\S.]+)\*\*\])/i' ,'<span class="label important">$2</span>' , $event['message'] );
								/**
								*	INFO([*TEXT*])
								*/
								$event['message'] = preg_replace( '/(\[\*([\w\d\s\W\D\S.]+)\*\])/i' ,'<span class="label info">$2</span>' , $event['message'] );
								/**
								*	PRINT
								*/
								echo '<p>'.$event['message'].'</p>';
							}
							
							if( array_key_exists('query', $event ) )
							{
								echo '<div class="debugger-lines'.(( !is_null($event['file']) OR array_key_exists('print', $event ) )?' separate':null).'">';
									echo '<h3>SQL CODE</h3>';
									echo '<pre>';
										echo '<ol start="1">';
										foreach( preg_split('/(\n)/i',$event['query']) AS $line => $content )
										{
											echo '<li>'. htmlentities( $content ) .'</li>';
										}
										echo '</ol>';
									echo '</pre>';
								echo '</div>';
							}
							if( array_key_exists('print', $event ) )
							{
								echo '<div class="debugger-lines'.(( !is_null($event['file']) OR array_key_exists( 'tail' , $event ) )?' separate':null).'">';
									echo '<h3>Print Result</h3>';
									echo '<pre>';
										echo '<ol start="1">';
										foreach( preg_split('/(\n)/i',$event['print']) AS $line => $content )
										{
											echo '<li>'. htmlentities( $content ) .'</li>';
										}
										echo '</ol>';
									echo '</pre>';
								echo '</div>';
							}
							if( array_key_exists('commands', $event ) )
							{
								echo '<div class="debugger-lines'.(( !is_null($event['file']) OR array_key_exists('tail', $event )  )?' separate':null).'">';
									echo '<h3>Command Line</h3>';
									echo '<pre>';
										echo '<ol start="1">';
										foreach( preg_split('/(\n)/i',$event['commands']) AS $line => $content )
										{
											echo '<li>'. htmlentities( $content ) .'</li>';
										}
										echo '</ol>';
									echo '</pre>';
								echo '</div>';
								if( array_key_exists('result', $event ) )
								{
									echo '<div class="debugger-lines'.(( !is_null($event['file']) )?' separate':null).'">';
										echo '<h3>Command Result</h3>';
										echo '<pre>';
											echo '<ol start="1">';
											foreach( $event['result'] AS $line => $content )
											{
												echo '<li>'. htmlentities( $content ) .'</li>';
											}
											echo '</ol>';
										echo '</pre>';
									echo '</div>';
								}
							}
							if( !is_null($event['file']) )
							{
								echo '<div class="debugger-lines">';
									echo '<h3>PHP CODE</h3>';
									echo '<pre>';
										$lines= $this->extractLinesFromFile($event['file'],$event['line']);
										$line = ((( $event['line']-$this->between < 0 ) ? 1 : $event['line']-$this->between +2));
										echo '<ol start="'.($this->start+1).'">';
										$init = ($this->start+1);
										foreach( $lines AS $content )
										{
											if( $init == $event['line'] )
											{
												echo '<li class="marker"><span>'. htmlentities( $content ) .'</span></li>';
											}else{
												echo '<li>'. htmlentities( $content ) .'</li>';
											}
											$init++;
										}
										echo '</ol>';
									echo '</pre>';
								echo '</div>';
								
								$event['file'] = str_replace(array('\\','/'),'/',$event['file']);
								$event['file'] = str_ireplace(str_replace(array('\\','/'),'/',_SYSTEM_),'/',$event['file'] );								
								$event['file'] = preg_replace( '/([a-z0-9\-\_\.]+)$/' , '<strong style="font-style:normal;">$1</strong>' , $event['file'] );
								$event['file'] = $event['file'];
								
								echo '<span class="fileinfo">';
								echo 'In '.$event['file'].' on line <strong style="font-style:normal;">'.$event['line'].'</strong>';
								if( !is_null( $event['class'] ) )
								{
									echo ', Object <strong style="font-style:normal;">'. $event['class'].'</strong>';
									if( !is_null( $event['function'] ) )
									{
										echo ' and Method <strong style="font-style:normal;">'. $event['function'] .'</strong>';
									}
								}else if( !is_null( $event['function'] ) )
								{
									echo ' and Function <strong style="font-style:normal;">'.$event['function'] .'</strong>';
								}
								echo '</span>';
							}
						echo '</div>';
					echo '</div>';
					$couter++;
				}
			}
			if( $couter > 0 )
			{
				echo '<div class="debugger-backdrop" id="debugger-backdrop"></div>';
				/**
				*	jQuery and pure javascript support
				*/
				//echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>';
				//echo "<script>!window.jQuery && document.write('<script src=\"http://code.jquery.com/jquery-1.7.2.min.js\"><\/script>');</script>";
				
				echo '<script type="text/javascript">';
					echo "if(typeof jQuery!=='undefined')";
					echo "{";
						echo "jQuery(document).ready(function(){";
							echo "jQuery('.debugger-container .debugger-close').click(function(){";
								echo "var self = this;";
								echo "jQuery(self).parents('.debugger-container').fadeOut(250,function(){";
									echo "jQuery(this).remove();";
									echo "if(jQuery('.debugger-container').length==0)";
									echo "{";
										echo "jQuery('.debugger-backdrop').remove();";
									echo "}else{";
										echo "jQuery('.debugger-container').first().fadeIn(250);";
									echo "}";
								echo "});";
								echo "return false;";
							echo "});";
						echo "});";
					echo "}else{";
						echo "var elements=document.getElementsByTagName('a');";
						echo "for(var i=0;i<elements.length;i++){";
							echo "if((' '+(elements[i]).getAttribute('class')+' ').replace(/[\\n\\t]/g,' ').indexOf('debugger-close')>-1){";
							
								echo "if((elements[i])){(elements[i]).onclick=function(){";
									echo "var element=this;";
									echo "while(element){";
										echo "if((' '+element.parentNode.getAttribute('class')+' ').replace(/[\\n\\t]/g,' ').indexOf('debugger-container')>-1){";
											echo "(element.parentNode).parentNode.removeChild(element.parentNode);";
											echo "delete elements[i];var contents=document.getElementsByTagName('div');";
											echo "var count=0;";
											echo "for(var token=0;token<contents.length;token++){";
												echo "if((' '+(contents[token]).getAttribute('class')+' ').replace(/[\\n\\t]/g,' ').indexOf('debugger-container')>-1){";
													echo "count++;";
													echo "(contents[token]).removeAttribute('style');";
												echo "}";
											echo "}";
											echo "if(count==0){";
												echo "var backdrop=document.getElementById('debugger-backdrop');";
												echo "backdrop.parentNode.removeChild(backdrop);";
											echo "}";
											echo "break;";
										echo "}";
										echo "element=element.parentNode;";
									echo "};";
									echo "return false;";
								echo "};";
							echo "}";
						echo "}";
					echo "}";
					echo "}";
				echo '</script>';
			}
		}
	}
	private function _clear()
	{
		$this->className = null;
		$this->eventBackTraceLevel = null;
	}
}
?>