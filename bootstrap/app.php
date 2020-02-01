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

require  BASE_PATH . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// set timezone
date_default_timezone_set( config('app.timezone', 'Asia/Shanghai') );