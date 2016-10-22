<?php

require 'vendor/autoload.php';

use Psr\Log\LogLevel;
use Anohov\Log\AnohovLoggerFactory as LoggerFactory;

$message = 'I have {cats} cats and {dogs} dogs';

$context = array(
    'cats' => 'three',
    'dogs' => 2,
);

$setting1 = array(
    'LOG_PATH' => __DIR__,
    'LOG_FILENAME' => 'file.log',
);

$setting2 = array(
    'LOG_HOST' => 'localhost',
    'LOG_USERNAME' => 'root',
    'LOG_USERPASSWORD' => 'qwerty123',
    'LOG_DBNAME' => 'mytest',
    'LOG_TABLENAME' => 'logs',
);

$setting3 = array();

$logger = LoggerFactory::getFileLogger();

$logger->setLogSetting($setting1);
$logger->alert($message, $context);

$logger = LoggerFactory::getDatabaseLogger();

$logger->setLogSetting($setting2);
$logger->log(LogLevel::NOTICE, $message, $context);

$logger = LoggerFactory::getSTDOutLogger();

$logger->setLogSetting($setting3);
$logger->log(LogLevel::ERROR, $message, $context);
