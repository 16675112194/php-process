<?php
// +----------------------------------------------------------------------
// |  
// | Exception.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 20:42
// +----------------------------------------------------------------------

namespace Wanglelecc\Exceptions;

use Throwable;

/**
 * 异常基础类
 * @package Wanglelecc\Exceptions
 *
 * @Author wll
 * @Time 2020-01-31 21:56
 */
class BaseException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}