<?php 
use ZeroPHP\ZeroPHP\Theme;

class SitemapController extends Controller {
    function category_product() {
    
            $this->load->library('category');
            $level1 = $this->category->loadEntityAll_from_group(1);
            
            $result = array();
            foreach ($level1 as $key => $value) {
                $result[$key] = array(
                    '#title' => $value->title,
                    '#children' => array(),
                );
                
                $children = $this->category->loadEntityAll_from_parent($key);
                foreach ($children as $k => $v) {
                    $result[$key]['#children'][$k] = $v->title;
                }
            }

            $zerophp->response->addContent('sitemap_category_product|sitemap', 'Chọn Chuyên Mục', array('category' => $result));
    }
}