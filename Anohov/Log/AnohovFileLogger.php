<?php

namespace Anohov\Log;

use Psr\Log\LogLevel;

class AnohovFileLogger extends AnohovIntermediateLogger
{
    private static $LOG_FILENAME;
    private static $LOG_PATH;

    private static $logger;
    private $fp;

    private function __construct()
    {
    }

    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
            case LogLevel::INFO:
            case LogLevel::DEBUG:
                $contextToStr = $this->contextToString($context);
                $result = '['.strtoupper($level).' MESSAGE]: '.$this->interpolate($message, $contextToStr);
                $result = '['.$this->getDate().']: '.$result."\n\n";
                fwrite($this->fp, $result);
                break;
            default:
                throw new \Exception('unknown RFC 5424 level');
        }
    }

    private function clearLogSetting()
    {
        if (isset(self::$fp)) {
            fclose(self::$logger->fp);
            unset(self::$fp);
        }
    }

    public function setLogSetting(array $setting)
    {
        $this->clearLogSetting();

        if (!isset($setting['LOG_FILENAME'])) {
            throw new \Exception('missing LOG_FILENAME in setting\'s array');
        }
        self::$LOG_FILENAME = $setting['LOG_FILENAME'];
        if (!isset($setting['LOG_PATH'])) {
            throw new \Exception('missing LOG_PATH in setting\'s array');
        }
        self::$LOG_PATH = $setting['LOG_PATH'].'\\';
        $path = self::$LOG_PATH.self::$LOG_FILENAME;
        if (is_writable($path)) {
            $this->fp = fopen($path, 'a');
        } else {
            throw new \Exception('file '.$path.' is not writable');
        }
    }

    public static function getLogger()
    {
        if (self::$logger == null) {
            return self::$logger = new self();
        } else {
            return self::$logger;
        }
    }
}
