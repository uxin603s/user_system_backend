<?php
/*
$where_list=[
	[
		"field"=>"",//欄位名稱
		"type"=>"",//組合符號0:=,1:!=,2:like,3:not like,4:大於,5:小於
		"value"=>"",//欄位值
	]
];
*/
class MysqlCompact{
	public static $type_list=[
		"=",
		"!=",
		"like",
		"not like",
		">",
		"<",
	];
	public static function where($list,&$bind_data){
		$query_str='';
		if(is_array($list) && count($list)){
			$query_str.=" ";
			$query_str.="where 1=1 ";
			$used_count=array_count_values(array_column($list,'field'));
			$or_array=[];
			$and_array=[];
			foreach($used_count as $field=>$count){
				if($count>1){
					$or_array[]=$field;
				}else{
					$and_array[]=$field;
				}
			}
			$or_where=[];
			foreach($list as $item){
				$field=$item['field'];
				$type=$item['type'];
				$value=$item['value'];
				$symbol=self::$type_list[$type];
				//欄位白名單
				if($symbol){
					if(in_array($field,$and_array) || in_array($type,[4,5])){
						$query_str.=" && {$field} {$symbol} ?";
						$bind_data[]=$value;
					}else if(in_array($field,$or_array)){
						$or_where[$field]['query'][]=" {$field} {$symbol} ? ";
						$or_where[$field]['value'][]=$value;
					}
				}
			}
			foreach($or_where as $field=>$item){
				$query_str.=" && (".implode(" || ",$item['query']).")";
				$bind_data=array_merge($bind_data,$item['value']);
			}
			$query_str.=" ";
		}
		return $query_str;
	}
	public static $sort_list=[
		"asc",
		"desc",
	];
	public static function order($list){
		$query_str='';
		if(is_array($list) && count($list)){
			$query_str.=" ";
			$query_str.="order by ";
			$array=[];
			foreach($list as $item){
				$field=$item['field'];
				$sort=self::$sort_list[$item['type']];
				if($sort){
					$array[]="{$field} {$sort}";
				}
			}
			$query_str.=implode(",",$array);
			$query_str.=" ";
		}
		return $query_str;
	}
	public static function group($list){
		$query_str='';
		if(is_array($list) && count($list)){
			$query_str.=" ";
			$query_str="group by ".implode(",",$list);
			$query_str.=" ";
		}
		return $query_str;
	}
	public static function have($list,&$bind_data){
		$query_str='';
		if(is_array($list) && count($list)){
			$query_str.=" ";
			foreach($list as $item){
				if($item['type']==0){
					$query_str.="having count({$item['field']})  >=  ? ";
					$bind_data[]=$item['count'];
					if(is_array($item['value'])){
						foreach($item['value'] as $value){
							$query_str.=" && max( CASE `{$item['field']}`  WHEN ? THEN 1 ELSE 0 END ) = 1";
							$bind_data[]=$value;
						}
					}
					if(is_array($item['valueR'])){
						foreach($item['valueR'] as $value){
							$query_str.=" && max( CASE `{$item['field']}`  WHEN ? THEN 1 ELSE 0 END ) = 0";
							$bind_data[]=$value;
						}
					}
				}
			}
			$query_str.=" ";
		}
		return $query_str;
	}
	public static function limit($data=[]){
		$query_str='';
		if(count($data) && is_numeric($data['page']) && is_numeric($data['count'])){
			$query_str.=" ";
			$start=$data['page']*$data['count'];
			$count=$data['count'];
			$query_str.="limit {$start},{$count}";
			$query_str.=" ";
		}
		return $query_str;
	}
}