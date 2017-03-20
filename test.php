<?php
include_once __DIR__."/include.php";

$where_list=[
	['field'=>'id','type'=>0,'value'=>9],
];
$result=UserList::getList(compact(['where_list']));


foreach($result['list'] as $val){
	var_dump(UserList::compactUser($val['access_token']));
	exit;
}