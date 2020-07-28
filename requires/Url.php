<?php


class URL
{
    private $url = null;
    public  $scheme, $host, $port, $user, $pass, $path, $query, $fragment, $path_parts;

    public function __construct($url)
    {
        $this->url = $url;
        $url_parts = parse_url($this->url);

        foreach ($url_parts as $key => $val) {
            $this->$key = $val;
        }

        $this->path_parts = explode("/", trim($this->path, "/"));

        return $url;
    }

    public function __toString()
    {
        // Build URL
        $this->url = "";
        if ($this->scheme)
            $this->url .= $this->scheme . '://';

        if ($this->host)
            $this->url .= $this->host;

        if ($this->path)
            $this->url .= $this->path;

        if ($this->query)
            $this->url .= '?' . $this->query;

        if ($this->fragment)
            $this->url .= "#" . $this->fragment;

        return $this->url;
    }

    public function add_parameter($parameter, $value = "")
    {
        // If URL doesn't have a query string.
        if (isset($this->query)) { // Avoid 'Undefined index: query'
            parse_str($this->query, $params);
        } else {
            $params = array();
        }

        $params[$parameter] = $value;     // Overwrite if exists

        // Note that this will url_encode all values
        $this->query = http_build_query($params);

        return $this->__toString();
    }
}
