<?php
include_once __DIR__."/github/DB/DB.php";
include_once __DIR__."/github/MysqlCompact/MysqlCompact.php";
include_once __DIR__."/github/MysqlCompact/CRUD.php";
include_once __DIR__."/github/View/View.php";


include_once __DIR__."/github/Cache/Fcache.php";
include_once __DIR__."/github/Cache/Mcache.php";
include_once __DIR__."/github/Cache/Cache.php";
include_once __DIR__."/github/FlushCache/FlushCache.php";

include_once __DIR__."/FbRegisterList.php";
include_once __DIR__."/UserList.php";
include_once __DIR__."/WebList.php";
include_once __DIR__."/RoleList.php";
include_once __DIR__."/DataList.php";
include_once __DIR__."/RoleData.php";
include_once __DIR__."/UserRole.php";

Mcache::$prefix="cfd_chichi_user";

ob_start();
DB::$config=json_decode(file_get_contents(__DIR__."/config/DB.json"),1);
ob_get_clean();