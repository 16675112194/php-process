<?php
// +----------------------------------------------------------------------
// |  
// | Manager.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 15:11
// +----------------------------------------------------------------------

namespace Wanglelecc\Process;

use Wanglelecc\Log\Logger;
use Wanglelecc\Component\Singleton;
use Wanglelecc\Exceptions\BaseException;
use Wanglelecc\Exceptions\SystemException;

/**
 * 进程管理类
 *
 * @package Wanglelecc\Process
 *
 * @Author wll
 * @Time 2020-02-01 20:16
 */
class Manager
{
    use Singleton;

    /**
     * 应用名称
     *
     * @var string
     */
    protected $appName = 'php-process';

    /**
     * 版本
     *
     * @var string
     */
    protected $version = "1.0.0";

    /**
     * Master 进程对象
     *
     * @var Master
     */
    private $master;

    /**
     * 守护进程对象
     *
     * @var Daemon
     */
    private $daemon;

    /**
     * Worker 进程对象
     *
     * @var array [Worker]
     */
    public $workers = [];

    /**
     * 处理信号
     *
     * @var string
     */
    private $waitSignal = '';

    /**
     * 信号版本
     *
     * @var int
     */
    private $signalVer = 0;

    /**
     * 系统信号
     *
     * @var array
     */
    private $signalSupport
        = [
            'reload'    => 10,  // reload signal
            'stop'      => 12,  // quit signal gracefully stop
            'terminate' => 15,  // terminate signal forcefully stop
            'int'       => 2    // interrupt signal
        ];

    /**
     * 日志对象
     *
     * @var Logger
     */
    protected $logger;

    /**
     * hangup sleep time unit:microsecond /μs
     *
     * default 200000μs
     *
     * @var int
     */
    private static $hangupLoopMicrotime = 200000;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->appName = config('app.name', 'php-process');

        // 初始化主进程实例
        $this->master = Master::getInstance();

