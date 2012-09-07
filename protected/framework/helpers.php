<?php




/**
 * Load all functions from the helper file
 *
 * syntax:
 * use_helper('Cookie');
 * use_helper('Number', 'Javascript', 'Cookie', ...);
 *
 * @param  string helpers in CamelCase
 * @return void
 */
function use_helper()
{
    static $_helpers = array();

    $helpers = func_get_args();

    foreach ($helpers as $helper) {
        if (in_array($helper, $_helpers)) continue;

        $helper_file = HELPER_PATH.DIRECTORY_SEPARATOR.$helper.'.php';

        if ( ! file_exists($helper_file)) {
            throw new Exception("Helper file '{$helper}' not found!");
        }

        include $helper_file;
        $_helpers[] = $helper;
    }
}


/**
 * Load model class from the model file (faster than waiting for the __autoload function)
 *
 * syntax:
 * use_model('Blog');
 * use_model('Post', 'Category', 'Tag', ...);
 *
 * @param  string models in CamelCase
 * @return void
 */
function use_model()
{
    static $_models = array();

    $models = func_get_args();

    foreach ($models as $model) {
        if (in_array($model, $_models)) continue;

        $model_file = APP_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$model.'.php';

        if ( ! file_exists($model_file)) {
            throw new Exception("Model file '{$model}' not found!");
        }

        include $model_file;
        $_models[] = $model;
    }
}


/**
 * Get the request method used to send this page
 *
 * @return string possible value: GET, POST or AJAX
 */
function get_request_method()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return 'AJAX';
    else if ( ! empty($_POST)) return 'POST';
    else return 'GET';
}


/**
 * Redirect this page to the url passed in param
 */
function redirect($url)
{
    header('Location: '.$url); exit;
}


/**
 * Encodes HTML safely for UTF-8. Use instead of htmlentities.
 */
function html_encode($string)
{
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}


/**
 * Display a 404 page not found and exit
 */
function page_not_found()
{
    Observer::notify('page_not_found');

    echo new View('404');
    exit;
}


/**
 * @param $num
 *
 * @return string
 */
function convert_size($num)
{
    if ($num >= 1073741824) $num = round($num / 1073741824 * 100) / 100 .' gb';
    else if ($num >= 1048576) $num = round($num / 1048576 * 100) / 100 .' mb';
    else if ($num >= 1024) $num = round($num / 1024 * 100) / 100 .' kb';
    else $num .= ' b';
    return $num;
}


/**
 * Information about time and memory.
 * @return string
 */
function memory_usage()
{
    return convert_size(memory_get_usage());
}


/**
 * @return string
 */
function execution_time()
{
    return sprintf("%01.4f", get_microtime() - FRAMEWORK_STARTING_MICROTIME);
}


/**
 * @return float
 */
function get_microtime()
{
    $time = explode(' ', microtime());
    return doubleval($time[0]) + $time[1];
}


/**
 * @param        $array
 * @param        $label
 * @param string $key_label
 * @param string $value_label
 */
function debug_table($array, $label, $key_label='Variable', $value_label='Value')
{
    echo '<h2>'.$label.'</h2>';
    echo '<table cellpadding="3" cellspacing="0" style="width: 800px; border: 1px solid #ccc">';
    echo '<tr><td style="border-right: 1px solid #ccc; border-bottom: 1px solid #ccc;">'.$key_label.'</td>'.
        '<td style="border-bottom: 1px solid #ccc;">'.$value_label.'</td></tr>';

    foreach ($array as $key => $value)
    {
        if( is_null($value) ) $value = 'null';
        else if( is_array($value) ) $value = 'array['.sizeof($value).']';
        else if( is_object($value) ) $value = get_class($value).' Object';
        else if( is_bool($value) ) $value = $value ? 'true' : 'false';
        else if( is_int($value) ) $value = $value;
        else
        {
            $value = htmlspecialchars(substr($value, 0, 64));
            if (strlen($value) >= 64) $value .= ' &hellip;';
        }
        echo '<tr><td><code>'.$key.'</code></td><td><code>'.$value.'</code></td></tr>';
    }
    echo '</table>';
} // end function


/**
 * Provides a nice print out of the stack trace when an exception is thrown.
 *
 * @param Exception $e Exception object.
 */
function framework_exception_handler($e)
{
    Observer::notify('framework_exception', $e);

    if (!DEBUG)
        page_not_found();

    echo '<style>h1,h2,h3,p,td {font-family:Verdana; font-weight:lighter;}</style>';
    echo '<p>Uncaught '.get_class($e).'</p>';
    echo '<h1>'.$e->getMessage().'</h1>';

    $traces = $e->getTrace();
    if (count($traces) > 1)
    {
        echo '<p><b>Trace in execution order:</b></p>'.
            '<pre style="font-family:Verdana; line-height: 20px">';

        $level = 0;
        foreach (array_reverse($traces) as $trace)
        {
            ++$level;

            if (isset($trace['class'])) echo $trace['class'].'&rarr;';

            $args = array();
            if ( ! empty($trace['args']) )
            {
                foreach( $trace['args'] as $arg )
                {
                    if (is_null($arg)) $args[] = 'null';
                    else if (is_array($arg)) $args[] = 'array['.sizeof($arg).']';
                    else if (is_object($arg)) $args[] = get_class($arg).' Object';
                    else if (is_bool($arg)) $args[] = $arg ? 'true' : 'false';
                    else if (is_int($arg)) $args[] = $arg;
                    else
                    {
                        $arg = htmlspecialchars(substr($arg, 0, 64));
                        if (strlen($arg) >= 64) $arg .= '...';
                        $args[] = "'". $arg ."'";
                    }
                }
            }

            echo '<b>'.$trace['function'].'</b>('.implode(', ',$args).')  ';
            echo 'on line <code>'.(isset($trace['line']) ? $trace['line'] : 'unknown').'</code> ';
            echo 'in <code>'.(isset($trace['file']) ? $trace['file'] : 'unknown')."</code>\n";
            echo str_repeat("   ", $level);
        }
        echo '</pre>';
    }
    echo "<p>Exception was thrown on line <code>"
        . $e->getLine() . "</code> in <code>"
        . $e->getFile() . "</code></p>";

    $dispatcher_status = Dispatcher::getStatus();
    $dispatcher_status['request method'] = get_request_method();
    debug_table($dispatcher_status, 'Dispatcher status');
    if ( ! empty($_GET)) debug_table($_GET, 'GET');
    if ( ! empty($_POST)) debug_table($_POST, 'POST');
    if ( ! empty($_COOKIE)) debug_table($_COOKIE, 'COOKIE');
    debug_table($_SERVER, 'SERVER');
} // end function