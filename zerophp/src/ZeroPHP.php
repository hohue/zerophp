<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Request;
use ZeroPHP\ZeroPHP\Response;
use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class ZeroPHP {

    private static $instance;

    public function __construct() {
        self::$instance = & $this;
    }

    public function bootstrap($request_type = 'get') {
        define('ZEROPHP_CACHE_EXPIRE_TIME', zerophp_variable_get('ZEROPHP_CACHE_EXPIRE_TIME', 10)); // 10 minutes

        $this->language = \Config::get('app.locale', 'en');
        $this->translate = array();

        //@todo 9 Support Multi-language
        if ($this->language != 'en') {
            $translate = new \ZeroPHP\ZeroPHP\LanguageTranslate;
            $this->translate = $translate->loadEntityAllByLanguage($this->language);
        }
        
        $this->request = new Request();
        $this->response = new Response();
        $this->response->setOutputType($this->request->prefix());

        // Process form
        $continue = true;
        if ($request_type == 'post') {
            //Check CSRF
            if (\Session::token() != \Input::get('_token')) {
                $this->response->addMessage(zerophp_lang('This form has expired. Please copy/refresh and try again.'), 'error');
            }
            else {
                $continue = Form::submit();

                // Form redirect
                if ($continue !== true && $continue !== false) {
                    return $continue;
                }
            }
        }

        // Run Controller
        if ($continue) {
            $controller = $this->request->getController();

            if (isset($controller->title)) {
                $this->response->addTitle(zerophp_lang($controller->title));
            }
            $controller->arguments = $controller->arguments ? explode('|', $controller->arguments) : array();
            $arguments = array($this);
            foreach($controller->arguments as $value) {
                $arguments[] = is_numeric($value) ? $this->request->segment($value) : $value;
            }
            
            if ($return = call_user_func_array(array(new $controller->class, $controller->method), $arguments)) {
                return $return;
            }
        }

        // Hack for Responsive File Manager
        //@todo 9 Chuyen den hook_init
        if ($userid = zerophp_userid()) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['userid'] = $userid;
        }

        // Flush cache for Development Environment
        if (\Config::get('app.environment', 'production') == 'development') {
            //\Cache::flush();
            zerophp_flush_cache_view();
        }

        return $this->response->output();
    }

    public static function &getInstance() {
        return self::$instance;
    }
}