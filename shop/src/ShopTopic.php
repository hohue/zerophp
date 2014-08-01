<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class ShopTopic extends Entity {
    function __construct() {
        $this->setStructure(array(
            '#id' => 'shop_topic_id',
            '#name' => 'shop_topic',
            '#class' => 'ZeroPHP\Shop\ShopTopic',
            '#title' => 'Shop topic',
            '#fields' => array(
                'shop_topic_id' => array(
                    '#name' => 'shop_topic_id',
                    '#title' => 'ID',
                    '#type' => 'hidden'
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => 'Tiêu đề',
                    '#type' => 'text',
                    '#validate' => 'required'
                ),
                'short_description' => array(
                    '#name' => 'short_description',
                    '#title' => 'Mô tả ngắn',
                    '#type' => 'text',
                    '#validate' => 'required'
                ),
                'content' => array(
                    '#name' => 'content',
                    '#title' => 'MÔ TẢ CHI TIẾT',
                    '#type' => 'textarea',
                    '#rte_enable' => 1
                ),
                'price' => array(
                    '#name' => 'price',
                    '#title' => 'Giá sản phẩm',
                    '#type' => 'text',
                    '#validate' => 'required|numeric'
                ),
                'shipping' => array(
                    '#name' => 'shipping',
                    '#title' => 'Phí Vận Chuyển',
                    '#type' => 'select_build',
                    '#options' => array(
                        0 => 'Phí Vận Chuyển',
                        1 => 'Mễn Phí',
                        2 => 'Liên Hệ'
                    )
                ),
                'is_promotion' => array(
                    '#name' => 'is_promotion',
                    '#title' => 'Sản phẩm này có khuyến mãi',
                    '#type' => 'checkbox',
                ),
                'promotion' => array(
                    '#name' => 'promotion',
                    '#title' => 'Khuyến Mãi',
                    '#type' => 'input'
                ),
                'promotion_type' => array(
                    '#name' => 'promotion_type',
                    '#title' => 'Kiểu Khuyến Mãi',
                    '#type' => 'select_build',
                    '#options' => array(
                        1 => 'Phần trăm',
                        2 => 'Giá trị'
                    ),
                    '#form_hidden' => 1
                ),
                'promotion_start' => array(
                    '#name' => 'promotion_start',
                    '#title' => 'Thời Gian Khuyến Mãi',
                    '#type' => 'input'
                ),
                'promotion_end' => array(
                    '#name' => 'promotion_end',
                    '#title' => 'đến',
                    '#type' => 'input'
                ),
                'category_id' => array(
                    '#name' => 'category_id',
                    '#title' => 'Danh mục',
                    '#type' => 'hidden',
                    '#validate' => 'required',
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => 'Tạo bởi',
                    '#type' => 'text',
                    '#validate' => 'required',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1
                ),
                'image' => array(
                    '#name' => 'image',
                    '#title' => 'Ảnh đại diện',
                    '#type' => 'upload',
                    '#widget' => 'image',
                    '#display_hidden' => 1,
                    '#description' => 'Không chèn quảng cáo, Số điện thoại, Địa chỉ, Tên web... lên ảnh đại diện'
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => 'created_at',
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1
                ),
                'updated_at' => array(
                    '#name' => 'updated_at	',
                    '#title' => 'updated_at',
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => 'Kích hoạt',
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable')
                    ),
                    '#form_hidden' => 1,
                    '#default' => 0
                )
            )
        ));
    }

    function shop_topic_warning($block) {
        $data = array();

        return zerophp_view('shop_topic_block_topic_warning|shop_topic', $data);
    }

    function shop_topic_warning_access($block) {
        $uri = explode('/', \URL::current());

        if (isset($uri[0]) && $uri[0] == 'e' && isset($uri[1]) && $uri[1] == 'read' && isset($uri[2]) && $uri[2] == 'shop_topic' && isset($uri[3]) && is_numeric($uri[3])) {
            return true;
        }

        return false;
    }

    public function category_option_get_all($level, $parent = 0) {
        $cache_name = __METHOD__ . $level . $parent;
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $entity = Entity::loadEntityObject('category');

        $attributes = array(
            'where' => array(
                'parent' => $parent,
            ),
        );

        $option = array();
        $category = $this->CI->category->loadEntityAll_from_group($level, $attributes);
        //fw_devel_print($category);
        if (count($category)) {
            foreach ($category as $key => $val) {
                $option[$key] = array(
                    '#title' => $val->title,
                    '#attributes' => array(
                        'data-children' => !empty($val->children_count) ? $val->children_count : 0,
                    ),
                );
            }
        }

        \Cache::forever($cache_name, $option);
        return $option;
    }

    public function create_start_form($return_item = null) {
        //@todo 6 Fixed for Danh muc san pham cap 1
        $form['category_level1'] = array(
            '#name' => 'category_level1',
            '#type' => 'select_build',
            '#item' => array(
                '#name' => 'category_level1',
                '#type' => 'select_build',
                'size' => 15,
                '#options' => $this->category_option_get_all(1),
                'ajax' => array(
                    'path' => 'shop/topic/create/start_get_level2',
                    'wrapper' => 'fii_category_level2',
                    'method' => 'html',
                ),
            ),
        );

        $form['category_level2'] = array(
            '#name' => 'category_level2',
            '#type' => 'select_build',
            '#item' => array(
                '#name' => 'category_level2',
                '#type' => 'select_build',
                'size' => 15,
                'ajax' => array(
                    'path' => 'shop/topic/create/start_get_level3',
                    'wrapper' => 'fii_category_level3',
                    'method' => 'html',
                ),
            ),
            '#prefix' => '<span>&gt;</span>',
        );

        $form['category_level3'] = array(
            '#name' => 'category_level3',
            '#type' => 'select_build',
            '#item' => array(
                '#name' => 'category_level2',
                '#type' => 'select_build',
                'size' => 15,
            ),
            '#prefix' => '<span>&gt;</span>',
        );

        if ($return_item) {
            switch ($return_item) {
                case 'category_level2':
                case 'category_level3':
                    $form[$return_item]['#item']['id'] = 'fii_' .$return_item. '_field';
                    return $form[$return_item];

                default:
                    return '';
            }
        }

        $form['category_choose'] = array(
            '#name' => 'category_choose',
            '#type' => 'select_build',
            '#item' => array(
                '#name' => 'category_choose',
                '#type' => 'select_build',
                'size' => 15,
                '#options' => $this->category_get_posted(zerophp_userid()),
            ),
        );

        $form['category_id'] = array(
            '#name' => 'category_id',
            '#type' => 'hidden',
            '#item' => array(
                'category_id' => 'a',
            ),
            '#required' => true,
        );

        $form['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#item' => array(
                '#name' => 'submit',
                '#value' => 'Bắt đầu đăng tin >',
            ),
        );

        $form['#validate'][] = array(
            'class' => 'shop_topic',
            'method' => 'create_start_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'shop_topic',
            'method' => 'create_start_form_submit',
        );

        $form['#redirect'] = \URL::to('e/create/shop_topic');

        $form_id = 'shop_topic_create_start_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function category_get_posted($id) {
        $cach_name = "shop_topic-get_category_posted-$id";
        if ($cache = \Cache::get($cach_name)) {
            return $cache;
        }

        $posted = array();
        $this->CI->load->model('shop_topic_model', '', false, 'shop_topic');
        $categories = $this->CI->shop_topic_model->get_category_posted($id);
        if (count($categories)) {
            $entity = Entity::loadEntityObject('category');
            foreach ($categories as $value) {
                $category = $this->CI->category->loadEntity($value->category_id);
                if (!empty($category->title)) {
                    $title = $category->title;
                    if (count($category->parent)) {
                        $parent = reset($category->parent)->category_id;
                        while ($parent) {
                            $category_parent = $this->CI->category->loadEntity($parent);
                            if (!empty($category_parent->title)) {
                                $title = $category_parent->title . ' » ' . $title;
                                $parent = count($category_parent->parent) ? reset($category_parent->parent)->category_id : 0;
                            }
                            else {
                                $parent = 0;
                            }
                        }
                    }

                    $posted[$value->category_id] = $title;
                }
            }
        }

        \Cache::forever($cach_name, $posted);
        return $posted;
    }

    function create_start_form_validate($form_id, $form, &$form_value) {
        if (!is_numeric($form_value['category_id']) || !$form_value['category_id']) {
            zerophp_get_instance()->response->addMessage('Vui lòng chọn danh mục bạn muốn đăng tin.', 'error');
            return false;
        }

        return true;
    }

    function create_start_form_submit($form_id, $form, &$form_value) {
        $this->CI->session->set_userdata('shop_topic_create_category_id', $form_value['category_id']);
    }

    function shop_topic_create_form_alter($form_id, &$form) {
        //fw_devel_print($form);
    }

    function shop_topic_create_form_value_alter($form_id, $form, &$form_value) {

        $form_value['category_id'] = $this->CI->session->userdata('shop_topic_create_category_id');
        if (!$form_value['category_id']) {
            zerophp_get_instance()->response->addMessage('Bạn phải chọn một danh mục trước.', 'error');
            return \Redirect::to('shop/topic/create/start');
        }
        //$this->CI->session->unset_userdata('shop_topic_create_category_id');
    }

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