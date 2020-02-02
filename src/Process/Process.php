<?php
// +----------------------------------------------------------------------
// |  
// | Process.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 15:07
// +----------------------------------------------------------------------

namespace Wanglelecc\Process;

use Wanglelecc\Log\Logger;
use Wanglelecc\Exceptions\SystemException;

/**
 * 进程基类
 *
 * @package Wanglelecc\Process
 *
 * @Author wll
 * @Time 2020-01-31 15:16
 */
abstract class Process
{
    /**
     * 应用名称
     *
     * @var string
     */
    protected $appName = '';

    /**
     * 进程名称
     *
     * @var string
     */
    protected $workerName = '';

    /**
     * 临时目录
     *
     * @var string
     */
    public $tmpDir = '';

    /**
     * 当前进程类型 master 或 worker
     *
     * @var string
     */
    public $type = '';

    /**
     * 进程id
     *
     * @var int
     */
    protected $pid = '';

    /**
     * 进程消费组序
     *
     * @var int
     */
    protected $index = -1;

    /**
     * 管道名称
     *
     * @var string
     */
    protected $pipeName = '';

    /**
     * 管道权限
     *
     * @var integer
     */
    protected $pipeMode = 0777;

    /**
     * 管道前缀
     *
     * @var string
     */
    protected $pipeNamePrefix = 'process.pipe';

    /**
     * 管道存储的文件夹
     *
     * @var string
     */
    protected $pipeDir = '';

    /**
     * 管道文件
     *
     * @var string
     */
    protected $pipePath = '';

    /**
     * 管道读取缓冲区大小
     *
     * @var integer
     */
    protected $readPipeType = 1024;

    /**
     * 信号
     *
     * @var string
     */
    protected $signal = '';

    /**
     * hangup sleep time unit:microsecond /μs
     *
     * default 200000μs
     *
     * @var int
     */
    protected static $hangupLoopMicrotime = 200000;

    /**
     * 最大执行时间
     *
     * default 5*60*60*24
     *
     * @var int
     */
    protected static $maxExecuteTimes = 60 * 60 * 24;

    /**
     * 当前执行时间
     *
     * default 0
     *
     * @var int
     */
    protected static $currentExecuteTimes = 0;

    /**
     * 日志实例
     *
     * @var Logger
     */
    public $logger = null;

    /**
     * Process constructor
     */
    public function __construct()
    {
        $this->logger = Logger::getInstance();

        $this->appName = config('app.name', 'php-process');
        $this->tmpDir  = tmp_path();

        $this->pipeDir        = pipe_path();
        $this->pipeNamePrefix = $this->appName . '.pipe';

        $this->initialize();
    }

    /**
     * Process initialize
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 22:11
     */
    protected function initialize()
    {
        if (empty($this->pid)) {
            $this->pid = posix_getpid();
        }

        $this->pipeName = $this->pipeNamePrefix . '.' . $this->pid;
        $this->pipePath = $this->pipeDir . DIRECTORY_SEPARATOR . $this->pipeName;
    }

    /**
     * 设置进程 pid
     *
     * @param int $pid
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 19:41
     */
    public function setPid(int $pid) :void
    {
        $this->pid = $pid;

        // pid 修改后重新初始化
        $this->initialize();
    }

    /**
     * 获取进程 pid
     *
     * @return int
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 11:02
     */
    public function getPid() :int
    {
        return $this->pid;
    }

    /**
     * 设置进程消费组序
     *
     * @param int $index
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 19:46
     */
    public function setIndex(int $index) :void
    {
        $this->index = $index;
    }

    /**
     * 获取进程消费组序
     *
     * @return int
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 11:03
     */
    public function getIndex() :int
    {
        return $this->index;
    }

    /**
     * 设置进程名称
     *
     * @param string $workerName
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 19:13
     */
    public function setWorkerName(string $workerName) :void
    {
        $this->workerName = $workerName;
    }

