<?php
include_once __DIR__."/include.php";

Mcache::$prefix="cfd_user_chichi";
Mcache::set("test",1);
var_dump(Mcache::get("test"));

// Fcache::set("test",1);
// var_dump(Fcache::get("test"));