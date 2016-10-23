<?php

namespace Anohov\Log;

use Psr\Log\LogLevel;

class AnohovDatabaseLogger extends AnohovIntermediateLogger
{
    private static $LOG_TARGET;
    private static $LOG_DBNAME;
    private static $LOG_TABLENAME;
    private static $LOG_USERNAME;
    private static $LOG_USERPASSWORD;
    private static $LOG_HOST;

    private static $logger;
    private $connect;

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
                $query = 'INSERT INTO `'.self::$LOG_TABLENAME.
                    '` VALUES (NULL, "'.addslashes($result).'", "'.$this->getDate().'");';
                if (!$this->connect->query($query)) {
                    throw new \Exception('invalid query');
                }
                break;
            default:
                throw new \Exception('unknown RFC 5424 level');
        }
    }

    private function clearLogSetting()
    {
        if (isset(self::$connect)) {
            self::$logger->connect->close();
            unset(self::$connect);
        }
    }

    public function setLogSetting(array $setting)
    {
        $this->clearLogSetting();

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
