<?php
// +----------------------------------------------------------------------
// |  
// | Demo.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 14:57
// +----------------------------------------------------------------------

namespace App\Consumer;

use Wanglelecc\Process\Worker;
use Wanglelecc\Business\Callback;

/**
 * 消费者实例
 *
 * @package App\Consumer
 *
 * @Author wll
 * @Time 2020-01-31 14:58
 */
class Demo extends Consumer implements Callback
{
    public function handle(Worker $worker): void
    {

    }
}