<?php
// +----------------------------------------------------------------------
// |  
// | console.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-28 11:35
// +----------------------------------------------------------------------

define('BASE_PATH', getcwd());

require BASE_PATH . DIRECTORY_SEPARATOR . "bootstrap/app.php";

use Wanglelecc\Log\Logger;
use Wanglelecc\Process\Manager;

/**
 * 命令行参数list
 */
$help = function () {
    $string =  <<<HELP
    \033[36m Usage \033[0m:
        php console start

    \033[36m Example \033[0m:
        php console start
        php console start dev
        php console start pro
        php console stop
        php console status
        php console list

An object-oriented multi process manager for PHP

Version: 1.0.0
    \n
HELP;

    die($string);
};


/**
 * 获取参数
 */
if (count($argv) === 1) {
    $help();
}
$input = [];
foreach ($argv as $v) {
    preg_match_all('/^--(.*)/', $v, $match);
    if (isset($match[1][0]) && ! empty($match[1][0])) {
        $match = explode('=', $match[1][0]);
        if ($match[0] === 'help') {
            $help();
        }
        if (isset($match[1])) {
            $input[$match[0]] = $match[1];
        }
    }
}

$cmd = strtolower($argv['1'] ?? '');
$env = strtolower($argv['2'] ?? 'dev');

switch ($cmd){
    case 'start':
        Manager::getInstance()->start($env);
        break;
    case 'restart': // reload / restart
        Manager::getInstance()->stop(SIGUSR1);
        break;
    case 'stop':
        Manager::getInstance()->stop(SIGUSR2);
        break;
    case 'quit': // terminate
        Manager::getInstance()->stop(SIGTERM);
        break;
    case 'status':
        Manager::getInstance()->status();
        break;
    case 'list':
        Manager::getInstance()->status();
        break;
    case 'debug':
        Wanglelecc\Log\Logger::getInstance()->info('test...');
        break;
    case 'help':
    default :
        $help();
        break;
}
