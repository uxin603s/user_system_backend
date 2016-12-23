<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int','access_token'];
	use CRUD{
		CRUD::insert as tmp_insert;
		CRUD::update as tmp_update;
	}
	public static function flushCache(){
		$where_list=[
			['field'=>'status','type'=>0,'value'=>1],
		];
		
		$tmp=self::getList(compact(['where_list']));
		$UserList=[];
		$access_token=[];
		if($tmp['status']){	
			foreach($tmp['list'] as $item){
				$UserList[$item['id']]=[
					'id'=>$item['id'],
					'name'=>$item['name'],
				];
				$access_token[$item['access_token']]=$item;
			}
		}
		FlushCache::run("UserList",$UserList,1);
		FlushCache::run("access_token",$access_token,1);
	}
	public static function getAccessToken(){
		do{
			$access_token=md5(time());
			$where_list=[
				['field'=>'access_token','type'=>0,'value'=>$access_token],
			];
			$result=self::getList(compact(['where_list']));
		}while($result['status']);
		return $access_token;
	}
	// public static function update($arg){
		// if(isset($arg['update']['access_token']) && isset($arg['where']['id'])){
			// $access_token=md5($arg['where']['id'].time());
		// }
		// return self::tmp_update($arg);
	// }
	public static function insert($arg){
		$arg['status']=0;
		$arg['created_time_int']=time();
		
		$result=self::tmp_insert($arg);
		
		$access_token=getAccessToken();
		$update=[
			'fb_id'=>$result['insert']['id'],
			'access_token'=>$access_token,
		];
		$where=[
			'id'=>$result['insert']['id'],
		];
		$update_result=self::update(compact(['update','where']));
		
		
		$result['insert']['access_token']=$access_token;
		$result['insert']['fb_id']=$result['insert']['id'];
		return $result;
	}
}