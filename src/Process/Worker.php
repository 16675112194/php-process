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

class Worker extends Process
{

    /**
     * 进程终止标记位
     *
     * @var boolean
     */
    protected $workerExitFlag = false;

    /**
     * Worker constructor.
     */
    public function __construct()
    {
        $this->type = 'worker';
    }

    /**
     * the work hungup function
     *
     * @param string $abstract
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 13:45
     */
    public function hungup(string $abstract)
    {
        $consumer = new $abstract;

        if (!($consumer instanceof Callback)) {
            throw new SystemException("`{$abstract}` Unrealized Callback instanceof.");
        }

        call_user_func_array([$consumer, 'handle'], [$this]);

        $this->workerExit();
    }

    /**
     * the work consume function
     *
     * @param string $abstract
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 13:46
     */
    public function consume(string $abstract)
    {
        $consumer = new $abstract;

        if (!($consumer instanceof Callback)) {
            throw new SystemException("`{$abstract}` Unrealized Callback instanceof.");
        }

        while ( true ) {
            // business logic
            call_user_func_array([$consumer, 'handle'], [$this]);

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
     * dispatch signal for the worker process
     *
     * @return void
     */
    private function dispatchSig()
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
     * exit worker
     *
     * @return void
     */
    private function workerExit()
    {
        $this->logger && $this->logger->info('worker process exit');

        $this->clearPipe();
        exit;
    }
}