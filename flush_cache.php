<?php
include_once __DIR__."/include.php";
//快取初始化


// UserList::flushCache();
// UserRole::flushCache();
// RoleList::flushCache();
// RoleData::flushCache();
// DataList::flushCache();
// WebList::flushCache();
// FbRegisterList::flushCache();
// UserList::reset_session();


$list=UserList::getCache(['id'=>9]);
// var_dump($list);
// exit;	
foreach($list as $val){	
	$result=UserList::compactUser($val['access_token']);
	var_dump($result);	
	exit;
}

// Cache::del_all();







