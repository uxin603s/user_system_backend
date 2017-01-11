<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int','access_token'];
	public static $cache_key_field=['id','access_token','fb_id','status'];
	use CRUD{
		CRUD::flushCache as private tmp_flushCache;	
	}
	public static function flushCache(){
		self::tmp_flushCache();
		// UserList::reset_session();
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
		
		if($tmp=UserList::getCache(["where"=>["access_token"=>$access_token,'status'=>1]])){
			
			$result=array_pop($tmp);
			$result['rid']=[];
			if($UserRole=UserRole::getCache(["where"=>["uid"=>$result['id']]])){
				$result['rid']=array_column($UserRole,'rid');
				$result['data']=[];
				$result['role']=[];
				$result['role_user']=[];
				$result['web']=[];
				
				foreach($result['rid'] as $rid){
					
					if($RoleData=RoleData::getCache(["where"=>['rid'=>$rid]])){
						$result['data'][$rid]=$RoleData;
					}
					
					if($RoleList=RoleList::getCache(["where"=>['id'=>$rid]])){
						$result['role'][$rid]=array_pop($RoleList);
						if($WebList=WebList::getCache(["where"=>['id'=>$result['role'][$rid]['wid']]])){
							$result['web'][$rid][$result['role'][$rid]['wid']]=$WebList;
						}
					}
					
					$RoleUser=[];
					if($UserRole=UserRole::getCache(["where"=>['rid'=>$rid]])){
						foreach($UserRole as $item){
							if($UserList=UserList::getCache(["where"=>["id"=>$item['uid']]])){
								$tmp=array_pop($UserList);
								$RoleUser[]=[
									"id"=>$tmp['id'],
									"name"=>$tmp['name'],
								];
							}
						}
					}
					$result['role_user'][$rid]=$RoleUser;
				}
				
				if(in_array(0,$result['rid'])){
					$result['role'][0]=['id'=>0,'name'=>"最高權限"];
					$result['role_user'][0]=array_column(UserList::getCache(),"name","id");
					$result['web'][0]=WebList::getCache();
					ksort($result['role_user'][0]);
				}
				
			}
		}
		return $result;
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
				Fcache::set("userSystem_{$access_token}",$data);
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