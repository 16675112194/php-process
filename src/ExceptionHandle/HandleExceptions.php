<?php
// +----------------------------------------------------------------------
// |  
// | HandleExceptions.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-02-02 16:34
// +----------------------------------------------------------------------

namespace Wanglelecc\ExceptionHandle;

use Exception;
use ErrorException;
use Wanglelecc\Component\Singleton;

class HandleExceptions
{
    use Singleton;

    /**
     * Reserved memory so that errors can be displayed properly on memory exhaustion.
     *
     * @var string
     */
    public static $reservedMemory;

    /**
     * Bootstrap the given application.
     *
     * @return void
     */
    public function bootstrap()
    {
        self::$reservedMemory = str_repeat('x', 10240);

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        if ( config('app.env', 'develop') !== 'develop' ) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws ErrorException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:55
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param $e
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:54
     */
    public function handleException($e)
    {
        if ( php_sapi_name() === 'cli' ) {
            $this->renderForConsole($e);
        } else {
            $this->renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param Exception $e
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:54
     */
    protected function renderForConsole(Exception $e)
    {
        $this->getExceptionHandler()->renderForConsole($e);
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param Exception $e
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:54
     */
    protected function renderHttpResponse(Exception $e)
    {
        $this->getExceptionHandler()->render($e);
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:55
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->exceptionFromError($error));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param array $error
     *
     * @return ErrorException
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:55
     */
    protected function ExceptionFromError(array $error)
    {
        return new ErrorException(
            $error['message'], $error['code'], 1, $error['file'], $error['line']
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param $type
     *
     * @return bool
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:55
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * Get an instance of the exception handler.
     *
     * @return ExceptionHandler
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:55
     */
    protected function getExceptionHandler()
    {
        return new ExceptionHandler;
    }
}