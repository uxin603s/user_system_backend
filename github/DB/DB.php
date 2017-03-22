<?php
class DB{
	//use Singleton pattern 
	public static $connect=null;
	public static $config=[];
	
	private function __construct(){
		if(file_exists(__DIR__."/config.json")){
			self::$config=json_decode(file_get_contents(__DIR__."/config.json"),1);
		}
		$dbName=self::$config['dbName'];
		$user=self::$config['user'];
		$password=self::$config['password'];
		$host=self::$config['host'];
		
		$pdo_string="mysql:host={$host};dbname={$dbName}";
		try{	
			self::$connect=new PDO($pdo_string,$user,$password,[
				PDO::ATTR_PERSISTENT => true,//持久連線(初始化就要使用不然會無效)
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
			]);
		}catch(PDOException $e){  
			error_log($e);//getMessage,getTrace
			exit;
		}
	}	
	public static function getErrorLog(){
		return self::$connect->errorInfo();
	}

	public static function query($sql,$array=[]){//下SQL語法
		ob_start();
		try{	
			if(self::$connect===null)new self;		
			$query=self::$connect->prepare($sql);		
			$query->execute($array);
		}catch(PDOException $e){
			error_log($e);//getMessage,getTrace
		}
		ob_end_clean();
		
		return $query;
	}
	
	public static function select($sql,$array=[]){//查詢
		if(!is_array($array)){
			$array=[$array];
		}
		$query=self::query($sql,$array);
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$data=$query->fetchAll();
		if(count($data)){
			return $data;
		}
		return false;
	}
	public static function get_prepare_data($array){
		if(!is_array($array))return false;
		$string_arr=[];
		$bind_data=[];
		foreach($array as $key=>$val){
			$string_arr[]="`{$key}` = ? ";
			$bind_data[]=$val;			
		}
		return compact(['string_arr','bind_data']);
	}
	
	public static function insert($insert,$table_name=""){//新增
		if(!is_array($insert) || !$table_name)return false;
		$fields=[];
		$values=[];
		
		foreach($insert as $field=>$value){
			$fields[]="`{$field}`";
			$bind_data[]=$value;
		}
		$values[]="(".implode(',',array_fill(0,count($insert),'?')).")";

		$field_str=implode(',',$fields);
		$value_str=implode(',',$values);
		
		$sql="insert into `{$table_name}` ({$field_str}) values {$value_str}";
		$query=self::query($sql,$bind_data);
		
		if($query->rowCount()){
			if(self::$connect->lastInsertId()){
				return self::$connect->lastInsertId();
			}
			return true;
		}else{
			return false;
		}
	}
	
	public static function update($update,$where,$table_name=""){//修改
		if(!is_array($update) || !is_array($where) || !$table_name)return false;
		
		$bind_data=[];	
		if($prepare_data=self::get_prepare_data($update)){
			$bind_data=array_merge($bind_data,$prepare_data['bind_data']);
			$update_str=implode(" , ",$prepare_data['string_arr']);
		}
		if($prepare_data=self::get_prepare_data($where)){
			$bind_data=array_merge($bind_data,$prepare_data['bind_data']);
			$where_str=implode(" && ",$prepare_data['string_arr']);
		}
		
		$sql="update `{$table_name}` set {$update_str}  where {$where_str}";
		
		$query=self::query($sql,$bind_data);
		return $query->rowCount();
	}
	
	public static function delete($where,$table_name=""){//刪除
		if(!is_array($where) || !$table_name)return false;
		if($prepare_data=self::get_prepare_data($where)){
			$bind_data=$prepare_data['bind_data'];
			$where_str=implode(" && ",$prepare_data['string_arr']);
			$sql="delete from `{$table_name}` where {$where_str}";
			$query=self::query($sql,$bind_data);
			return $query->rowCount();
		}
		return false;
	}
}