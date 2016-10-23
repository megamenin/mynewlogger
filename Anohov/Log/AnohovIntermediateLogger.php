<?php

namespace Anohov\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

abstract class AnohovIntermediateLogger extends AbstractLogger implements LoggerInterface
{
    protected function getDate()
    {
        return date('Y-m-d H:i:s');
    }

    protected function contextToString(array $context = array())
    {
        $result = array();
        foreach ($context as $key => $val) {
            $result[$key] = $this->messageToString($val);
        }

        return $result;
    }

    protected function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{'.$key.'}'] = $val;
        }

        return strtr($message, $replace);
    }

    protected function messageToString($message)
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

    abstract public function setLogSetting(array $setting);
}
