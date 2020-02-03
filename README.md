```

56BR.COM

Multi process manager for PHP

Author: wll <wanglelecc@gmail.com>
Github: https://github.com/wanglelecc/php-process

Version: 1.0.0

```

## 项目背景
一直在寻找轻量级 PHP 版本的多进程管理，用来处理一些计算任务，数据处理...等。要求足够轻量，对环境依赖低，能够在大多数环境下直接运行。
经过一系列筛选，感觉 `naruto` 还不错，试用一番感觉与我的需求还是有些不是很吻合，于是就萌生自己写一个的念头，正好趁着最近有些空闲的时间，不符所望，顺利的出炉。
当然也需要感谢 `naruto` 大量参考了它的设计与部分代码。

## 基本介绍
- 多进程的管理也是参考 pm2，php-fpm, nginx... 等。分为：master 与 worker。
- master 专注管理 worker 不参与业务处理，保证自身的稳定，支持守护进程模式，自定义处理信号控制进程退出。 
- worker 专注处理业务逻辑，有两种类型：切片型(循环消费) 与 单片型(消费完成进程退出)


## 如何使用？

### 依赖环境

- Linux / OS X
- PHP >= 7.2
- Composer
- Pcntl 扩展
- Posix 扩展

### 安装

```bash
git clone https://github.com/wanglelecc/php-process.git php-process
cd php-process
composer install -vvv
```

### 启动示例
```bash
php console.php start
```

### 启动状态
![启动状态](https://wanglelecc.oss-cn-beijing.aliyuncs.com/github/php-process/welcome.png)

### 可用命令
```bash
# 命令行方式
php console.php start

# 守护进程方式
php console.php start pro

# 查看状态
php console.php status

# 重启
php console.php restart

# 停止(软停止不影响业务)
php console.php stop

# 退出(kill会影响业务)
php console.php quit

# 进程状态 - 待开发
# php console.php list

# 调试入口
php console.php debug

# 查看帮助
php console.php help
```

### 目录结构
```

app ---------------| 应用
app/Consumer ------| 消费者
bootstrap ---------| 初始化
config ------------| 配置
src ---------------| 核心
storage -----------| 文件系统
console.php -------| 程序入口

```

## 如何配置进程？

系统设计是根据消费者来选择启动多少进程，所以配置好消费者就可以了。具体的消费者模型可以参考 `App\Consumer\Demo` 来定义。
下面是消费者配置文件( `config/consumer.php` ):

```php
<?php

return [
    "demo" => [
        "workerName"      => "demo",                // 进程名称也是消费者名称
        "workerNum"       => 4,                     // 进程数量
        "consumeSharding" => true,                  // 消费类型: true => 切片, false => 单片
        "consumeClass"    => "\App\Consumer\Demo",  // 业务消费类
        "consumeConfig"   => [

        ],                                          // 消费者参数，需要与消费者的构造方法参数一一对应，默认为空
    ],
];
```
### 进程状态
![进程状态](https://wanglelecc.oss-cn-beijing.aliyuncs.com/github/php-process/php-process.png)

## 项目地址
Github: https://github.com/wanglelecc/php-process

## 最后
如果有写的不对的地方，希望大家及时指正。欢迎PR~

## 鸣谢
- naruto
