<?php
include_once __DIR__."/include.php";
//快取初始化

UserRole::flushCache();
UserList::flushCache();
RoleList::flushCache();
RoleData::flushCache();
DataList::flushCache();
WebList::flushCache();

// UserList::reset_session();