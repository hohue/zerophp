<?php
namespace ZeroPHP\ZeroPHP;

class SearchController {
    function  searchResultEmpty() {
        $vars = array();
        $zerophp->response->addContent(zerophp_view('search_search_no_result', $vars));
    }
}