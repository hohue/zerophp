<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Request;
use ZeroPHP\ZeroPHP\Response;
use ZeroPHP\ZeroPHP\Entity;

class ZeroPHP {

    private static $instance;
    public $entity = array();

    public function __construct() {
        self::$instance = & $this;
    }

    public function bootstrap($request_type = 'get') {
        //@todo 6 Nhan gia tri tu file cau hinh
        define('ZEROPHP_CACHE_EXPIRE_TIME', 10); // 10 minutes

        $this->language = \Config::get('app.locale', 'en');
        $this->translate = array();

        //@todo 9 Support Multi-language
        if ($this->language != 'en') {
            $translate = Entity::loadEntityObject('\ZeroPHP\ZeroPHP\LanguageTranslate');
            $this->translate = $translate->loadEntityAllByLanguage($this->language);
        }
        
        $this->request = new Request();
        $this->response = new Response();
        $this->response->setOutputType($this->request->prefix());

        $controller = $this->request->getController();
        $class = new $controller['class'];
        $class->$controller['method']($this);

        // Flush cache for Development Environment
        if (\Config::get('app.environment', 'production') == 'development') {
            \Cache::flush();
            zerophp_flush_cache_view();
        }

        return $this->response->output();
    }

    public static function &getInstance() {
        return self::$instance;
    }
}