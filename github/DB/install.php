<?php

$array=[
"dbName",
"user",
"password",
"host",
];
foreach($array as $val){
	echo "請設定{$val}:";
	$$val=stream_get_line(STDIN, 1024, PHP_EOL);
	echo "\n";
}

$data=compact($array);
var_dump($data);
file_put_contents(__DIR__."/config.json",json_encode($data));