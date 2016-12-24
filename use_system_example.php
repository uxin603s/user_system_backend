<?php
/*
access_token在本系統取得

使用http取得系統資料
	/api?access_token={access_token}

access_token錯誤3次會被鎖30分鐘
	
http code 等於200
	把系統資料寫進session後，
	寫一份快取或資料表方便在系統壞掉時使用
	
http code不等於 200
	會員系統掛了，使用快取資料暫時登入
	
flush_url當有更新網站角色或權限
	該網頁要用session_id()去記憶目前登入使用者session_id
	自己會去/api?access_token={access_token}重新刷session
	自動去該網頁刷新session 重新取得資料
	回傳json格式 ["status":"{status}"]//status:true成功,false失敗
	
	

*/