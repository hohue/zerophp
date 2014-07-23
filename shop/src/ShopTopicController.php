<?php 
use ZeroPHP\ZeroPHP\Theme;

class ShoptopicController extends Controller {
    function start() {
        $this->load->library('shop_topic');

        $vars = array(
            'form_id' => $this->shop_topic->create_start_form(),
        );

        $zerophp->response->addContent('shop_topic_start|shop_topic', 'Chọn Chuyên Mục', $vars);
    }

    function  finalize() {
        $vars = array();
        $zerophp->response->addContent('shop_topic_finalize|shop_topic', 'Đăng Tin Thành Công', $vars);
    }

    function start_get_level2() {
        $this->load->library('shop_topic');
        $data = $this->input->get();

        $result = '';
        if (!empty($data['category_level1']) && is_numeric($data['category_level1'])) {
            $form_item = $this->shop_topic->create_start_form('category_level2');
            $form_item['#item']['options'] = $this->shop_topic->category_option_get_all(3, $data['category_level1']);

            if (count($form_item['#item']['options'])) {
                $result = form_render($form_item);
            }
        }

        $this->response->content_set($result);
    }

    function start_get_level3() {
        $this->load->library('shop_topic');
        $data = $this->input->get();

        $result = '';
        if (!empty($data['category_level2']) && is_numeric($data['category_level2'])) {
            $form_item = $this->shop_topic->create_start_form('category_level3');
            $form_item['#item']['options'] = $this->shop_topic->category_option_get_all(4, $data['category_level2']);

            if (count($form_item['#item']['options'])) {
                $result = form_render($form_item);
            }
        }

        $this->response->content_set($result);
    }
}