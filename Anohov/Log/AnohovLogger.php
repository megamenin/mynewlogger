<?php

namespace Anohov\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AnohovLogger extends AbstractLogger implements LoggerInterface
{
    private static $LOG_TARGET;
    private static $LOG_DBNAME;
    private static $LOG_TABLENAME;
    private static $LOG_USERNAME;
    private static $LOG_USERPASSWORD;
    private static $LOG_HOST;

    private static $LOG_FILENAME;
    private static $LOG_PATH;

    public static $logger;
    private $fp;
    private $connect;

    private function __construct()
    {
    }

    private function getDate()
    {
        return date('Y-m-d H:i:s');
    }

    private function contextToString(array $context = array())
    {
        $result = array();
        foreach ($context as $key => $val) {
            $result[$key] = $this->messageToString($val);
        }

        return $result;
    }

    private function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{'.$key.'}'] = $val;
        }

        return strtr($message, $replace);
    }

    private function messageToString($message)
    {
        if (is_string($message)) {
            return $message;
        } elseif (is_bool($message) || is_numeric($message) || is_null($message)) {
            return (string) $message;
        }
        if (is_array($message)) {
            $result = print_r($message, true);

            return $result;
        }
        if (is_object($message)) {
            if (get_class($message) == 'Exception') {
                $result = $message->__toString();

                return $result;
            }
            $result = print_r($message, true);

            return $result;
        }

        return null;
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

                switch (self::$LOG_TARGET) {
                case AnohovLoggerTarget::DATABASE:
                    $query = 'INSERT INTO `'.self::$LOG_TABLENAME.
                        '` VALUES (NULL, "'.addslashes($result).'", "'.$this->getDate().'");';
                    if (!$this->connect->query($query)) {
                        throw new \Exception('invalid query');
                    }
                    break;
                case AnohovLoggerTarget::FILE:
                    $result = '['.$this->getDate().']: '.$result."\n\n";
                    fwrite($this->fp, $result);
                    break;
                case AnohovLoggerTarget::STDOUT:
                    $result = '['.$this->getDate().']: '.$result."\n\n";
                    fputs($this->fp, $result);
                    break;
                }
                break;
            default:
                throw new \Exception('unknown RFC 5424 level');
        }
    }

    private function clearLogSetting()
    {
        switch (self::$LOG_TARGET) {
        case AnohovLoggerTarget::DATABASE:
            self::$logger->connect->close();
            break;
        case AnohovLoggerTarget::FILE:
            fclose(self::$logger->fp);
            break;
        case AnohovLoggerTarget::STDOUT:
            fclose(self::$logger->fp);
            break;
        }
    }

    public function setLogSetting(array $setting)
    {
        $this->clearLogSetting();

        if (!isset($setting['LOG_TARGET'])) {
            throw new \Exception('LOG_TARGET is not specified');
        }
        self::$LOG_TARGET = $setting['LOG_TARGET'];

        switch (self::$LOG_TARGET) {
            case AnohovLoggerTarget::DATABASE:
                if (!isset($setting['LOG_HOST'])) {
                    throw new \Exception('missing LOG_HOST in setting\'s array');
                }
                self::$LOG_HOST = $setting['LOG_HOST'];
                if (!isset($setting['LOG_USERNAME'])) {
                    throw new \Exception('missing LOG_USERNAME in setting\'s array');
                }
                self::$LOG_USERNAME = $setting['LOG_USERNAME'];
                if (!isset($setting['LOG_USERPASSWORD'])) {
                    throw new \Exception('missing LOG_USERPASSWORD in setting\'s array');
                }
                self::$LOG_USERPASSWORD = $setting['LOG_USERPASSWORD'];
                if (!isset($setting['LOG_DBNAME'])) {
                    throw new \Exception('missing LOG_DBNAME in setting\'s array');
                }
                self::$LOG_DBNAME = $setting['LOG_DBNAME'];
                if (!isset($setting['LOG_TABLENAME'])) {
                    throw new \Exception('missing LOG_TABLENAME in setting\'s array');
                }
                self::$LOG_TABLENAME = $setting['LOG_TABLENAME'];
                $this->connect = new \mysqli(
                    self::$LOG_HOST,
                    self::$LOG_USERNAME,
                    self::$LOG_USERPASSWORD);
                if (!$this->connect->connect_errno) {
                    $this->connect->select_db(self::$LOG_DBNAME);
                    $this->connect->query('SET NAMES utf8');
                } else {
                    throw new \Exception('error connecting to database');
                }
                break;

            case AnohovLoggerTarget::FILE:
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
                    throw new \Exception('file '.$path.' does not exist or does not writable');
                }
                break;

            case AnohovLoggerTarget::STDOUT:
                $this->fp = fopen('php://stdout', 'a');
                break;

            default:
                throw new \Exception('unknown type of LOG_TARGET');
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
