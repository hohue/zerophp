<?php 
namespace ZeroPHP\Shop;

use ZeroPHP\ZeroPHP\Entity;

class Shop extends Entity {
    function __construct() {
        $this->setStructure(array(
            '#id' => 'shop_id',
            '#name' => 'shop',
            '#class' => 'ZeroPHP\Shop\Shop',
            '#title' => zerophp_lang('Shop'),
            '#fields' => array(
                'shop_id' => array(
                    '#name' => 'shop_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'title' => array(
                    '#name' => 'title',
                    '#title' => 'Tên Shop',
                    '#type' => 'text',
                    '#required' => true,
                    '#validate' => 'required|min_length[3]|max_length[100]',
                    '#attributes' => array(
                        'placeholder' => 'Hoa mai shop'
                    ),
                ),
                'url_alias' => array(
                    '#name' => 'url_alias',
                    '#title' => 'URL shop',
                    '#type' => 'text',
                    '#required' => true,
                    '#validate' => 'required|min_length[3]|max_length[100]',
                    '#attributes' => array(
                        'placeholder' => 'hoa-mai-shop',
                        'data-prefix' => 'http://chovip.vn/',
                    ),
                    '#class' => 'form-prefix',
                ),
                'local_id' => array(
                    '#name' => 'local_id',
                    '#title' => 'Khu vực',
                    '#type' => 'select_build',
                    '#reference' => array(
                        'name' => 'category',
                        'type' => 'internal',
                        '#options' => array(
                            'class' => 'category',
                            'method' => 'parent_get_from_group',
                            'arguments' => array(
                                'group' => 5,
                                'load_children' => false,
                            ),
                        ),
                    ),
                    '#ajax' => array(
                        'path' => 'shop/district_get_from_local',
                        'wrapper' => 'fii_district_id',
                        'method' => 'html',
                    ),
                ),
                'district_id' => array(
                    '#name' => 'district_id',
                    '#title' => 'Quận huyện',
                    '#type' => 'select_build',
                    '#reference' => array(
                        'name' => 'category',
                        'type' => 'internal',
                        '#options' => array(
                            'class' => 'users_profile',
                            'method' => 'district_get_from_local',
                            'arguments' => array(
                                'group' => 0,
                                'load_children' => false,
                            ),
                        ),
                    ),
                    '#display_hidden' => 1,
                ),
                'address' => array(
                    '#name' => 'address',
                    '#title' => zerophp_lang('Address'),
                    '#type' => 'text',
                    '#required' =>true,
                    '#validate' => '#required',
                    '#attributes' => array(
                        'placeholder' => "123 Chánh Nghĩa",
                    ),
                    '#display_hidden' => 1,
                ),
                'website' => array(
                    '#name' => 'website',
                    '#title' => zerophp_lang('Website'),
                    '#type' => 'text',
                    '#display_hidden' => 1,
                ),
                'homephone' => array(
                    '#name' => 'homephone',
                    '#title' => zerophp_lang('Homephone'),
                    '#type' => 'text',
                    '#display_hidden' => 1,
                ),
                'mobile' => array(
                    '#name' => 'mobile',
                    '#title' => zerophp_lang('Mobile'),
                    '#type' => 'text',
                    '#required' =>true,
                    '#validate' => 'required|integer',
                    '#display_hidden' => 1,
                    '#attributes' => array(
                        'placeholder' => '0912345678',
                    ),
                ),
                'image' => array(
                    '#name' => 'image',
                    '#title' => zerophp_lang('Avatar Shop'),
                    '#type' => 'upload',
                    '#widget' => 'image',
                    '#display_hidden' => 1,
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => zerophp_lang('Created by'),
                    '#type' => 'text',
                    '#form_hidden' => 1,
                    '#display_hidden' => 1,
                ),
                'created_at' => array(
                    '#name' => 'created_at',
                    '#title' => zerophp_lang('Created date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'updated_at' => array(
                    '#name' => 'updated_at',
                    '#title' => zerophp_lang('Updated date'),
                    '#type' => 'text',
                    '#widget' => 'date_timestamp',
                    '#form_hidden' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#default' => 0,
                    '#form_hidden' => 1,
                ),
                'paymenth_method' => array(
                    '#name' => 'paymenth_method',
                    '#title' => 'Phương thức thanh toán',
                    '#type' => 'textarea',
                    '#rte_enable' => 1,
                    '#display_hidden' => 1,
                ),
                'shipmenth_method' => array(
                    '#name' => 'shipmenth_method',
                    '#title' => 'Phương thức giao hàng',
                    '#type' => 'textarea',
                    '#rte_enable' => 1,
                    '#display_hidden' => 1,
                ),
            ),
        ));
    }


    

    function loadEntity_by_user($user_id, $attributes = array()) {
        $attributes['where']['created_by'] = $user_id;

        if (!isset($attributes['check_active'])) {
            $attributes['check_active'] = false;
        }

        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function loadEntity_by_url_alias($path, $attributes = array()) {
        $attributes['where']['url_alias'] = $path;

        if (!isset($attributes['check_active'])) {
            $attributes['check_active'] = false;
        }

        return reset($this->loadEntityExecutive(null, $attributes));
    }

    function shop_create_form_alter($form_id, &$form) {
        if ($form_id == 'entity_crud_create_shop') {
            // Check shop registered
            $shop = $this->loadEntity_by_user(zerophp_userid());
            if (!empty($shop->shop_id)) {
                zerophp_get_instance()->response->addMessage('Bạn chỉ có thể mở một shop.', 'error');
                return \Redirect::to();
            }

            unset($form['paymenth_method']);
            unset($form['shipmenth_method']);

            $form['#redirect'] = 'up/shop/confirmation';
        }

        $form['submit']['#item']['value'] = 'Xác Nhận Thông Tin';

        $form['#validate'][] = array(
            '#class' => 'shop',
            'method' => 'shop_create_form_validate',
        );

        $form['#submit'][] = array(
            '#class' => 'shop',
            'method' => 'shop_create_form_submit',
        );
    }

    function shop_create_form_validate($form_id, &$form, &$form_value) {
        if (substr($form_value['mobile'], 0, 1) != '+') {
            if (substr($form_value['mobile'], 0, 1) == '0') {
                $form_value['mobile'] = substr($form_value['mobile'], 1);
            }

            $form_value['mobile'] = "+84" . $form_value['mobile'];
        }

        $form_value['url_alias'] = uri_validate($form_value['url_alias']);

        if ($form_id == 'entity_crud_create_shop') {
            //@todo 9 Hack for value changed
            $_POST = $form_value;

            $entity = Entity::loadEntityObject('form_validation');

            $this->CI->form_validation->set_rules('mobile', $form['mobile']['#label'], 'is_unique[shop.mobile]');
            $this->CI->form_validation->set_rules('url_alias', $form['url_alias']['#label'], 'is_unique[shop.url_alias]');

            if ($this->CI->form_validation->run() == FALSE) {
                zerophp_get_instance()->response->addMessage($validator->messages(), 'error');
                return false;
            }
        }

        return true;
    }

    function shop_create_form_submit($form_id, &$form, &$form_value) {
        $role_id = reset(fw_variable_get('shop roles salesman', array()));

        if ($role_id) {
            $user = $this->CI->users->loadEntity(zerophp_userid());
            $user->roles[] = $role_id;
            $this->CI->users->saveEntity($user);
        }

        $entity = Entity::loadEntityObject('url_alias');
        $this->url_alias->url_alias_create('e/read/shop/' . $form_value['shop_id'], $form_value['url_alias']);
    }

    function shop_update_form_alter($form_id, &$form) {
        $shop_obj_create_form_alter($form_id, $form);

        // Check shop updated
        $shop = $this->loadEntity_by_user(zerophp_userid());
        if (empty($shop->shop_id) || $shop->shop_id != $form['shop_id']['#item']['shop_id']) {
            zerophp_get_instance()->response->addMessage('Bạn không có quyền truy cập vào trang này', 'error');
            return \Redirect::to();
        }

        $form['url_alias']['#disabled'] = 'disabled';
        $form['url_alias']['#item']['disabled'] = 'disabled';
        unset($form['#redirect']);
    }

    function crud_views($attributes = array()) {
        if (isset($attributes['entity_id']) && $attributes['entity_id'] == 'me') {
            $attributes['entity_id'] = 0;

            $shop = $this->loadEntity_by_user(zerophp_userid());
            if (!empty($shop->shop_id)) {
                $attributes['entity_id'] = $shop->shop_id;
            }
        }

        parent::crud_views($attributes);
    }


    function shop_information($block) {
        if (!isset($block->shop_id) || !is_numeric($block->shop_id)) {
            return '';
        }

        $shop_id = $block->shop_id;

        $entity = Entity::loadEntityObject('shop');
        $shop = $this->CI->shop->loadEntity($shop_id);
        $saleman = $this->CI->users->loadEntity($shop->created_by);

       //fw_devel_print($shop);

        if (substr($shop->mobile, 0, 3) == "+84") {
            $shop->mobile = '0' . substr($shop->mobile, 3);
        }

        if (count($shop->district_id)) {
            $shop->address .= ' ' . reset($shop->district_id)->title;
        }

        if (count($shop->local_id)) {
            $shop->address .= ' ' . reset($shop->local_id)->title;
        }

        $data = array(
            'shop_url' => "e/read/shop/$shop_id",
            '#name'=> $shop->title,
            'address' => $shop->address,
            'mobile' => $shop->mobile,
            'email' => $saleman->email,
            'website' => $shop->website,
        );

        return zerophp_view('shop_block_shop_information|shop', $data);
    }

    function shop_information_access_for_topic(&$block) {
        $uri = explode('/', \URL::current());

        if (isset($uri[0]) && $uri[0] == 'e'
            && isset($uri[1]) && $uri[1] == 'read'
            && isset($uri[2]) && $uri[2] == 'shop_topic'
            && isset($uri[3]) && is_numeric($uri[3])
        ) {
            $entity = Entity::loadEntityObject('shop_topic');
            $topic = $this->CI->shop_topic->loadEntity($uri[3]);
            $shop = $this->loadEntity_by_user($topic->created_by);

            $block->shop_id = $shop->shop_id;
            return true;
        }

        return false;
    }

    function shop_information_access_for_shop(&$block) {
        $uri = explode('/', \URL::current());

        if (isset($uri[0]) && $uri[0] == 'e'
            && isset($uri[1]) && $uri[1] == 'read'
            && isset($uri[2]) && $uri[2] == 'shop'
            && isset($uri[3]) && is_numeric($uri[3])
        ) {
            $block->shop_id = $uri[3];
            return true;
        }

        return false;
    }
}