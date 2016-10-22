<?php

namespace Anohov\Log;

use Psr\Log\LogLevel;

class AnohovSTDOutLogger extends AnohovIntroduceLogger
{
    public static $logger;
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
                fputs($this->fp, $result);
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

    public function setLogSetting(array $setting = array())
    {
        $this->clearLogSetting();
        $this->fp = fopen('php://stdout', 'a');
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
