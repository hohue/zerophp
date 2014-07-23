<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Request;
use ZeroPHP\ZeroPHP\Response;

class ZeroPHP {

    private static $instance;

    public function __construct() {
        self::$instance = & $this;
    }

    public function bootstrap($request_type = 'get') {
        //@todo 6 Nhan gia tri tu file cau hinh
        define('ZEROPHP_CACHE_EXPIRE_TIME', 10); // 10 minutes

        $this->request = new Request();
        $this->response = new Response();
        $this->response->setOutputType($this->request->prefix());

        $controller = $this->request->getController();
        $class = new $controller['class'];
        $class->$controller['method']($this->getInstance());

        // Flush cache for Development Environment
        if (\Config::get('app.environment', 'production') == 'development') {
            \Cache::flush();
        }

        return $this->response->output();
    }

    public static function &getInstance() {
        return self::$instance;
    }
}