    /**
     * 创建管道
     *
     * @return void
     */
    public function makePipe(): void
    {
        if (!file_exists($this->pipePath)) {
            if (!posix_mkfifo($this->pipePath, $this->pipeMode)) {
                $this->logger && $this->logger->error("pipe make {$this->pipePath}");
                exit;
            }

            chmod($this->pipePath, $this->pipeMode);
            $this->logger && $this->logger->info("pipe make {$this->pipePath}");
        }
    }

    /**
     * 将消息写入管道
     *
     * @return void
     */
    public function pipeWrite($signal = ''): void
    {
        $pipe = fopen($this->pipePath, 'w');
        if (!$pipe) {
            $this->logger && $this->logger->error("pipe open {$this->pipePath}");
            return;
        }

        $this->logger && $this->logger->info("pipe open {$this->pipePath}");

        $res = fwrite($pipe, $signal);
        if (!$res) {
            $this->logger && $this->logger->error("pipe write signal: {$signal}", [
                'msg'    => "pipe write {$this->pipePath}",
                'signal' => $signal,
                'res'    => $res,
            ]);
            return;
        }

        $this->logger && $this->logger->info("pipe write signal: {$signal}", [
            'msg'    => "pipe write {$this->pipePath}",
            'signal' => $signal,
            'res'    => $res,
        ]);

        if (!fclose($pipe)) {
            $this->logger && $this->logger->error("pipe close {$this->pipePath}");
            return;
        }

        $this->logger && $this->logger->info("pipe close {$this->pipePath}");
    }

    /**
     * 从管道读取消息
     *
     * @return mixed
     */
    public function pipeRead()
    {
        // check pipe
        while (!file_exists($this->pipePath)) {
            usleep(self::$hangupLoopMicrotime);
        }

        // open pipe
        do {
            // fopen() will block if the file to be opened is a fifo. This is true whether it's opened in "r" or "w" mode.  (See man 7 fifo: this is the correct, default behaviour; although Linux supports non-blocking fopen() of a fifo, PHP doesn't).
            $workerPipe = fopen($this->pipePath, 'r+'); // The "r+" allows fopen to return immediately regardless of external  writer channel.
            usleep(self::$hangupLoopMicrotime);
        } while (!$workerPipe);

        // set pipe switch a non blocking stream
        stream_set_blocking($workerPipe, false);

        // read pipe
        if ($msg = fread($workerPipe, $this->readPipeType)) {
            $this->logger && $this->logger->debug( "pipe read signal: {$msg}");
        }

        return $msg;
    }

    /**
     * 清除管道文件
     *
     * @return bool
     */
    public function clearPipe(): bool
    {
        $msg = "{$this->type} pipe clear {$this->pipePath}";

        $this->logger && $this->logger->info($msg);

        if( !file_exists($this->pipePath) ){
            return true;
        }

        if (!unlink($this->pipePath)) {
            $this->logger && $this->logger->error($msg);
            return false;
        }
        shell_exec("rm -f {$this->pipePath}");
        return true;
    }

    /**
     * 停止当前进程
     *
     * @return bool
     */
    public function stop(): bool
    {
        $msg = "{$this->pid} stop";

        $this->logger && $this->logger->info($msg);

        $this->clearPipe();
        if (!posix_kill($this->pid, SIGKILL)) {
            $this->logger && $this->logger->error($msg);
            return false;
        }

        return true;
    }

    /**
     * 设置当前进程名称
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 22:22
     */
    public function setProcessName(): void
    {
        $os = strtolower(php_uname('s'));

        // 只有在 linux 环境下才可以设置进程名称
        if (strlen($os) > 4 && substr($os, 0, 5) == 'linux') {

            $workerName = empty($this->workerName) ? '' : " ({$this->workerName})";

            cli_set_process_title( "{$this->appName}: {$this->type} process{$workerName}" );
        }
    }

    /**
     * 检测环境是否满足要求
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 22:20
     */
    protected function checkEnvironment(): void
    {
        if (php_sapi_name() != 'cli') {
            throw new SystemException('The program should run in CLI.');
        }
        if (!extension_loaded('pcntl')) {
            throw new SystemException('Need PHP pcntl extension.');
        }
        if (!extension_loaded('posix')) {
            throw new SystemException('Need PHP posix extension.');
        }
    }

}