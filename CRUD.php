<?php
trait CRUD{
	
	public static function filter_field($list){
		if($list && is_array($list)){
			foreach($list as $field=>$value){
				if(isset($value['field'])){
					if(!in_array($value['field'],self::$filter_field_arr)){
						unset($list[$field]);
					}
				}else{
					if(is_numeric($field)){
						if(!in_array($value,self::$filter_field_arr)){
							unset($list[$field]);
						}
					}else{
						if(!in_array($field,self::$filter_field_arr)){
							unset($list[$field]);
						}
					}
				}
			}
		}
		return $list;
	}
	public static function getList($arg=null){
		$bind_data=[];		
		$select_str_arr=[];
		if($arg['select_list']){
			$arg['select_list']=self::filter_field($arg['select_list']);
			if(is_array($arg['select_list']) && count($arg['select_list'])){
				foreach($arg['select_list'] as $item){
					$select_str_arr[]=$item;
				}
			}
		}
		if($arg['count_select_list']){
			$arg['count_select_list']=self::filter_field($arg['count_select_list']);
			if(is_array($arg['count_select_list']) && count($arg['count_select_list'])){
				foreach($arg['count_select_list'] as $item){
					$select_str_arr[]="count(".$item.")";
				}
			}
		}
		if(count($select_str_arr)){
			$select_str=implode(",",$select_str_arr);
		}else{
			$select_str="*";
		}
		$where_str=MysqlCompact::where(self::filter_field($arg['where_list']),$bind_data);
		$order_str=MysqlCompact::order(self::filter_field($arg['order_list']));
		$group_str=MysqlCompact::group(self::filter_field($arg['group_list']));
		$have_str=MysqlCompact::have(self::filter_field($arg['have_list']),$bind_data);
		
		$limit_str=MysqlCompact::limit($arg['limit']);
		
		$sql="select {$select_str} from ".self::$table;
		$sql.=$where_str;
		$sql.=$order_str;
		$sql.=$group_str;
		$sql.=$have_str;
		$sql.=$limit_str;
		
		if($tmp=DB::select($sql,$bind_data)){
			$status=true;
			$list=$tmp;			
		}else{
			$status=false;
		}
		if(!$arg['not_count_flag'] && $limit_str){
			$count_sql="select count(*) count from ".self::$table;
			$count_sql.=$where_str;
			$count_sql.=$order_str;
			$count_sql.=$group_str;
			$count_sql.=$have_str;
			if($tmp=DB::select($count_sql,$bind_data)){
				$total_count=$tmp[0]['count'];
				$total_page=ceil($total_count/$arg['limit']['count']);
			}
		}
		return compact(['status','list','sql','bind_data','total_page','total_count','arg']);
	}
	
	public static function insert($insert){
		
		$insert=self::filter_field($insert);
		if($id=DB::insert($insert,self::$table)){
			if(is_numeric($id)){
				$insert['id']=$id;
			}
			$status=true;
			$message="新增成功";
			self::flushCache($insert,1);
		}else{
			$status=false;
			$message="新增失敗";
		}
		return compact(['status','message','insert']);
	}
	public static function update($arg){
		$update=self::filter_field($arg['update']);
		$where=self::filter_field($arg['where']);
		
		//欄位案權限 再過濾一次
		if(DB::update($update,$where,self::$table)){
			$status=true;
			$message="修改成功";
			self::flushCache(compact(['update','where']),2);
		}else{
			$status=false;
			$message="修改失敗";
		}
		return compact(['status','message','arg','update','where']);
	}
	public static function delete($where){
		$where=self::filter_field($where);
		if(DB::delete($where,self::$table)){
			$status=true;
			$message="刪除成功";
			self::flushCache(compact(['where']),3);
		}else{
			$status=false;
			$message="刪除失敗";
		}
		return compact(['status','message']);
	}
	public static function getCache($where=[],$not_where=[]){
		$query_field=self::$cache_key_field;
		if(!is_array($query_field))return false;
		$preg_arr=[__CLASS__];
		foreach($query_field as $field){
			$preg_arr[]=$field;
			
			if(!is_array($where[$field])){
				if(isset($where[$field])){
					$preg_arr[]=$where[$field];
				}else{
					$preg_arr[]="(?P<{$field}>[\w]+?)";
				}
			}else{
				$preg_arr[]="(?P<{$field}>[\w]+?)";
			}
			if(!is_array($where[$field])){
				unset($where[$field]);
			}
			if(!is_array($not_where[$field])){
				unset($not_where[$field]);
			}
		}
		$preg=implode("\.",$preg_arr);
		$preg="/{$preg}/";
		
		$count=Fcache::get(__CLASS__.".index_page");
		
		$result=[];
		for($page=0;$page<$count;$page++){
			$index_page=Fcache::get(__CLASS__.".index_page.{$page}");
			if($index_page)
			foreach($index_page as $key_name){
				if(preg_match($preg,$key_name,$match)){
					if($match){
						foreach($not_where as $field=>$array){
							if($match[$field] && in_array($match[$field],$array)){
								continue 2;
							}
						}
						foreach($where as $field=>$array){
							if($match[$field] && !in_array($match[$field],$array)){
								continue 2;
							}
						}
					}
					
					if($value=Cache::get($key_name,30*60)){
						$result[$key_name]=$value;
					}
				}
			}
		}
		return $result;
	}
	
