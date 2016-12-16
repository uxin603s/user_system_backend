<?php
Class View{
	public static function set($view_path,$data){
		extract($data,EXTR_OVERWRITE);
		ob_start();
		include $view_path;
		$result=ob_get_contents();
		ob_end_clean();
		return $result;
	}
}