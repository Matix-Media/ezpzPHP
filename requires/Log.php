<?php


abstract class LogTypes
{
    const LOG = "log";
    const ERROR = "error";
    const INFO = "info";
    const DEBUG = "debug";
    const WARN = "warn";

    static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}

class Log
{
    public static function console()
    {
        $arguments = func_get_args();
        $args = [];
        $type = LogTypes::LOG;

        if (count($arguments) < 1)
            throw new ErrorException("The log requires at least one value.");

        // Checking first argument for log type
        if (count($arguments) > 1) {
            foreach (LogTypes::getConstants() as $var => $val) {
                if ($val === $arguments[0]) {
                    $type = $arguments[0];
                    array_shift($arguments);
                    break;
                }
            }
        }

        // Outputting 
        foreach ($arguments as $a) {
            switch (gettype($a)) {
                case 'boolean':
                    // Boolean Output
                    if ($a === true)
                        $args[] = "true";
                    else
                        $args[] = "false";
                    break;

                case 'double':
                case 'string':
                case 'integer':
                    // String & Number Output
                    $args[] = "\"" . str_replace("\"", "'", strval($a)) . "\"";
                    break;

                case 'array':
                    // Array Output
                    $arr = json_encode($a, JSON_HEX_QUOT);
                    $args[] = "JSON.parse('$arr')";
                    break;

                case 'object':
                    // Object Output
                    $args[] = "\"" . str_replace("\"", "'", print_r($a, true)) . "\"";
                    break;

                case 'resource':
                    // Resource Output
                    $args[] = "\"" . str_replace("\"", "'", get_resource_type($a)) . "\"";
                    break;

                case 'NULL':
                    // NULL Output
                    $args[] = "\"NULL\"";

                case 'unknown type':
                    // Unknown type Output
                    $args[] = "\"[unknown type]\"";
            }
        }

        // Forming JS
        $output = "<script class=\"framework-log\">console.$type(";
        foreach ($args as $a) {
            $output .= $a . ", ";
        }
        $output .= ");</script>";

        echo $output;
    }
}
