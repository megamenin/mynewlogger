<?php

require 'vendor/autoload.php';

use Psr\Log\LogLevel;
use Anohov\Log\AnohovLogger as Logger;
use Anohov\Log\AnohovLoggerTarget as LoggerTarget;

$message = 'I have {cats} cats and {dogs} dogs';

$context = array(
    'cats' => 'three',
    'dogs' => 2,
);

$setting1 = array(
    'LOG_TARGET' => LoggerTarget::FILE,
    'LOG_PATH' => __DIR__,
    'LOG_FILENAME' => 'file.log',
);

$setting2 = array(
    'LOG_TARGET' => LoggerTarget::DATABASE,
    'LOG_HOST' => 'localhost',
    'LOG_USERNAME' => 'root',
    'LOG_USERPASSWORD' => 'qwerty123',
    'LOG_DBNAME' => 'mytest',
    'LOG_TABLENAME' => 'logs',
);

$setting3 = array(
    'LOG_TARGET' => LoggerTarget::STDOUT,
);

$logger = Logger::getLogger();

$logger->setLogSetting($setting1);
$logger->alert($message, $context);

$logger->setLogSetting($setting2);
$logger->log(LogLevel::NOTICE, $message, $context);

$logger->setLogSetting($setting3);
$logger->log(LogLevel::ERROR, $message, $context);
