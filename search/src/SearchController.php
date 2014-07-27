<?php
namespace ZeroPHP\ZeroPHP;

class SearchController {



    
    function  search_no_result() {
        $vars = array(
        );
        $zerophp->response->addContent('search_search_no_result|search', 'KẾT QUẢ TÌM KIẾM', $vars);
    }
}