	public static function flushCache($arg=[],$type=0){
		if(is_array(self::$cache_key_field)){
			$query_field=self::$cache_key_field;
		}else{
			return false;
		}
		Fcache::lock(__CLASS__.".index_page");
		
		switch($type){
			case 0://init
				self::flushIndex([],2);
				
				$page=0;
				$count=500;
				while(1){
					$index_array=[];
					$limit=['page'=>$page,'count'=>$count];
					$tmp=self::getList(compact("limit"));
					if($tmp['status']){
						foreach($tmp['list'] as $value){
							$key_arr=[__CLASS__];
							foreach($query_field as $field){
								$key_arr[]=$field;
								$key_arr[]=$value[$field];
							}
							$key=implode(".",$key_arr);
							$index_array[]=$key;
							Cache::set($key,$value,30*60);
						}
						self::flushIndex($index_array,0);
					}else{
						break;
					}
					$page++;
				}
				Fcache::set(__CLASS__.".index_page",$page,30*60);
				break;
			case 1://insert
				$key_arr=[__CLASS__];
				foreach($query_field as $field){
					$key_arr[]=$field;
					$key_arr[]=$arg[$field];
				}
				$key=implode(".",$key_arr);
				Cache::set($key,$arg,60*30);
				self::flushIndex([$key],0);
				
				break;
			case 2:case 3://update//delete
				
				$list=self::getCache($arg['where']);
				foreach($list as $key=>$val){
					if($arg['update']){
						foreach($arg['update'] as $u_key=>$u_val){
							$val[$u_key]=$u_val;
						}
					}
					if($type==2){
						Cache::set($key,$val,60*30);
					}else{
						Cache::del($key);
					}
				}
				if($type==3){
					self::flushIndex(array_keys($list),1);
				}
				break;
		}
		Fcache::unlock(__CLASS__.".index_page");
		
	}
	
	public static function flushIndex($key_array,$type=0){
		
		$limit=500;
		$page=Fcache::get(__CLASS__.".index_page");
		
		$array=[];
		$result=[];
		for($i=0;$i<$page;$i++){
			$data=Fcache::get(__CLASS__.".index_page.{$i}");
			Fcache::del(__CLASS__.".index_page.{$i}");
			if($type==1){
				foreach($data as $key=>$val){
					if(is_numeric(array_search($val,$key_array))){
						unset($data[$key]);
					}
				}
			}
			
			if($type!=2){
				// var_dump($data);
				$array=array_merge($array,$data);
				if(count($array)>=$limit){
					$result[]=array_splice($array,0,$limit);
				}
			}
		}
		
		if($type==0){
			$array=array_merge($array,$key_array);
			if(count($array)>=$limit){
				$result[]=array_splice($array,0,$limit);
			}
		}
		
		if(count($array)){
			$result[]=$array;
		}
		
		foreach($result as $key=>$val){
			Cache::set(__CLASS__.".index_page.{$key}",$val,30*60);
		}
		
		Cache::set(__CLASS__.".index_page",count($result),30*60);
		
	}
}