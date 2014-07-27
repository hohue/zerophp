<?php 
namespace ZeroPHP\Profile;

use ZeroPHP\ZeroPHP\Entity;

class Profile extends Entity {
    function __construct() {
        $this->setStructure(array(
            'id' => 'user_id',
            'name' => 'profile', //ten bang
            'class' => 'ZeroPHP\Profile\Profile',
            'title' => zerophp_lang('Users profile'),
            'fields' => array(
                'user_id' => array(
                    'name' => 'user_id',
                    'title' => zerophp_lang('ID'),
                    'type' => 'hidden'
                ),
                'address' => array(
                    'name' => 'address',
                    'title' => zerophp_lang('address'),
                    'type' => 'input',
                    'placeholder' => '123 Phường Chánh Nghĩa',
                ),
                'birthday' => array(
                    'name' => 'birthday',
                    'title' => zerophp_lang('Birthday'),
                    'validate' => 'required',
                    'required' => true,
                    'type' => 'date_group',
                ),
                'local_id' => array(
                    'name' => 'local_id',
                    'title' => 'Khu vực',
                    'type' => 'select_build',
                    'reference' => 'category',
                    'reference_type' => 'internal',
                    'reference_option' => array(
                        'library' => 'category',
                        'method' => 'parent_get_from_group',
                        'arguments' => array(
                            'group' => 5,
                            'load_children' => false,
                            'attributes' => array(
                                'order' => array(
                                    'weight' => 'ASC',
                                    'title' => 'ASC',
                                ),
                            ),
                        ),
                    ),
                    'ajax' => array(
                        'path' => 'users_profile/district_get_from_local',
                        'wrapper' => 'fii_district_id',
                        'method' => 'html',
                        'autoload' => 1,
                    ),
                ),
                'district_id' => array(
                    'name' => 'district_id',
                    //'title' => 'Quận huyện',
                    'type' => 'select_build',
                    'reference' => 'category',
                    'reference_type' => 'internal',
                    'reference_option' => array(
                        'library' => 'users_profile',
                        'method' => 'district_get_from_local',
                        'arguments' => array(
                            'group' => 0,
                            'load_children' => false,
                        ),
                    ),
                    'display_hidden' => 1,
                ),
                'mobile' => array(
                    'name' => 'mobile',
                    'title' => zerophp_lang('mobile'),
                    'type' => 'input',
                ),
            ),
        ));
    }

    function users_profile_update_form_alter($form_id, &$form) {
        $form['fullname'] = array(
            '#type' => 'text',
            '#name' => 'fullname',
            '#label' => zerophp_lang('Fullname'),
            '#required' => true,
            '#item' => array(
                'name' => 'fullname',
                'validate' => 'required',
                'required' => 1,
                'type' => 'input',
                'placeholder' => 'Nguyễn Văn Anh',
            ),
            '#error_messages' => zerophp_lang('Required field'),
        );

        $form['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#item' => array(
                'name' => 'reset',
                'type' => "reset",
                'value' => zerophp_lang('Reset'),
            ),
        );

        $form['submit']['#item']['value'] = 'Lưu Xác Nhận';
        unset($form['#redirect']);
    }

    function crud_update($entity, $url_prefix = '') {
        $crud = array(
            array(
                'item' => zerophp_lang('Users profile'),
            ),
            array(
                'item' => zerophp_lang('Save users profile'),
            ),
        );

        $this->CI->theme->breadcrumbs_add($crud);
        return parent::crud_update($entity, $url_prefix);
    }

    function users_profile_update_form_value_alter($form_id, $form, &$form_values) {
        $form_values['fullname'] = $this->CI->users->user_get()->title;
        $form_values['user_id'] = zerophp_user_current();
    }

    function entity_exists($entity_id, $active = true, $cache = true) {
        if (!$entity_id || !is_numeric($entity_id)) {
            return false;
        }

        $entity = $this->loadEntity($entity_id, array('cache' => $cache));

        if (!empty($entity->user_id)) {
            return $entity;
        }
        else {
            // Create a new profile for user if not exists
            $user = $this->CI->users->loadEntity($entity_id);
            if (!empty($user->user_id)) {
                $entity = new stdClass();
                $entity->user_id = $user->user_id;
                $entity_id = $this->entity_save($entity);

                return $this->loadEntity($entity_id);
            }
        }

        return false;
    }

    function district_get_from_local($arguments = null) {
        $cache_name = 'Users_profile-children_get_from_parent' . serialize($arguments);
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $result = array();

        $group = !empty($arguments['group']) && is_numeric($arguments['group']) ? $arguments['group'] : 0;

        $empty = new stdClass();
        $empty->title = '---';
        $empty->category_id = 0;
        $result[0] = $empty;

        if ($group) {
            $entity = Entity::loadEntityObject('category');
            $attributes = array(
                'order' => array(
                    'weight' => 'ASC',
                    'title' => 'ASC',
                )
            );
            $categories = $this->CI->category->loadEntityAll_from_parent($group, $attributes);

            //fw_devel_print($categories);
            if (count($categories) > 1) {
                $categories = template_tree_build_option($categories, 0, 0, false);
            }
            $result = array_merge($result, $categories);
        }

        \Cache::put($cache_name, $result);
        return $result;
    }
}