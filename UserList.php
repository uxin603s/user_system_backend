<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int','access_token'];
	use CRUD{
		CRUD::insert as tmp_insert;
		// CRUD::update as tmp_update;
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
		Cache::group_save("UserList",$UserList);
		Cache::group_save("access_token",$access_token);
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
		
		if($result=Cache::group_get_one("access_token",$access_token)){
			$result['rid']=[];
			if($tmp=Cache::group_get_one("UserRole",$result['id'])){
				$result['rid']=$tmp;
				$result['data']=[];
				$result['role']=[];
				$result['role_user']=[];
				$result['web']=[];
				foreach($result['rid'] as $rid){
					if($RoleData=Cache::group_get_one("RoleData",$rid)){
						$result['data'][$rid]=$RoleData;
					}
					if($RoleList=Cache::group_get_one("RoleList",$rid)){
						$result['role'][$rid]=$RoleList;
						if($WebList=Cache::group_get_one("WebList",$RoleList['wid'])){
							$result['web'][$rid][$RoleList['wid']]=$WebList;
						}
					}
					
					$RoleUser=[];
					if($tmp=Cache::group_get_one("RoleUser",$rid)){
						foreach($tmp as $uid){
							if($UserList=Cache::group_get_one("UserList",$uid)){
								$RoleUser[]=$UserList;
							}
						}
					}					
					$result['role_user'][$rid]=$RoleUser;
				}
				if(in_array(0,$result['rid'])){
					$result['role_user'][0]=Cache::group_get_all("UserList");
					$result['web'][0]=Cache::group_get_all("WebList");;
				}
			}
		}
		return $result;
	}
	public static function remember($access_token,$data){
		if($access_token){
			Fcache::set("userSystem_{$access_token}",$data);
		}
	}
	public static function reset_session(){
		$tmp_session_id=session_id();
		if($list=Fcache::where("userSystem_")){
			foreach($list as $val){
				$access_token=$val['access_token'];
				$session_id=$val['session_id'];
				session_id($session_id);
				session_start();
				$_SESSION=self::compactUser($access_token);
				$_SESSION['session_id']=$session_id;
				self::remember($access_token,$_SESSION);
				session_write_close();
			}
		}
		if($tmp_session_id){
			session_id($tmp_session_id);
			session_start();
			session_write_close();
		}
		file_get_contents("http://tag.cfd888.info/flush_auth.php");
		file_get_contents("http://fans.cfd888.info/flush_auth.php");
	}
	
}