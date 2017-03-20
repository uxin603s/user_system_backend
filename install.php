<?php
$array=[
	['field'=>"base_path","message"=>"請設定路徑:(ex:http://example.com)不輸入會使用預設","default"=>"http://user.cfd888.info"],
	['field'=>"white_path","message"=>"請設定白名單IP:",],
	['field'=>"dbName","message"=>"請設定資料庫",],
	['field'=>"user","message"=>"請設定資料庫使用者",],
	['field'=>"password","message"=>"請設定資料庫密碼",],
	['field'=>"host","message"=>"請設定資料庫網址",],
	['field'=>"location","message"=>"是否為本機:",],
	['field'=>"FB.id","message"=>"fb_id:",],
	['field'=>"FB.secret","message"=>"fb_secret:",],
	['field'=>"Mcache.prefix","message"=>"Mcache.prefix:",],
];
$path=__DIR__."/config.json";
if(file_exists($path)){
	$data=json_decode(file_get_contents($path),1);
}
foreach($array as $value){
	$field=$value['field'];
	$message=$value['message'];
	if(!isset($data[$field])){
		echo "{$message}";
		$result=stream_get_line(STDIN, 1024, PHP_EOL);
		if(!$result){
			if(isset($value['default'])){
				$result=$value['default'];
			}
		}
		echo "\n";
		$data[$field]=$result;
	}
}

file_put_contents($path,json_encode($data));