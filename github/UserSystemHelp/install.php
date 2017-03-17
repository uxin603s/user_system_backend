<?php
echo "請設定路徑:(ex:http://example.com)不輸入會使用預設";
$base_path=stream_get_line(STDIN, 1024, PHP_EOL);
echo "\n";
echo "請設定白名單IP:";
$white_path=stream_get_line(STDIN, 1024, PHP_EOL);
echo "\n";
if(!$base_path){
	$base_path="http://user.cfd888.info";
}
$path=__DIR__."/config.json";

if(file_exists($path)){
	$data=json_decode(file_get_contents($path),1);
}

$data["base_path"]=$base_path;
$data["white_path"]=$white_path;

file_put_contents(__DIR__."/config.json",json_encode($data));