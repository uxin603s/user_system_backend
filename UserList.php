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
		$status=true;
		return compact(["status","access_token"]);
	}
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
		if($list=Fcache::where("userSystem_")){
			foreach($list as $val){
				$access_token=$val['access_token'];
				$old_data=Fcache::get("userSystem_{$access_token}");
				$session_id=$val['session_id'];
				session_id($session_id);
				session_start();
				$_SESSION=self::compactUser($access_token);
				$_SESSION['session_id']=$session_id;
				$_SESSION['REMOTE_ADDR']=$old_data['REMOTE_ADDR'];
				Fcache::set("userSystem_{$access_token}",$_SESSION);
				session_write_close();
			}
		}
		
		if($tmp_session_id){
			session_id($tmp_session_id);
			session_start();
			session_write_close();
		}
		
		ob_start();
		system("/usr/bin/nohup /usr/bin/curl http://tag.cfd888.info/flush_auth.php > /dev/null 2>&1 & ");
		system("/usr/bin/nohup /usr/bin/curl http://fans.cfd888.info/flush_auth.php > /dev/null 2>&1 & ");
		ob_get_contents();
	}
	
}