<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 09.11.16
 * Time: 16:38
 */

namespace PbxParser;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    const STR_LEN = 25;

    private $debug = false;

    public function logSessionNotFound($id) {
        $this->log('notice', "session not found: $id");
    }

    public function logIpChange($auth, $sid, $oldIp, $newIp, $type) {
        $this->log('warinig', 'ip cange');
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()) {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()) {
        $context['trace'] = debug_backtrace();
        $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()) {
        $context['trace'] = debug_backtrace();
        $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()) {
        $context['trace'] = debug_backtrace();
        $this->log('error', $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array()) {
        $context['trace'] = debug_backtrace();
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()) {
        $context['trace'] = debug_backtrace();
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()) {
        if ($this->debug) {
            $this->log('info', $message, $context);
        }
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()) {
        if ($this->debug) {
            $this->log('debug', $message, $context);
        }
    }

    public function getExceptionLog(\Throwable $ex, $msg) {
        $out = $msg . "with message '" . $ex->getMessage() . "' at " . $ex->getFile() . ":" . $ex->getLine() . "\n";
        $out .= $this->buildTrace($ex->getTrace()) . "\n";
        if ($ex->getPrevious()) {
            $out .= $this->getExceptionLog($ex->getPrevious(), 'Previous Exception:');
        }

        return $out;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()) {

        $keys = [];
        $values = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $keys[] = "{{$key}}";
                $values[] = $value;
            }
        }

        $out = str_replace($keys, $values, $message);

        if (isset($context['exception'])) {
            $desc = isset($context['exception_desc']) ? $context['exception_desc'] : 'Founded Exception';
            $out .= $this->getExceptionLog($context['exception'], $desc);
        }

        if (isset($context['trace'])) {
            $out .= "\n" . $this->buildTrace($context['trace']);
        }

        if (php_sapi_name() == "cli") {
            $request = implode(" ", $_SERVER['argv']);
        } else {
            $request = $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $out .= "\nRequest: " . $request;

        $out .= "\n";

        /*if (ini_get('display_errors')) {
            echo $out . "\n";
        }*/
        error_log("[{$level}] " . $out);
        die();
    }

    public function error_handler($errno,  $errstr,  $errfile, $errline, array $errcontext = []) {

        $errcontext['trace_reduce'] = 3;
        switch ($errno) {
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_ERROR:
                $this->critical($errstr, $errcontext);
                break;
            case E_USER_WARNING:
            case E_WARNING:
                $this->warning($errstr, $errcontext);
                break;
            default:
                $this->notice($errstr, $errcontext);
        }
    }

    public function exception_handler(\Throwable $e) {

        $this->log(
            "Uncaught Exception",
            '',
            ['exception' => $e, 'exception_desc' => '']
        );
    }

    public function buildTrace($trace) {
        $out = [];
        $length = [0, 0];
        $items = [];
        foreach ($trace as $i => $item) {
            $point = [];
            $length[0] = max($length[0], mb_strlen('#' . $i));
            if (isset($item['file'])) {
                $file = $item['file'] . ":" . $item['line'];
            } else {
                $file = '[internal function]';
            }
            $length[1] = max($length[1], mb_strlen($file));
            $point['id'] = '#' . $i;
            $point['file'] = $file;
            $line = (isset($item['object']) ? (get_class($item['object']) . $item['type']) : "") . $item['function'];

            $args = [];

            if (isset($item['args'])) {
                foreach ($item['args'] as $j => $arg) {
                    if (is_string($arg)) {
                        $arg = str_replace(["\r", "\n"], ['\r', '\n'], $arg);

                        if (mb_strlen($arg) > self::STR_LEN) {
                            $args[] = mb_strcut($arg, 0, self::STR_LEN) . "…";
                        } else {
                            $args[] = $arg;
                        }
                    } elseif (is_object($arg)) {
                        $name = get_class($arg);

                        if ($arg instanceof LoggerElement) {
                            $name .= '[' . $arg->getLoggerId() . ']';
                        }

                        if (mb_strlen($name) > self::STR_LEN) {
                            $name = "…" . mb_strcut($name, mb_strlen($name) - self::STR_LEN);
                        }
                        $args[] = $name;
                    } elseif (is_array($arg)) {
                        $args[] = 'array[' . count($arg) . "]";
                    } else {
                        $args[] = '' . $arg;
                    }
                }
            }

            $point['line'] = $line . "(" . implode(",", $args) . ")";
            $items[] = $point;
        }

        foreach ($items as $item) {
            $id = $item['id'];

            for ($i = mb_strlen($id); $i < $length[0]; $i++) {
                $id = " " . $id;
            }
            $point = $id;

            $file = $item['file'];
            for ($i = mb_strlen($file); $i < $length[1]; $i++) {
                $file .= " ";
            }
            $point .= " " . $file;
            $point .= " " . $item['line'];
            $out[] = $point;
        }

        return implode("\n", $out);
    }
}