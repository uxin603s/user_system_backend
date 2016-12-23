<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int','access_token'];
	use CRUD{
		CRUD::insert as tmp_insert;
		CRUD::update as tmp_update;
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
		do{
			$access_token=md5($arg['name'].time());
			$update=[
				'fb_id'=>$result['insert']['id'],
				'access_token'=>$access_token,
			];
			$where=[
				'id'=>$result['insert']['id'],
			];
			$update_result=self::update(compact(['update','where']));
		}while(!$update_result['status']);
		
		$result['insert']['access_token']=$access_token;
		$result['insert']['fb_id']=$result['insert']['id'];
		return $result;
	}
}