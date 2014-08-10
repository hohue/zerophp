<?php
namespace ZeroPHP\Solr;

class Solr {
    function searchResultEmpty() {
        $vars = array();
        $zerophp->response->addContent(zerophp_view('search_search_no_result', $vars));
    }
}