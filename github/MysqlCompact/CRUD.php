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
		Fcache::lock(__CLASS__.".index_page");
		
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
		Fcache::unlock(__CLASS__.".index_page");
		return compact(['status','message','insert']);
	}
	public static function update($arg){
		Fcache::lock(__CLASS__.".index_page");
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
		Fcache::unlock(__CLASS__.".index_page");
		return compact(['status','message','arg','update','where']);
	}
	public static function delete($where){
		Fcache::lock(__CLASS__.".index_page");
		$where=self::filter_field($where);
		if(DB::delete($where,self::$table)){
			$status=true;
			$message="刪除成功";
			self::flushCache(compact(['where']),3);
		}else{
			$status=false;
			$message="刪除失敗";
		}
		Fcache::unlock(__CLASS__.".index_page");
		return compact(['status','message']);
	}
	
	public static function getCache($arg=[]){
		if($arg['where']){
			$where=$arg['where'];
		}else{
			$where=[];
		}
		if($arg['not_where']){
			$not_where=$arg['not_where'];
		}else{
			$not_where=[];
		}
		if($arg['limit']){
			$limit=$arg['limit'];
		}else{
			$limit=['count'=>200,'page'=>0,'rand'=>false,'sort'=>false,];
		}
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
		$preg="/^{$preg}$/";
		
		$count=Cache::get(__CLASS__.".index_page")-1;
		
		$result=[];
		$total=0;
		$pages=range(0,$count);
		
		if($limit['sort']){
			$pages=array_reverse($pages);
		}
		if($limit['rand']){
			shuffle($pages);
		}
		foreach($pages as $page){
			$index_page=Cache::get(__CLASS__.".index_page.{$page}");
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
					
					
					
					if(($limit['count']*$limit['page'])<=$total){
						if($value=Cache::get($key_name,30*60)){
							$result[$key_name]=$value;
						}
					}
					if(count($result)>=$limit['count']){
						break 2;
					}
					
					++$total;
				}
			}
			
		}
		return $result;
	}
	public static $limit=500;
	public static function flushCache($arg=[],$type=0){
		if(is_array(self::$cache_key_field)){
			$query_field=self::$cache_key_field;
		}else{
			return false;
		}
		
		switch($type){
			case 0://init
				self::flushIndex([],0);
				
				$page=0;
				$count=self::$limit;
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
						self::flushIndex($index_array,1);
					}else{
						break;
					}
					$page++;
					var_dump($page);
				}
				Cache::set(__CLASS__.".index_page",$page,30*60);
				break;
			case 1://insert
				$key_arr=[__CLASS__];
				foreach($query_field as $field){
					$key_arr[]=$field;
					$key_arr[]=$arg[$field];
				}
				$key=implode(".",$key_arr);
				Cache::set($key,$arg,60*30);
				self::flushIndex([$key],1);
				
				break;
			case 2:case 3://update//delete
				$update_index=[];
				$list=self::getCache(['where'=>$arg['where']]);
				foreach($list as $key=>$val){
					if($arg['update']){
						foreach($arg['update'] as $u_key=>$u_val){
							$val[$u_key]=$u_val;
						}
					}
					
					if($type==2){
						$key_arr=[__CLASS__];
						foreach($query_field as $field){
							$key_arr[]=$field;
							$key_arr[]=$val[$field];
						}
						$key=implode(".",$key_arr);
						Cache::set($key,$val,60*30);
						$update_index[]=$key;
					}else{
						Cache::del($key);
					}
				}
				self::flushIndex(array_keys($list),2);
				if(count($update_index)){
					self::flushIndex($update_index,1);
				}
				break;
		}
		
	}
	/*
	type 
	0:clear
	1:insert
	2:delete 
	*/
	public static function flushIndex($key_array,$type=0){
		
		$limit=self::$limit;
		$page=Cache::get(__CLASS__.".index_page");
		
		$array=[];
		$result=[];
		for($i=0;$i<$page;$i++){
			$data=Cache::get(__CLASS__.".index_page.{$i}");
			Cache::del(__CLASS__.".index_page.{$i}");
			if($type==2){
				foreach($data as $key=>$val){
					if(is_numeric(array_search($val,$key_array))){
						unset($data[$key]);
					}
				}
			}
			
			if($type!=0){
				foreach($data as $val){
					$array[]=$val;
				}
				if(count($array)>=$limit){
					$result[]=array_splice($array,0,$limit);
				}
			}
		}
		
		if($type==1){
			foreach($key_array as $val){
				$array[]=$val;
			}
			if(count($array)>=$limit){
				$result[]=array_splice($array,0,$limit);
			}
		}
		
		if(count($array)){
			$result[]=$array;
		}
		
		foreach($result as $key=>$val){
			Cache::set(__CLASS__.".index_page.{$key}",$val);
		}
		
		Cache::set(__CLASS__.".index_page",count($result));
	}
}