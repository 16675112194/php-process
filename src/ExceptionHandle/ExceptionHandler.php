<?php
// +----------------------------------------------------------------------
// |  
// | ExceptionHandler.php
// | 
// +----------------------------------------------------------------------
// | Copyright (c) https://www.56br.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author:  wll <wanglelecc@gmail.com>
// +----------------------------------------------------------------------
// | Date: 2020-02-02 16:44
// +----------------------------------------------------------------------

namespace Wanglelecc\ExceptionHandle;

use Exception;

class ExceptionHandler
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param Exception $e
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:53
     */
    public function render(Exception $e)
    {
        throw $e;
    }

    /**
     * Render an exception to the console.
     *
     * @param Exception $e
     *
     * @throws Exception
     *
     * @author wll <wanglelecc@gmail.com>
     * @date 2020-02-02 16:53
     */
    public function renderForConsole( Exception $e)
    {
        throw $e;
    }
}