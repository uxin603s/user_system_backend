<?php

$array=[
"dbName",
"user",
"password",
"host",
];
foreach($array as $field){
	echo "請設定{$field}:";
	$$field=stream_get_line(STDIN, 1024, PHP_EOL);
	echo "\n";
}

$path=__DIR__."/config.json";
if(file_exists($path)){
	$data=json_decode(file_get_contents($path),1);
}
foreach($array as $field){
	$data[$field]=$$field;
}
file_put_contents($path,json_encode($data));