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
$data=compact(["base_path","white_path"]);
var_dump($data);
file_put_contents(__DIR__."/config.json",json_encode($data));