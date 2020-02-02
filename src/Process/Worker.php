<?php
// +----------------------------------------------------------------------
// |  
// | Worker.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 15:08
// +----------------------------------------------------------------------

namespace Wanglelecc\Process;

use Wanglelecc\Business\Callback;
use Wanglelecc\Exceptions\SystemException;

/**
 * Worker 进程
 *
 * @package Wanglelecc\Process
 *
 * @Author wll
 * @Time 2020-02-01 14:52
 */
class Worker extends Process
{
    /**
     * 消费者参数
     *
     * @var array
     */
    protected $consumeConfig = [];

    /**
     * 进程终止标记位
     *
     * @var boolean
     */
    protected $workerExitFlag = false;

    /**
     * 进程启动需要的参数(备份)
     *
     * @var array
     */
    protected $workerManagerParams = [];

    /**
     * 信号版本
     *
     * @var int
     */
    protected $signalVer = -1;

    /**
     * Worker constructor.
     */
    public function __construct()
    {
        $this->type = 'worker';

        parent::__construct();
    }

    /**
     * 设置消费参数
     *
     * @param array $consumeConfig
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 18:54
     */
    public function setConsumeConfig(array $consumeConfig = []) :void
    {
        $this->consumeConfig = $consumeConfig;
    }

    /**
     * 设置进程启动需要的参数
     *
     * @param array $workerManagerParams
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 20:06
     */
    public function setWorkerManagerParams(array $workerManagerParams = []) :void
    {
        $this->workerManagerParams = $workerManagerParams;
    }

    /**
     * 获取进程启动需要的参数
     *
     * @return array
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 20:07
     */
    public function getWorkerManagerParams() :array
    {
        return $this->workerManagerParams;
    }

    /**
     * 设置信号版本
     *
     * @param int $signalVer
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 21:02
     */
    public function setSignalVer(int $signalVer) :void
    {
        $this->signalVer = $signalVer;
    }

    /**
     * 获取信号版本
     *
     * @return int
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 21:02
     */
    public function getSignalVer() :int
    {
        return $this->signalVer;
    }

    /**
     * 获取进程键值
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 21:22
     */
    public function getKey() :string
    {
        return "{$this->workerName}_{$this->index}";
    }

    /**
     * 当前进程执行入口
     *
     * @param string $abstract
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:56
     */
    public function hungup(string $abstract) :void
    {
        $consumer = new $abstract(...$this->consumeConfig);

        if (!($consumer instanceof Callback)) {
            throw new SystemException("`{$abstract}` Unrealized Callback instanceof.");
        }

        call_user_func_array([$consumer, 'setWorker'], [$this]);

        call_user_func([$consumer, 'handle']);

        $this->workerExit();
    }

    /**
     * 当前进程消费入口(分片)
     *
     * @param string $abstract
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:54
     */
    public function consume(string $abstract) :void
    {
        $consumer = new $abstract(...$this->consumeConfig);

        if (!($consumer instanceof Callback)) {
            throw new SystemException("`{$abstract}` Unrealized Callback instanceof.");
        }

        call_user_func_array([$consumer, 'setWorker'], [$this]);

        while ( true ) {
            // business logic
            call_user_func([$consumer, 'handle']);

            // check exit flag
            if ( $this->workerExitFlag ) {
                $this->workerExit();
            }

            // check max execute time
            if ( self::$currentExecuteTimes >= self::$maxExecuteTimes ) {
                $this->workerExit();
            }

            // handle pipe msg
            if ( $this->signal = $this->pipeRead() ) {
                $this->dispatchSig();
            }

            // increment 1
            ++self::$currentExecuteTimes;

            // precent cpu usage rate reach 100%
            usleep( self::$hangupLoopMicrotime );
        }
    }

    /**
     * 处理当前进程信号动作
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:53
     */
    private function dispatchSig() :void
    {
        switch ( $this->signal ) {
            // reload
            case 'reload':
                $this->workerExitFlag = true;
                break;

            // stop
            case 'stop':
                $this->workerExitFlag = true;
                break;

            default:

                break;
        }
    }

    /**
     * 停止进程
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:52
     */
    private function workerExit() :void
    {
        $this->logger && $this->logger->info('worker process exit');

        $this->clearPipe();
        exit;
    }
}