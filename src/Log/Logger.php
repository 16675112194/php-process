<?php
// +----------------------------------------------------------------------
// |  
// | Logger.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-31 22:32
// +----------------------------------------------------------------------

namespace Wanglelecc\Log;

use Wanglelecc\Component\Singleton;
use Wanglelecc\Exceptions\SystemException;

/**
 * 日志使用类
 *
 * @package Wanglelecc\Log
 *
 * @Author wll
 * @Time 2020-01-31 22:33
 */
class Logger
{
    use Singleton;

    /**
     * log method support
     *
     * @var array
     */
    private static $methodSupport = ['info', 'notice', 'warning', 'error', 'debug'];

    /**
     * the log name
     *
     * @var string
     */
    private $logName = 'app.log';

    /**
     * the is json
     *
     * @var bool
     */
    private $isJson = true;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * initialize
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 23:17
     */
    protected function initialize()
    {
        $this->isJson = (bool)config('logging.json', true);
    }

    /**
     * Log an error message to the logs.
     *
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a notice to the logs.
     *
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return void
     */
    public function write($level, $message, array $context = [])
    {
        $this->writeLog($level, $message, $context);
    }

    /**
     * Write a message to the log.
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return void
     */
    protected function writeLog($level, $message, $context)
    {
        $message = $this->formatMessage($message);

        $logFile = log_file_path($this->logName);

        try{
            $logDir = dirname( $logFile );
            if ( !file_exists( $logDir ) ) {
                mkdir( $logDir, config('logging.permission', 0777), true );
            }else{
                chmod($logDir, config('logging.permission', 0777));
            }

            error_log($this->decorate($level, $message, $context), 3, $logFile);
        }catch (\Exception $exception){
            echo $exception->getMessage();
            exit;
        }

    }


    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     * @return mixed
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        }

        return $message;
    }

    /**
     * 静态入口
     *
     * @param string $method
     * @param array $parameters
     *
     * @throws SystemException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 23:02
     */
    public static function __callStatic($method = '', $parameters = [])
    {
        if (!in_array($method, self::$methodSupport)) {
            throw new SystemException('logger method not support', 500);
        }

        self::getInstance()->$method(...$parameters);
    }

    /**
     * 处理日志格式
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @param array $extra
     *
     * @return false|string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-01 22:28
     */
    private function decorate($level = 'info', $message = '', $context = [], $extra = [])
    {
        $time        = date('Y-m-d H:i:s');
        $pid         = posix_getpid();
        $memoryUsage = round(memory_get_usage() / 1024, 2) . ' kb';

        switch ($level = strtolower($level)) {
            case 'info':
                $cRank = "\033[36m{$level} \033[0m";
                break;
            case 'error':
                $cRank = "\033[31m{$level}\033[0m";
                break;
            case 'debug':
                $cRank = "\033[32m{$level}\033[0m";
                break;

            default:
                $cRank = $level;
                break;
        }

        $msg = [
            '@timestamp'   => $time,
            'host'         => gethostname(),
            'env'          => config('app.env'),
            'level'        => $cRank,
            'message'      => $message,
            'pid'          => $pid,
            'memory_usage' => $memoryUsage,
            'context'      => json_encode($context, JSON_UNESCAPED_UNICODE),
            'extra'        => json_encode($extra, JSON_UNESCAPED_UNICODE),
        ];

        // 命令行模式下输出日志内容
        if (php_sapi_name() == 'cli') {
            echo implode(' | ', array_values($msg)) . PHP_EOL;
        }

        $msg['level'] = $level;

        return $this->isJson ? json_encode($msg, JSON_UNESCAPED_UNICODE) . PHP_EOL : implode(' | ', $msg) . PHP_EOL;
    }
}