<?php 
use ZeroPHP\ZeroPHP\Theme;

class ZdashboardController extends Controller {

    public function index() {
        $zerophp->response->addContent('zdashboard', zerophp_lang('Admin Control Panel'));
    }
}