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
			self::flush($insert,1);
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
			self::flush(compact(['update','where']),2);
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
			self::flush(compact(['where']),3);
		}else{
			$status=false;
			$message="刪除失敗";
		}

		return compact(['status','message','where']);
	}
	public static function flush($arg=[],$type=0){
		
	}
}