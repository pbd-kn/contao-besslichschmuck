<?php

declare(strict_types=1);

/*
 *
 */

namespace PBDKN\Efgco4\Resources\ContaoBesslichschmuck\classes;

use Contao\StringUtil;

/**
 * Class EfgLog.
 *
 * @copyright  PBD 2021..
 * @license    LGPL
 */
class BLog
{
    protected static $cnt = 0;
    protected static int $myefgdebuglevel = 0;
    protected static $uniqid = 0;
    

    /* setzt den debugmode
     */
    public static function setDebugmode($key): void
    {
//\System::log("PBD BwriteLog key '" . $key."'", __METHOD__, TL_GENERAL);
        if (is_numeric($key)) {
            self::$myefgdebuglevel = $key;
            $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
            self::$uniqid = $arrUniqid[1];
        }

        return;
    }

    /**
     * Write in log file, if debug is enabled.
     *
     * @param int    $level
     * @param string $method
     * @param int    $line
     * @param string $value
     */
    public static function WriteLog($level, $method, $line, $value): void
    {
        if ('' === self::$debFormKey) {
            return;
        }
        $method = trim($method);
        $arr = StringUtil::trimsplit('\\', $method);
        $vclass = $arr[\count($arr) - 1];

        if (\is_array($value)) {
            $value = print_r($value, true);
        }
        if (((int)$level & self::$myefgdebuglevel) === $level) {
            self::logMessage(sprintf('[%s] [%s] [%s:%s] %s', self::$uniqid, $level, $vclass, $line, 'PBD '.$value), 'bessl_debug');
        }
    }

    /**
     * Write in stack log file, if debug is enabled.
     *
     * @param int $level
     */
    public static function WriteStack($level): void
    {
        if ('' === self::$debFormKey) {
            return;
        }
        if (($level & self::$myefgdebuglevel) === $level) {
            $barr = debug_backtrace();
            foreach ($barr as $k => $v) {
                if (str_contains($v['file'], 'symfony')) {
                    $str = 'file: '.$v['file'].' line: '.$v['line'].' function: '.$v['function'];
                } else {
                    $str = 'file: '.$v['file'].' line: '.$v['line'].' function: '.$v['function'];
                    foreach ($v['args'] as $k1 => $v1) {
                        if (\is_array($v1)) {
                            $str .= " isarray[$k1]: [ ";
                            foreach ($v1 as $k2 => $v2) {
                                if (\is_string($v2)) {
                                    $str .= " [$k2]:$v2, ";
                                } else {
                                    if (\is_object($v2)) {
                                        $str .= "value von $k1 ist ein Object class  ".\get_class($v2);
                                    } else {
                                        $str .= "value von $k1 kein string ";
                                    }
                                }
                            }
                            $str .= '],  ';
                        } else {
                            if (\is_string($v1)) {
                                $str .= " args[$k1]:$v1, ";
                            } else {
                                if (\is_object($v1)) {
                                    $str .= "value von $k ist ein Object class  ".\get_class($v1);
                                } else {
                                    $str .= "value von $k kein string ";
                                }
                            }
                        }
                    }
                }
                self::logMessage(sprintf('[%s] [%s] %s', self::$uniqid, $level, 'PBD '.$str), 'efg_debug');
            }
        }
    }

    /**
     * Wrapper for old log_message.
     *
     * @param string     $strMessage
     * @param mixed|null $strLog
     */
    public static function logMessage($strMessage, $strLog = null): void
    {
        $env = $_SERVER['APP_ENV'] ?? 'prod';

        if (null === $strLog) {
            $strLog = $env.'-'.date('Y-m-d').'.log';
        } else {
            $strLog = $env.'-'.date('Y-m-d').'-'.$strLog.'.log';
        }

        $strLogsDir = null;

        if (($container = \System::getContainer()) !== null) {
            $strLogsDir = $container->getParameter('kernel.logs_dir');
        }

        if (!$strLogsDir) {
            $strLogsDir = \System::getContainer()->getParameter('kernel.project_dir').'/var/logs';
        }

        error_log(sprintf("[%s] %s\n", date('d-M-Y H:i:s'), $strMessage), 3, $strLogsDir.'/'.$strLog);
    }

    /**
     * Triggers a silenced warning notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     */
    public static function triggerWarning(string $package, string $version, string $message, ...$args): void
    {
        @trigger_error(($package || $version ? "Since $package $version: " : '').($args ? vsprintf($message, $args) : $message), E_USER_WARNING);
    }

    /**
     * Triggers a silenced deprecation notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     */
    public function triggerDeprecation(string $package, string $version, string $message, ...$args): void
    {
        trigger_deprecation($package, $version, $message, ...$args);
    }
}
