<?php
// +----------------------------------------------------------------------
// |  
// | Callback.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 14:34
// +----------------------------------------------------------------------

namespace Wanglelecc\Business;

use Wanglelecc\Process\Worker;

interface Callback
{
    public function setWorker(Worker $worker) :void;

    public function handle() : void;
}