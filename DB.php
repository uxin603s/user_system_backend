<?php
class DB{
	//use Singleton pattern 
	public static $connect=null;
	public static $config=[
		'dbName'=>'test',
		'user'=>'user',
		'password'=>'password',
		'host'=>'127.0.0.1',
	];
	
	private function __construct(){
		$dbName=self::$config['dbName'];
		$user=self::$config['user'];
		$password=self::$config['password'];
		$host=self::$config['host'];
		$pdo_set=[
			PDO::ATTR_PERSISTENT => true,//持久連線(初始化就要使用不然會無效)
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			// PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
		];
		$pdo_string="mysql:host={$host};dbname={$dbName}";
		
		self::$connect=new PDO($pdo_string,$user,$password,$pdo_set);	
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
		
		$error_log=ob_get_contents();
		if($error_log){
			error_log($error_log);
			self::$connect=null;
		}
		ob_end_clean();
		
		return $query;
	}
	
	public static function select($sql,$array=[]){//查詢
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
		$field_str_arr=[];
		$value_str_arr=[];
		$bind_data=[];
		foreach($insert as $key=>$val){
			$field_str_arr[]="`{$key}`";
			$value_str_arr[]="?";
			$bind_data[]=$val;
		}
		$field_str=implode(',',$field_str_arr);
		$value_str=implode(',',$value_str_arr);
		
		$sql="insert into {$table_name} ({$field_str}) values ({$value_str})";
		$query=self::query($sql,$bind_data);
		
		if(self::$connect->lastInsertId()){
			return self::$connect->lastInsertId();
		}
		return $query->rowCount();
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
	function test(){
		DB::query("CREATE TABLE `test` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `pass` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		$insert=[
			'user'=>'test1',
			'pass'=>'pass1',
			'comment'=>'註解',
		];
		$id=DB::insert($insert,"test");
		if($id){
			echo "新增成功";
			var_dump($id);
		}else{
			echo "新增失敗";
		}
		$tmp=DB::select("select * from test where user = ? && pass= ?",['test1','pass1']);
		if($tmp){
			echo "查詢成功";
			var_dump($tmp);
		}else{
			echo "查詢失敗";
		}
		$update['user']="test2";
		$where['id']=1;
		$tmp=DB::update($update,$where,"test");
		if($tmp){
			echo "更新成功";
			var_dump($tmp);
		}else{
			echo "更新失敗";
		}
		$tmp=DB::delete($where,"test");
		if($tmp){
			echo "刪除成功";
			var_dump($tmp);
		}else{
			echo "刪除失敗";
		}

		DB::query("drop table test");
	}
}