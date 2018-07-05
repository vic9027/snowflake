<?php
/**
 * @copyright (c) 2018, sunny-daisy 
 * all rights reserved.
 *
 * phpunit bootstrap.php 
 *
 * @author liyong5@staff.sina.com.cn wenqiang1@staff.sina.com.cn
 *
 * @createdate 2018-06-28
 */
if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    require_once __DIR__ . "/../vendor/autoload.php";
} else {
    throw new Exception("Can not find autoload.php");
}
