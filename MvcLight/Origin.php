<?php

namespace MvcLight;

use MvcLight\Model\Base as BaseModel;
use MvcLight\Core\App;

class Origin {

    private $app = null;
    private $controller;
    private $action;
    private $param;

    public function run() {
        self::access();
    }

    public static function getStatic() {
        return new static();
    }

    public function getApp() {
        return $this->app;
    }

    public function init($param) {
        $this->app = new App((isset($param['env']) && $param['env'] == 'PRO') ? $param['env'] : 'DEV');
        $info = include ROOTDIR . DS . 'application' . DS . 'config' . DS . 'db.php';
        BaseModel::getInstance()->init($info, $this->app);
        return $this;
    }

    private function access() {
        self::checkRoute();
//        $result = self::runAction();
        $result = $this->app->runAction($this->controller, $this->action);
        self::runView($result['view'], $result['data']);
    }

    private function runView($view, $data = array()) {
        self::checkView($view);
        $data['app'] = $this->app;
        try {
            echo $this->app->getTwig()->render($view, $data);
        } catch (\Twig_Error $ex) {
            self::addError('Twig File Error!', "Messeger: " . $ex->getMessage(), 500);
            self::checkError();
        }
    }

    private function checkView($view) {
        $dir_view = ROOTDIR . DS . 'application' . DS . 'view';
        if (!is_file($dir_view . DS . $view)) {
            self::addError('View error!', "File: <b>\"" . str_replace('/', '\\', $dir_view . DS . $view) . "\"</b> is not exist!", 500);
            self::checkError();
        }
    }

//    public function runAction($ctrl = null, $act = null) {
//        $controller = ($ctrl) ? $ctrl : $this->controller;
//        $class = '\\App\\Controller\\' . $controller;
//        if (!class_exists($class)) {
//            self::addError('Controller error!', "Class: <b>\"$class\"</b> is not exist!", 404);
//            self::checkError();
//        } else {
//            self::checkInstantiable($class);
//            $action = ($act) ? $act : $this->action;
//            if (!method_exists($class, $action)) {
//                self::addError('Action error!', "Method: <b>\"$class::$action\"</b> is not exist!", 404);
//                self::checkError();
//            } else {
//                return call_user_func_array(
//                        array(
//                    new $class(),
//                    $action
//                        ), $this->app->getRoute()->get('seleted_route')['param_route']
//                );
//            }
//        }
//    }

//    private function checkInstantiable($class) {
//        $reflectionClass = new \ReflectionClass($class);
//        if (!$reflectionClass->IsInstantiable()) {
//            self::addError('Controller error!', "Class: <b>\"$class\"</b> is can not initialize!", 404);
//            self::checkError();
//        }
//    }

    private function checkRoute() {
        $_path = $this->app->getRequest()->get('path');
        $check_path = $this->app->getRoute()->run($_path);
        if ($check_path) {
            self::set_value_route($this->app->getRoute());
        } else {
            self::addError('Route error!', 'Can not found a router for your path!<br><i><b>Your Path: </b>' . $_path . '</i>', 404);
            self::checkError();
        }
    }

    private function set_value_route($routeObj) {
        $info_route = $routeObj->get('seleted_route');
        $name_route = $info_route['name_route'];
        $param_route = $info_route['param_route'];
        $seleted_route = $routeObj->getRoute($name_route);
        self::checkMethod($seleted_route);
        $ca_string = explode(':', $seleted_route[1]);
        if (!isset($ca_string[0]) || $ca_string[0] == '' || !isset($ca_string[1]) || $ca_string[1] == '') {
            self::addError('Route error!', 'Your route is not invalid same structure!', 404);
            self::checkError();
        }
        $this->controller = $ca_string[0] . 'Controller';
        $this->action = $ca_string[1] . 'Action';
        $this->param = $param_route;
    }

    private function checkMethod($seleted_route) {
        $cur_method = $this->app->getRequest()->get('method');
        $allow_method = (isset($seleted_route[2])) ? $seleted_route[2] : '';
        if ($allow_method !== '' && $allow_method !== $cur_method) {
            self::addError('Route error!', 'Method access is not invalid!', 405);
            self::checkError();
        }
    }

    public function addError($name, $msg, $state) {
        $this->app->addError($name, $msg, $state);
    }

    public function checkError() {
        return $this->app->checkError();
    }

}
