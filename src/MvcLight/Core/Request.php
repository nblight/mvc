<?php

namespace MvcLight\Core;

class Request {
    
    private $server;
    private $path;
    private $port;
    private $scheme;
    private $method;
    private $url;
    private $request_time;

    public function __construct() {
        $this->server = strtolower($_SERVER['SERVER_NAME']);
        
        
        $path_current = strtolower($_SERVER['REQUEST_URI']);
        if (strpos($path_current, '?')) {
            $path_current = substr($path_current, 0, strpos($path_current, '?'));
        }
        $this->path = $path_current;
        
        
        $this->port = strtolower($_SERVER['SERVER_PORT']);
        $this->scheme = strtolower($_SERVER['REQUEST_SCHEME']);
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->url = strtolower($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        $this->request_time = $_SERVER['REQUEST_TIME'];
    }
    
    public function get($key){
        switch ($key){
            case 'server':
                return $this->server;
            case 'path':
                return $this->path;
            case 'port':
                return $this->port;
            case 'scheme':
                return $this->scheme;
            case 'method':
                return $this->method;
            case 'url':
                return $this->url;
            case 'request_time':
                return $this->request_time;
        }
    }
    
}