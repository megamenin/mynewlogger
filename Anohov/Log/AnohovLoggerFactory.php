<?php

namespace Anohov\Log;

class AnohovLoggerFactory
{
    public static function getFileLogger()
    {
        return AnohovFileLogger::getLogger();
    }

    public static function getDatabaseLogger()
    {
        return AnohovDatabaseLogger::getLogger();
    }

    public static function getSTDOutLogger()
    {
        return AnohovSTDOutLogger::getLogger();
    }
}
