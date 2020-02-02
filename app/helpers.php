<?php
// +----------------------------------------------------------------------
// |  
// | helpers.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-01-28 11:29
// +----------------------------------------------------------------------

use Wanglelecc\Config\Config;

if( !function_exists('config') )
{
    /**
     * 获取配置文件
     *
     * @param $key
     * @param null $default
     *
     * @return array|mixed|null
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2019-12-01 21:34
     */
    function config($key, $default = null){
        return Config::getInstance()->get($key, $default);
    }
}

if( !function_exists('base_path') )
{
    /**
     * 获取系统根路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function base_path(){
        return BASE_PATH;
    }
}

if( !function_exists('app_path') )
{
    /**
     * 获取 app 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function app_path(){
        return BASE_PATH . DIRECTORY_SEPARATOR . 'app';
    }
}

if( !function_exists('config_path') )
{
    /**
     * 获取 config 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function config_path(){
        return BASE_PATH . DIRECTORY_SEPARATOR . 'config';
    }
}

if( !function_exists('storage_path') )
{
    /**
     * 获取 storage 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function storage_path(){
        return BASE_PATH . DIRECTORY_SEPARATOR . 'storage';
    }
}

if( !function_exists('logs_path') )
{
    /**
     * 获取 logs 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function logs_path(){

        $dir = config('path.logs', 'logs');

        if( substr($dir,0, 1) == '/' ){
            return $dir;
        }

        return storage_path() . DIRECTORY_SEPARATOR . $dir;
    }
}

if( !function_exists('cache_path') )
{
    /**
     * 获取 cache 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function cache_path(){

        $dir = config('path.cache', 'cache');

        if( substr($dir,0, 1) == '/' ){
            return $dir;
        }

        return storage_path() . DIRECTORY_SEPARATOR . $dir;
    }
}

if( !function_exists('tmp_path') )
{
    /**
     * 获取 tmp 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function tmp_path(){

        $dir = config('path.tmp', 'cache');

        if( substr($dir,0, 1) == '/' ){
            return $dir;
        }

        return storage_path() . DIRECTORY_SEPARATOR . $dir;
    }
}

if( !function_exists('pipe_path') )
{
    /**
     * 获取 pipe 路径
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 17:37
     */
    function pipe_path(){

        $dir = config('path.pipe', 'pipe');

        if( substr($dir,0, 1) == '/' ){
            return $dir;
        }

        return storage_path() . DIRECTORY_SEPARATOR . $dir;
    }
}

if( !function_exists('log_file_path') )
{
    /**
     * 获取 log 文件路径
     *
     * @param string $filename 日志名称
     *
     * @return string
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-01-31 19:25
     */
    function log_file_path($filename = 'app.log'){

        if(empty($filename)){
            return null;
        }

        $filename = strtolower($filename);

        if(strlen($filename) > 4 && substr($filename, -4) == '.log'){
            $filename = substr($filename, 0, -4);
        }

        $dir = logs_path();

        // 处理按天分割方式
        if(config('logging.channels', 'daily') == 'daily'){
            $dir = $dir . DIRECTORY_SEPARATOR . date('Ym');
            $filename = $filename . '.' . date('Y-m-d');
        }

        return $dir . DIRECTORY_SEPARATOR . $filename . '.log';
    }
}
