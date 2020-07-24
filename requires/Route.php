<?php


class Route
{

    private static $routes = array();
    private static $pathNotFound = null;
    private static $methodNotAllowed = null;

    public static function add($expression, $function, $method = 'get')
    {
        array_push(self::$routes, array(
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ));
    }

    public static function pathNotFound($function)
    {
        self::$pathNotFound = $function;
    }

    public static function methodNotAllowed($function)
    {
        self::$methodNotAllowed = $function;
    }

    public static function run($basepath = '/')
    {

        // Parse current url
        $parsed_url = parse_url($_SERVER['REQUEST_URI']); //Parse Uri

        if (isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
        } else {
            $path = '/';
        }

        // Get current request method
        $method = $_SERVER['REQUEST_METHOD'];

        $path_match_found = false;

        $route_match_found = false;

        foreach (self::$routes as $route) {

            // If the method matches check the path

            // Add basepath to matching string
            if ($basepath != '' && $basepath != '/') {
                $route['expression'] = '(' . $basepath . ')' . $route['expression'];
            }

            // Add 'find string start' automatically
            $route['expression'] = '^' . $route['expression'];

            // Add 'find string end' automatically
            $route['expression'] = $route['expression'] . '$';

            // echo $route['expression'].'<br />';

            // Check path match
            if (preg_match('#' . $route['expression'] . '#', $path, $matches)) {

                $path_match_found = true;

                // Check method match
                if (strtolower($method) == strtolower($route['method'])) {

                    array_shift($matches); // Always remove first element. This contains the whole string

                    if ($basepath != '' && $basepath != '/') {
                        array_shift($matches); // Remove basepath
                    }

                    call_user_func_array($route['function'], $matches);

                    $route_match_found = true;

                    // Do not check other routes
                    break;
                }
            }
        }

        // No matching route was found
        if (!$route_match_found) {

            // But a matching path exists
            if ($path_match_found) {
                header("HTTP/1.0 405 Method Not Allowed");
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed, array($path, $method));
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                if (self::$pathNotFound) {
                    call_user_func_array(self::$pathNotFound, array($path));
                }
            }
        }
    }

    public static function load_control($control, $arguments = null)
    {
        if ($control === null || $control === "")
            return;

        try {
            $file_path = Utils::join_paths(Utils::$_HOME, "content", "controls", $control);
            include($file_path);
        } catch (ErrorException $ex) {
            Utils::display_error("Unable to load control \"$control\".", $ex);
        }
    }

    public static function load_page($page, $arguments = null)
    {
        if ($page === null || $page === "")
            return;

        try {
            $file_path = Utils::join_paths(Utils::$_HOME, "content", "pages", $page);
            include($file_path);
        } catch (ErrorException $ex) {
            Utils::display_error("Unable to load page \"$page\".", $ex);
        }
    }

    public static function load_view($view, $body_page = null, $title = null, $arguments = null, $header_control = null, $footer_control = null, $head = null)
    {
        $start_time = microtime(true);

        $view_control_clouseure = "[!VIEW_CONTROL]";
        $view_variables = ["header_control", "footer_control"];

        try {
            // Load Context variables and content
            $view_context_variables = [
                "title" => ["type" => "string", "content" => $title],
                "head_content" => ["type" => "string", "content" => "$head\n"],
                "header_content" => ["type" => "control", "content" => $header_control],
                "body_content" => ["type" => "page", "content" => $body_page],
                "footer_content" => ["type" => "control", "content" => $footer_control]
            ];

            // Getting view file
            $view_path = Utils::join_paths(Utils::$_HOME, "content", "views", $view);
            $view_content = file_get_contents($view_path);

            // Getting view vars
            if (substr_count($view_content, $view_control_clouseure) == 2) {
                $begin_view_control = strpos($view_content, $view_control_clouseure) + strlen($view_control_clouseure);
                $end_view_control = strpos(substr($view_content, $begin_view_control), $view_control_clouseure);
            } else {
                throw new ErrorException('Too few / too many view_control_closures ("[! VIEW_CONTROL]") specified. There must be one above and one below the view variable.', 0, 0, $view_path, null);
            }


            $content_view_control = substr($view_content, $begin_view_control, $end_view_control);

            // Iterate over lines and get variables
            $assigned_view_variables = [];
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $content_view_control) as $line) {
                $line = trim($line);

                // Iterate over possible variables
                foreach ($view_variables as $var) {
                    if (strpos($line, "$var=") !== false) {
                        $assigned_view_variables[$var] = substr($line, strlen("$var="));
                        //echo "$" . $var . " = " . $view_variables[$var] . "\n";
                    }
                }
            }
            $view_variables = $assigned_view_variables;

            // Assign variables
            foreach ($view_variables as $var => $val) {
                if ($val !== null || $val !== "") {
                    switch ($var) {
                        case 'header_control':
                            $view_context_variables["header_content"]["content"] = $val;
                            break;

                        case 'footer_control':
                            $view_context_variables["footer_content"]["content"] = $val;
                            break;
                    }
                }
            }

            // Generate output HTML-Script
            $html_content = substr($view_content, 0, $begin_view_control - strlen($view_control_clouseure)) .
                substr($view_content, $begin_view_control + $end_view_control + strlen($view_control_clouseure));

            // Looping trough context variables
            $view_context_variables_positions = [];
            foreach ($view_context_variables as $var => $val) {
                if (strpos($html_content, "$($var)") !== false) {
                    $last_pos = 0;
                    while (($last_pos = strpos($html_content, "$($var)", $last_pos)) !== false) {
                        $view_context_variables_positions[$last_pos] = ["var" => $var, "begin" => $last_pos, "end" => $last_pos + strlen("$($var)")];
                        $last_pos = $last_pos + strlen("$($var)");
                    }
                }
            }

            // Sort array by position
            ksort($view_context_variables_positions);

            //Iterating over context variable positions and printing HTML
            if (count($view_context_variables_positions) > 0) {
                $last_pos = 0;
                foreach ($view_context_variables_positions as $var => $val) {
                    echo substr($html_content, $last_pos, $val["begin"] - $last_pos);

                    switch ($view_context_variables[$val["var"]]["type"]) {
                        case 'string':
                            echo $view_context_variables[$val["var"]]["content"];
                            break;

                        case 'page':
                            self::load_page($view_context_variables[$val["var"]]["content"], $arguments);
                            break;

                        case 'control':
                            self::load_control($view_context_variables[$val["var"]]["content"], $arguments);
                            break;
                    }

                    $last_pos = $val["end"];
                }
                echo substr($html_content, $last_pos);
            } else {
                // If no variables used, print HTML
                echo $html_content;
            }

            $time_elapsed_secs = microtime(true) - $start_time;

            Log::console(LogTypes::DEBUG, "Render time: $time_elapsed_secs");

            //echo "\n\n$begin_view_control-$end_view_control\n$content_view_control";
        } catch (ErrorException $ex) {
            Utils::display_error("Unable to load/generate view \"$view\".", $ex);
        }
    }
}
