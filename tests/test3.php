<?php
// +----------------------------------------------------------------------
// |  
// | 产生僵尸进程
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-29 14:45
// +----------------------------------------------------------------------

// 获取当前进程ID
$parentPid = posix_getpid();
echo "parent progress pid:{$parentPid}\n";

$childList = array();

// 创建子进程
$pid = pcntl_fork();
if ( $pid == -1) {
    // 创建失败
    exit("fork progress error!\n");
} else if ($pid == 0) {
    $repeatNum = 10;
    for ( $i = 1; $i <= $repeatNum; $i++) {
        // 子进程执行程序
        $ppid = posix_getppid();
        $pid = posix_getpid();

        echo date('Y-m-d H:i:s') , ' ';
        echo "PPID:{$ppid} , PID:{$pid} child progress is running! {$i} \n";
        $rand = rand(1,3);
        sleep($rand);
    }

    echo date('Y-m-d H:i:s') , ' ';
    exit("({$pid})child progress end!\n");
} else {
    // 父进程执行程序
    $childList[$pid] = 1;
}

// 延迟90秒，父进程退出
sleep(90);

echo date('Y-m-d H:i:s') , ' ';
echo "({$parentPid})main progress end!\n";