        // 初始化守护进程对象实例
        $this->daemon = Daemon::getInstance();
    }

    /**
     * 启动
     *
     * @param string $env
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 22:17
     */
    public function start(string $env)
    {
        // 守护进程模式
        if($env == 'pro'){
            $this->daemon->daemonize();
        }

        // 输出欢迎语
        $this->welcome();

        // 设置主进程名称
        $this->master->setProcessName();

        // 创建主进程管道
        $this->master->makePipe();

        // 保存主进程id
        $this->master->makePid();

        // 重定义信号值
        $this->signalSupport = [
            'reload'    => SIGUSR1,
            'stop'      => SIGUSR2,
            'terminate' => SIGTERM,
            'int'       => SIGINT,
        ];

        // 执行 fork worker 操作
//        $this->execFork(config('consumer', []));

        // 注册信号处理
        $this->registerSigHandler();

        $this->hangup();
    }

    /**
     * 状态
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 22:05
     */
    public function status()
    {
        if($this->master->status()){
            echo date('Y-m-d H:i:s').' The '.$this->appName.' is running.'.PHP_EOL;
        }else{
            echo date('Y-m-d H:i:s').' The '.$this->appName.' is stopped.'.PHP_EOL;
        }
    }

    /**
     * 停止
     *
     * @param int $sig
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 22:08
     */
    public function stop(int $sig)
    {
        if($this->master->status()){
            $pid = $this->master->getPid();
            if ($pid > 0 && posix_kill($pid, $sig)) {
                echo date('Y-m-d H:i:s').' Successfully stopped '.$this->appName.'.'.PHP_EOL;
            } else {
                echo date('Y-m-d H:i:s').' Failed stopped '.$this->appName.'.'.PHP_EOL;
            }
        }else{
            echo date('Y-m-d H:i:s').' The '.$this->appName.' is stopped.'.PHP_EOL;
        }
    }


    /**
     * 欢迎语
     *
     * @return void
     */
    public function welcome()
    {
        $welcome = <<<WELCOME
\033[36m
56BR.COM
			
Multi process manager for PHP

Author: wll <wanglelecc@gmail.com>
Github: https://github.com/wanglelecc/php-process

Version: {$this->version}

\033[0m
WELCOME;

        echo $welcome;
    }

    /**
     * 定义信号处理
     *
     * @param int $signal
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 18:19
     */
    public function defineSigHandler($signal = 0): void
    {
        switch ($signal) {
            // reload signal
            case $this->signalSupport['reload']:

                $this->waitSignal = 'reload';
                $this->signalVer++;

                // push reload signal to the worker processes from the master process
                foreach ($this->workers as $v) {
                    $v->pipeWrite('reload');
                }
                break;

            // kill signal
            case $this->signalSupport['stop']:

                $this->waitSignal = 'stop';
                $this->signalVer++;

                // push stop signal to the worker processes from the master process
                foreach ($this->workers as $v) {
                    $v->pipeWrite('stop');
                }
                break;

            case $this->signalSupport['int']:
                foreach ($this->workers as $v) {
                    // clear pipe
                    $v->clearPipe();
                    // kill -9 all worker process
                    $result = posix_kill($v->pid, SIGKILL);

                    $context = [
                        'from'   => $this->master->type,
                        'extra'  => "kill -SIGKILL {$v->pid}",
                        'result' => $result,
                    ];

                    $this->logger->info("kill -SIGKILL {$v->pid}", $context);
                }

                // clear pipe
                $this->master->clearPipe();
                $this->master->clearPid();
                // kill -9 master process
                echo "master stop... \n";
                exit;
                break;

            case $this->signalSupport['terminate']:
                foreach ($this->workers as $v) {
                    // clear pipe
                    $v->clearPipe();
                    // kill -9 all worker process
                    posix_kill($v->pid, SIGKILL);
                }
                // clear pipe
                $this->master->clearPipe();
                $this->master->clearPid();
                // kill -9 master process
                echo "master stop... \n";
                exit;
                break;

            default:

                break;
        }
    }

    /**
     * 注册信号处理器
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 18:19
     */
    private function registerSigHandler(): void
    {
        foreach ($this->signalSupport as $v) {
            pcntl_signal($v, [&$this, 'defineSigHandler']);
        }
    }

    /**
     * 挂断主进程
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 18:36
     */
    private function hangup()
    {
        while (true) {
            // dispatch signal for the handlers
            pcntl_signal_dispatch();

            foreach ($this->workers as $k => $worker) {
                // 获取子进程的状态信息，防止子进程成为僵尸进程
                $res = pcntl_waitpid($worker->pid, $status, WNOHANG);
                if ($res > 0) {
                    // 停止信号，进程结束从 workers 池中释放
                    if($this->waitSignal === 'stop' || $this->waitSignal === 'reload'){
                        unset($this->workers[$res]);
                        continue;
                    }

                    goto forkWorkerProcess;
                }

                // 检查进程是否存活
                if( posix_kill($worker->pid, 0) ){
                    continue;
                }

                // fork 子进程
                forkWorkerProcess:
//                $this->fork($worker->getWorkerManagerParams());
            }

            // 停止信号
            if ($this->waitSignal === 'stop' || $this->waitSignal === 'reload') {
                // 子进程都停止后停止主进程
                if (empty($this->workers)) {

                    $this->master->stop();

                    sleep(1);

                    if($this->waitSignal === 'reload'){
                        // 启动一个新的自己
                        // $this->master->start();
                    }
                }
            }

            // 睡眠, 防止 cpu 100%
            usleep(self::$hangupLoopMicrotime);
        }
    }


    /**
     * 执行 fork worker 操作
     *
     * @param array $consumers
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 20:15
     */
    public function execFork($consumers = []) :void
    {
        foreach($consumers as $consumer){
            for($index = 0; $index < $consumer['workerNum'] ?: 1; $index++ ){
                $consumer['index'] = $index;
                $this->fork($consumer);
            }
        }
    }

    /**
     * 创建 worker 进程
     *
     * @param array $consumer
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 20:10
     */
    private function fork(array $consumer = [])
    {
        $consumer['index']           = $consumer['index'] ?? -1;
        $consumer['workerName']      = $consumer['workerName'] ?? '';
        $consumer['consumeConfig']   = $consumer['consumeConfig'] ?? [];
        $consumer['consumeSharding'] = $consumer['consumeSharding'] ?? true;

        if($consumer['index'] < 0){
            throw new SystemException('consumer `index` error');
        }

        if (empty($consumer['consumeClass'])) {
            throw new SystemException('consumer `consumeClass` is null');
        }

        if (!class_exists($consumer['consumeClass'])) {
            throw new SystemException("class `{$consumer['consumeClass']}` Undefined");
        }

        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                // exception
                exit;
                break;

            case 0:
                try {
                    // 初始化子进程实例
                    $worker = new Worker();
                    $worker->setWorkerName($consumer['workerName']);
                    $worker->setConsumeConfig($consumer['consumeConfig']);
                    $worker->setIndex($consumer['index']);

                    $worker->setProcessName();
                    $worker->makePipe();

                    $method = boolval($consumer['consumeSharding']) ? 'consume' : 'hungup';
                    $worker->$method($consumer['consumeClass']);
                } catch (BaseException $exception) {
                    $context = [
                        'msg'  => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ];

                    $this->logger->error($exception->getMessage(), $context);
                }
                exit;
                break;

            default:
                // 在主进程中也实例化一份 Worker 对象用于管理
                try {
                    $worker = new Worker();
                    $worker->setWorkerName($consumer['workerName']);
                    $worker->setConsumeConfig($consumer['consumeConfig']);
                    $worker->setIndex($consumer['index']);
                    $worker->setPid($pid);
                    $worker->setSignalVer($this->signalVer);

                    // 备份进程fork所需参数
                    $worker->setWorkerManagerParams($consumer);

                    $this->workers[$worker->getKey()] = $worker;
                } catch (BaseException $exception) {
                    $context = [
                        'msg'  => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ];

                    $this->logger->error($exception->getMessage(), $context);
                }
                break;
        }
    }

}