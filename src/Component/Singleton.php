<?php
// +----------------------------------------------------------------------
// |
// | Singleton.php
// |
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-28 11:28
// +----------------------------------------------------------------------

namespace Wanglelecc\Component;

/**
 * 单例 Trait
 * @package Wanglelecc\Component
 *
 * @Author wll
 * @Time 2020-01-31 15:15
 */
trait Singleton
{
    private static $instance;

    public static function getInstance(...$args)
    {
        if(!isset(self::$instance)){
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }
}