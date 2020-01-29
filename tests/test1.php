<?php
// +----------------------------------------------------------------------
// |  
// | test1.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-29 10:54
// +----------------------------------------------------------------------

$parentPid = posix_getpid();
echo "parent progress pid:{$parentPid}\n";
$childList = array();

for($f = 0; $f < 5; $f++){
    $pid = pcntl_fork();
    if ( $pid == -1) {
        // 创建失败
        exit("fork progress error!\n");
    } else if ($pid == 0) {
        // 子进程执行程序
        $pid = posix_getpid();
        $repeatNum = 5;
        for ( $i = 1; $i <= $repeatNum; $i++) {
            echo "({$pid})child progress is running! {$i} \n";
            $rand = rand(1,3);
            sleep($rand);
        }
        exit("({$pid})child progress end!\n");
    } else {
        // 父进程执行程序
        $childList[$pid] = 1;
    }

    sleep(5);
}


sleep(60);

// 等待子进程结束
pcntl_wait($status);

echo "({$parentPid})main progress end!";