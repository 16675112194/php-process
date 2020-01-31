<?php
// +----------------------------------------------------------------------
// |  
// | Daemon.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 15:07
// +----------------------------------------------------------------------

namespace Wanglelecc\Process;

use Wanglelecc\Component\Singleton;
use Wanglelecc\Exceptions\SystemException;

/**
 * 守护进程基类
 * @package Wanglelecc\Process
 *
 * @Author wll
 * @Time 2020-01-31 15:15
 */
abstract class Daemon
{
    use Singleton;

    /**
     * @var string
     */
    private $stdin = '/dev/null';

    /**
     * @var string
     */
    private $stdout = 'storage/logs/console.log';

    /**
     * @var string
     */
    private $stderr = 'storage/logs/console.error.log';

    /**
     * Daemon constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * 初始化
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 19:44
     */
    protected function initialize()
    {
        $this->stdout = log_file_path('console.log');
        $this->stderr = log_file_path('console.error.log');
    }

    /**
     * 守护进程
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 16:25
     */
    protected function daemonize(): void
    {
        global $stdin, $stdout, $stderr;

        // 创建一个子进程
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new SystemException("进程创建失败", 1);
        } elseif ($pid > 0) {
            //父进程退出，子进程被1号进程收养
            exit(0);
        }

        //创建一个新的会话，脱离终端控制，更改子进程为组长进程
        $sid = posix_setsid();
        if ($sid == -1) {
            throw new SystemException('进程创建新会话失败');
        }

        //修改进程的工作目录，由于子进程会继承父进程的工作目录，修改工作目录释放对父进程工作目录的占用
        chdir('/');

        //重设文件掩码
        umask(0);

        /**
         * 通过上一步，我们创建了一个新的会话组长，进程组长，且脱离了终端，但是会话组长可以申请重新打开一个终端，为了避免
         * 这种情况，我们再次创建一个子进程，并退出当前进程，这样运行的进程就不再是会话组长。
         */
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new SystemException("进程创建失败", 1);
        } elseif ($pid > 0) {
            //再一次退出父进程，子进程成为最终的守护进程
            exit(0);
        }

        //关闭守护进程不是用的标准输入、输出、错误数据的描述符
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        /**
         * 如果关闭了标准输入/输出/错误描述符
         * 那么打开的前三个文件描述符将成为新的标准输入/输出/错误的文件描述符
         * 使用的$stdin,$stdout,$stderr就是普通的变量
         * 必须指定为全局变量，否则文件描述符将在函数执行完毕后被释放
         */
        $stdin  = fopen($this->stdin, 'r');
        $stdout = fopen($this->stdout, 'a+');
        $stderr = fopen($this->stderr, 'a+');
    }
}