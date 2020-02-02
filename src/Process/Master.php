<?php
// +----------------------------------------------------------------------
// |  
// | Master.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 15:09
// +----------------------------------------------------------------------

namespace Wanglelecc\Process;

use Wanglelecc\Component\Singleton;

/**
 * 主进程基类
 *
 * @package Wanglelecc\Process
 *
 * @Author wll
 * @Time 2020-02-01 17:25
 */
class Master extends Process
{
    use Singleton;

    /**
     * PID 文件路径
     *
     * @var string
     */
    protected $pidFile = 'pid';

    /**
     * PID 目录权限
     *
     * @var int
     */
    protected $pidMode = 0755;

    /**
     * Worker constructor.
     */
    public function __construct()
    {
        $this->type = 'master';

        parent::__construct();

        $this->pidFile = $this->tmpDir . DIRECTORY_SEPARATOR . $this->type . '.' . $this->pidFile;
    }

    /**
     * 保存 master pid
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 15:42
     */
    public function makePid(): void
    {
        $pidDir = dirname($this->pidFile);
        if (!file_exists($pidDir)) {
            mkdir($pidDir, $this->pipeMode, true);
        }

        file_put_contents($this->pidFile, posix_getpid());
    }

    /**
     * 清除 master pid
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 15:43
     */
    public function clearPid(): void
    {
        if (file_exists($this->pidFile)) {
            unlink($this->pidFile);
        }
    }

    /**
     * 获取 master pid
     *
     * @return int
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 15:50
     */
    public function getPid(): int
    {
        if (file_exists($this->pidFile)) {
            return intval(file_get_contents($this->pidFile));
        }

        return 0;
    }

    /**
     * 检测服务状态
     *
     * @return bool
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 15:53
     */
    public function status(): bool
    {
        return $this->checkPid();
    }

    /**
     * 检查 master pid 是否存在
     *
     * @return bool
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 15:49
     */
    protected function checkPid(): bool
    {
        if (file_exists($this->pidFile)) {

            $pid = intval(file_get_contents($this->pidFile));

            //向进程发送一个默认信号用来查看进程是否还存活
            if ($pid > 0 && posix_kill($pid, 0)) {
                return true;
            } else {
                unlink($this->pidFile);
            }
        }

        // 未运行状态清除残留管道文件
        $this->clearAllPipe();

        return false;
    }

    /**
     * 批量清除残留的管道文件
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 09:19
     */
    private function clearAllPipe() :void
    {
        $files = scandir($this->pipeDir);
        $result = [];
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.gitgnore' && substr( $file, 0, strlen($this->pipeNamePrefix) ) == $this->pipeNamePrefix) {
                if (is_dir($this->pipeDir . '/' . $file)) {
                    continue;
                } else {
                    $result[] = $filename = $this->pipeDir . DIRECTORY_SEPARATOR . basename($file);
                    if(file_exists($filename)){
                        unlink($filename);
                        shell_exec("rm -f {$filename}");
                    }

                }
            }
        }
    }

    /**
     * 终止 master 进程
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 14:52
     */
    public function masterExit() :void
    {
        $this->logger && $this->logger->info('master process exit');

        // 清除管道
        $this->clearPipe();

        // 清除PID
        $this->clearPid();

        sleep(5);

        // 清除残余管道文件
        $this->clearAllPipe();

        echo "master process exit...", PHP_EOL;

        exit;
    }

}