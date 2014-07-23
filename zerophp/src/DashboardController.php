<?php 
use ZeroPHP\ZeroPHP\Theme;

class DashboardController extends Controller {
    public function index($zerophp) {
        $zerophp->response->addContent(zerophp_view('dashboard'), zerophp_lang('Homepage'));
    }
}