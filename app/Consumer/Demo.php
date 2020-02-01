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

use Wanglelecc\Business\Callback;

/**
 * 消费者示例
 *
 * @package App\Consumer
 *
 * @Author wll
 * @Time 2020-01-31 14:58
 */
class Demo extends Consumer implements Callback
{
    public function handle(): void
    {
        $this->logger->debug('wll....');

        sleep(2);
    }
}