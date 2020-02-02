<?php
// +----------------------------------------------------------------------
// |  
// | bootstrap.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 14:02
// +----------------------------------------------------------------------

use Wanglelecc\ExceptionHandle\HandleExceptions;

// 设置时区
date_default_timezone_set('Asia/Shanghai' );


// 载入Autoload
require  BASE_PATH . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// 注册异常处理
HandleExceptions::getInstance()->bootstrap();


