<?php


class Utils
{
    public static $_HOME = null;

    public static function set_error_handler()
    {
        function exception_error_handler($errno, $errstr, $errfile, $errline)
        {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        }

        set_error_handler("exception_error_handler");
    }

    public static function get_real_path($relative_file_location)
    {
        return realpath($relative_file_location);
    }

    public static function set_home($relative_base_path)
    {
        if (!isset($relative_base_path))
            self::$_HOME = $_SERVER["DOCUMENT_ROOT"];
        else
            self::$_HOME = $relative_base_path;
        return self::$_HOME;
    }

    public static function join_paths()
    {
        $path = '';
        $arguments = func_get_args();
        $args = array();
        foreach ($arguments as $a) if ($a !== '') $args[] = $a; //Removes the empty elements

        $arg_count = count($args);
        for ($i = 0; $i < $arg_count; $i++) {
            $folder = $args[$i];

            if ($i != 0 and $folder[0] == DIRECTORY_SEPARATOR) $folder = substr($folder, 1); //Remove the first char if it is a '/' - and its not in the first argument
            if ($i != $arg_count - 1 and substr($folder, -1) == DIRECTORY_SEPARATOR) $folder = substr($folder, 0, -1); //Remove the last char - if its not in the last argument

            $path .= $folder;
            if ($i != $arg_count - 1) $path .= DIRECTORY_SEPARATOR; //Add the '/' if its not the last element.
        }
        return $path;
    }

    public static function display_error($message, $ex)
    {
        echo "\n<pre class='framework-error-msg'>$message\n$ex</pre>\n";
    }

    public static function print_var_name($var)
    {
        foreach ($GLOBALS as $var_name => $value) {
            if ($value === $var) {
                return $var_name;
            }
        }

        return false;
    }
}
