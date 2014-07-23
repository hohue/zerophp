<?php
use ZeroPHP\ZeroPHP\Theme;

class SearchController extends Controller {
    function  search_no_result() {
        $vars = array(
        );
        $zerophp->response->addContent('search_search_no_result|search', 'KẾT QUẢ TÌM KIẾM', $vars);
    }
}