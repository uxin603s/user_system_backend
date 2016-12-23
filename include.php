<?php
include_once __DIR__."/github/DB/DB.php";
include_once __DIR__."/github/MysqlCompact/MysqlCompact.php";
include_once __DIR__."/github/MysqlCompact/CRUD.php";
include_once __DIR__."/github/View/View.php";


include_once __DIR__."/github/Cache/Fcache.php";
include_once __DIR__."/github/Cache/Mcache.php";
include_once __DIR__."/github/Cache/Cache.php";


ob_start();
DB::$config=json_decode(file_get_contents(__DIR__."/config/DB.json"),1);
ob_get_clean();