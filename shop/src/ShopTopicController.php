<?php 
namespace ZeroPHP\Shop;

class ShoptopicController {





    
    function start() {
        $shop_obj = \ZeroPHP\ZeroPHP\Entity::loadEntityObject('ZeroPHP\Shop\ShopTopic');

        $vars = array(
            'form_id' => $shop_obj->create_start_form(),
        );

        $zerophp->response->addContent(zerophp_view('shop_topic_start|shop_topic', $vars));
    }

    function finalize() {
        $vars = array();
        $zerophp->response->addContent(zerophp_view('shop_topic_finalize|shop_topic', $vars));
    }

    function start_get_level2() {
        $shop_obj = \ZeroPHP\ZeroPHP\Entity::loadEntityObject('ZeroPHP\Shop\ShopTopic');
        $data = $this->input->get();

        $result = '';
        if (!empty($data['category_level1']) && is_numeric($data['category_level1'])) {
            $form_item = $shop_obj_topic->create_start_form('category_level2');
            $form_item['#item']['options'] = $shop_obj_topic->category_option_get_all(3, $data['category_level1']);

            if (count($form_item['#item']['options'])) {
                $result = form_render($form_item);
            }
        }

        $zerophp->content_set($result);
    }

    function start_get_level3() {
        $shop_obj = \ZeroPHP\ZeroPHP\Entity::loadEntityObject('ZeroPHP\Shop\ShopTopic');
        $data = $this->input->get();

        $result = '';
        if (!empty($data['category_level2']) && is_numeric($data['category_level2'])) {
            $form_item = $shop_obj_topic->create_start_form('category_level3');
            $form_item['#item']['options'] = $shop_obj_topic->category_option_get_all(4, $data['category_level2']);

            if (count($form_item['#item']['options'])) {
                $result = form_render($form_item);
            }
        }

        $zerophp->content_set($result);
    }
}