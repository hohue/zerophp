<?php
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class ShopTopic extends Entity {

    function __construct() {

        $this->setStructure(array(
            'id' => 'shop_topic_id',
            'name' => 'shop_topic',
            'title' => 'Shop topic',
            'fields' => array(
                'shop_topic_id' => array(
                    'name' => 'shop_topic_id',
                    'title' => 'ID',
                    'type' => 'hidden'
                ),
                'title' => array(
                    'name' => 'title',
                    'title' => 'Tiêu đề',
                    'type' => 'input',
                    'validate' => 'required'
                ),
                'short_description' => array(
                    'name' => 'short_description',
                    'title' => 'Mô tả ngắn',
                    'type' => 'input',
                    'validate' => 'required'
                ),
                'content' => array(
                    'name' => 'content',
                    'title' => 'MÔ TẢ CHI TIẾT',
                    'type' => 'textarea',
                    'rte_enable' => 1
                ),
                'price' => array(
                    'name' => 'price',
                    'title' => 'Giá sản phẩm',
                    'type' => 'input',
                    'validate' => 'required|numeric'
                ),
                'shipping' => array(
                    'name' => 'shipping',
                    'title' => 'Phí Vận Chuyển',
                    'type' => 'dropdown_build',
                    'options' => array(
                        0 => 'Phí Vận Chuyển',
                        1 => 'Mễn Phí',
                        2 => 'Liên Hệ'
                    )
                ),
                'is_promotion' => array(
                    'name' => 'is_promotion',
                    'title' => 'Sản phẩm này có khuyến mãi',
                    'type' => 'checkbox',
                    'value' => 1
                ),
                'promotion' => array(
                    'name' => 'promotion',
                    'title' => 'Khuyến Mãi',
                    'type' => 'input'
                ),
                'promotion_type' => array(
                    'name' => 'promotion_type',
                    'title' => 'Kiểu Khuyến Mãi',
                    'type' => 'dropdown_build',
                    'options' => array(
                        1 => 'Phần trăm',
                        2 => 'Giá trị'
                    ),
                    'form_hidden' => 1
                ),
                'promotion_start' => array(
                    'name' => 'promotion_start',
                    'title' => 'Thời Gian Khuyến Mãi',
                    'type' => 'input'
                ),
                'promotion_end' => array(
                    'name' => 'promotion_end',
                    'title' => 'đến',
                    'type' => 'input'
                ),
                'category_id' => array(
                    'name' => 'category_id',
                    'title' => 'Danh mục',
                    'type' => 'hidden',
                    'validate' => 'required',
                ),
                'created_by' => array(
                    'name' => 'created_by',
                    'title' => 'Tạo bởi',
                    'type' => 'input',
                    'validate' => 'required',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1
                ),
                'image' => array(
                    'name' => 'image',
                    'title' => 'Ảnh đại diện',
                    'type' => 'upload',
                    'widget' => 'image',
                    'display_hidden' => 1,
                    'description' => 'Không chèn quảng cáo, Số điện thoại, Địa chỉ, Tên web... lên ảnh đại diện'
                ),
                'created_date' => array(
                    'name' => 'created_date',
                    'title' => 'created_date',
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1
                ),
                'updated_date' => array(
                    'name' => 'updated_date	',
                    'title' => 'updated_date',
                    'type' => 'input',
                    'widget' => 'date_timestamp',
                    'form_hidden' => 1
                ),
                'active' => array(
                    'name' => 'active',
                    'title' => 'Kích hoạt',
                    'type' => 'radio_build',
                    'options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable')
                    ),
                    'form_hidden' => 1,
                    'default' => 0
                )
            )
        ));
    }

    function shop_topic_warning($block) {
        $data = array();

        return zerophp_view('shop_topic_block_topic_warning|shop_topic', $data);
    }

    function shop_topic_warning_access_for_topic($block) {
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
        $category = $this->CI->category->entity_load_all_from_group($level, $attributes);
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
            '#type' => 'dropdown_build',
            '#item' => array(
                'name' => 'category_level1',
                'type' => 'dropdown_build',
                'size' => 15,
                'options' => $this->category_option_get_all(1),
                'ajax' => array(
                    'path' => 'shop_topic/start_get_level2',
                    'wrapper' => 'fii_category_level2',
                    'method' => 'html',
                ),
            ),
        );

        $form['category_level2'] = array(
            '#name' => 'category_level2',
            '#type' => 'dropdown_build',
            '#item' => array(
                'name' => 'category_level2',
                'type' => 'dropdown_build',
                'size' => 15,
                'ajax' => array(
                    'path' => 'shop_topic/start_get_level3',
                    'wrapper' => 'fii_category_level3',
                    'method' => 'html',
                ),
            ),
            '#prefix' => '<span>&gt;</span>',
        );

        $form['category_level3'] = array(
            '#name' => 'category_level3',
            '#type' => 'dropdown_build',
            '#item' => array(
                'name' => 'category_level2',
                'type' => 'dropdown_build',
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
            '#type' => 'dropdown_build',
            '#item' => array(
                'name' => 'category_choose',
                'type' => 'dropdown_build',
                'size' => 15,
                'options' => $this->category_get_posted(zerophp_user_current()),
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
                'name' => 'submit',
                'value' => 'Bắt đầu đăng tin >',
            ),
        );

        $form['#validate'][] = array(
            'class' => 'shop_topic',
            'function' => 'create_start_form_validate',
        );

        $form['#submit'][] = array(
            'class' => 'shop_topic',
            'function' => 'create_start_form_submit',
        );

        $form['#redirect'] = site_url('e/create/shop_topic');

        $form_id = 'shop_topic_create_start_form';
        $this->CI->form->form_build($form_id, $form);
        return $form_id;
    }

    function category_get_posted($user_id) {
        $cach_name = "shop_topic-get_category_posted-$user_id";
        if ($cache = \Cache::get($cach_name)) {
            return $cache;
        }

        $posted = array();
        $this->CI->load->model('shop_topic_model', '', false, 'shop_topic');
        $categories = $this->CI->shop_topic_model->get_category_posted($user_id);
        if (count($categories)) {
            $entity = Entity::loadEntityObject('category');
            foreach ($categories as $value) {
                $category = $this->CI->category->entity_load($value->category_id);
                if (!empty($category->title)) {
                    $title = $category->title;
                    if (count($category->parent)) {
                        $parent = reset($category->parent)->category_id;
                        while ($parent) {
                            $category_parent = $this->CI->category->entity_load($parent);
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
            $this->CI->theme->messages_add('Vui lòng chọn danh mục bạn muốn đăng tin.', 'error');
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
            $this->CI->theme->messages_add('Bạn phải chọn một danh mục trước.', 'error');
            redirect('shop_topic/start');
        }
        //$this->CI->session->unset_userdata('shop_topic_create_category_id');
    }

    //entity_crud_create_shop_topic
}