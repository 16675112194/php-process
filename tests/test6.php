<?php
// +----------------------------------------------------------------------
// |  
// | test6.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 12:40
// +----------------------------------------------------------------------

$pid = 59740;
//$pid = 58517;

$res = pcntl_waitpid( $pid, $status, WNOHANG );

var_dump($res, $status);