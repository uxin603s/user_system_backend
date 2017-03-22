<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=[
		'id',
		'name',
		'status',
		'fb_id',
		'created_time_int',
		'access_token'
	];
	use CRUD{
		CRUD::insert as tmp_insert;
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
	public static function resetAccessToken($arg){
		$update=[];
		$update['access_token']=self::getAccessToken();
		$where=$arg;
		return self::update(compact(['update','where']));
	}
	
	public static function insert($arg){
		$arg['status']="0";
		$arg['created_time_int']=time();
		$arg['access_token']=self::getAccessToken();
		return self::tmp_insert($arg);
	}
	public static function compactUser($access_token){
		$result=false;
		$where_list=[
			["field"=>"access_token","type"=>0,"value"=>$access_token],
			["field"=>"status","type"=>0,"value"=>1],
		];
		$UserList=UserList::getList(compact(['where_list']));
		if($UserList['status']){
			$result=$UserList['list'][0];
			$result['rid']=[];
			$where_list=[
				["field"=>"uid","type"=>0,"value"=>$result['id']],
			];
			$UserRole=UserRole::getList(compact(['where_list']));
			
			if($UserRole['status']){
				$result['rid']=array_column($UserRole['list'],'rid');
				$where_list=[];
				foreach($result['rid'] as $rid){
					$where_list[]=["field"=>"rid","type"=>0,"value"=>$rid];
				}
				$RoleData=RoleData::getList(compact(['where_list']));
				if($RoleData['status']){
					$result['data']=[];
					foreach($RoleData['list'] as $value){
						$rid=$value['rid'];
						$result['data'][$rid]=$value;
					}
				}
				return $result;
			}
		}
		return $result;
	}
	
	public static function reset_session(){
		$tmp_session_id=session_id();
		$tmp_remote_addr=$_SERVER['REMOTE_ADDR'];
		UserSystemHelp::flushData();
		
		if($tmp_session_id){//還原
			session_id($tmp_session_id);
			$_SERVER['REMOTE_ADDR']=$tmp_remote_addr;
			session_start();
			session_write_close();
		}
		
		ob_start();
		//刷新網站
		system("/usr/bin/nohup /usr/bin/curl http://tag.cfd888.info/flush_auth.php > /dev/null 2>&1 & ");
		system("/usr/bin/nohup /usr/bin/curl http://fans.cfd888.info/flush_auth.php > /dev/null 2>&1 & ");
		ob_get_contents();
	}
	
}