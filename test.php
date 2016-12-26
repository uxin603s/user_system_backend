<?php
function test(){
	DB::query("CREATE TABLE `test` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `user` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	  `pass` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	 PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	$insert=[
		'user'=>'test1',
		'pass'=>'pass1',
		'comment'=>'註解',
	];
	$id=DB::insert($insert,"test");
	if($id){
		echo "新增成功";
		var_dump($id);
	}else{
		echo "新增失敗";
	}
	$tmp=DB::select("select * from test where user = ? && pass= ?",['test1','pass1']);
	if($tmp){
		echo "查詢成功";
		var_dump($tmp);
	}else{
		echo "查詢失敗";
	}
	$update['user']="test2";
	$where['id']=1;
	$tmp=DB::update($update,$where,"test");
	if($tmp){
		echo "更新成功";
		var_dump($tmp);
	}else{
		echo "更新失敗";
	}
	$tmp=DB::delete($where,"test");
	if($tmp){
		echo "刪除成功";
		var_dump($tmp);
	}else{
		echo "刪除失敗";
	}

	DB::query("drop table test");
}