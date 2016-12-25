<?php
class BlockList{
	public static function getList(){
		if($tmp=Mcache::where("login_try_time")){
			$list=[];
			foreach($tmp as $key=>$val){
				$ip=str_replace("login_try_time","",$key);
				if($count=Mcache::get("login_try_count".$ip)){
					if($val+30*60>time() && $count>=3){
						$lock_time=$val+30*60;
						$list[]=compact(["ip","lock_time"]);
					}
				}
			}
			$status=true;
		}else{
			$status=false;
		}
		return compact(["status","list"]);
	}
	public static function delete($arg){
		$status=Mcache::del("login_try_time".$arg['ip']);
		return compact(["status"]);
	}

}