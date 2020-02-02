<?php
// +----------------------------------------------------------------------
// |
// | Consumer.php
// |
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 14:34
// +----------------------------------------------------------------------

namespace App\Consumer;

use Wanglelecc\Log\Logger;
use Wanglelecc\Process\Worker;

/**
 * 进程消费基类
 *
 * @package App\Consumer
 *
 * @Author wll
 * @Time 2020-01-31 14:55
 */
abstract class Consumer
{
    /**
     * 当前进程id
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * 进程消费组序
     *
     * @var int
     */
    protected $index = -1;

    /**
     * 当前进程对象
     *
     * @var Worker
     */
    protected $worker;

    /**
     * 日志对象
     *
     * @var Logger
     */
    protected $logger;

    /**
     * 构造方法
     *
     * Consumer constructor.
     */
    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    /**
     * 设置 Worker
     *
     * @param Worker $worker
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:17
     */
    public function setWorker(Worker $worker): void
    {
        $this->worker = $worker;
        $this->pid    = $worker->getPid();
        $this->index  = $worker->getIndex();
    }
}