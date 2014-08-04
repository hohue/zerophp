<?php 
namespace ZeroPHP\Profile;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Form;

class Profile extends Entity implements EntityInterface  {
    function __config() {
        return array(
            '#id' => 'id',
            '#name' => 'profile', //ten bang
            '#class' => 'ZeroPHP\Profile\Profile',
            '#title' => zerophp_lang('Users profile'),
            '#fields' => array(
                'id' => array(
                    '#name' => 'id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden'
                ),
                'address' => array(
                    '#name' => 'address',
                    '#title' => zerophp_lang('Address'),
                    '#type' => 'text',
                    '#attributes' => array(
                        'placeholder' => '123 Phường Chánh Nghĩa',
                    ),
                    '#required' => true,
                ),
                /*'birthday' => array(
                    '#name' => 'birthday',
                    '#title' => zerophp_lang('Birthday'),
                    '#validate' => 'required',
                    '#required' => true,
                    '#type' => 'date_group',
                ),*/
                /*'province_id' => array(
                    '#name' => 'province_id',
                    '#title' => 'Khu vực',
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category',
                        'type' => 'internal',
                        'options' => array(
                            'class' => 'category',
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
                    ),
                    '#ajax' => array(
                        'path' => 'users_profile/district_get_from_local',
                        'wrapper' => 'fii_district_id',
                        'method' => 'html',
                        'autoload' => 1,
                    ),
                ),
                'district_id' => array(
                    '#name' => 'district_id',
                    //'#title' => 'Quận huyện',
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category',
                        'type' => 'internal',
                        'options' => array(
                            'class' => 'users_profile',
                            'method' => 'district_get_from_local',
                            'arguments' => array(
                                'group' => 0,
                                'load_children' => false,
                            ),
                        ),
                    ),
                    '#display_hidden' => 1,
                ),*/
                'mobile' => array(
                    '#name' => 'mobile',
                    '#title' => zerophp_lang('Mobile'),
                    '#type' => 'text',
                    '#required' => true,
                ),
            ),
        );
    }

    public function update($zerophp) {
        if (!zerophp_userid()) {
            \App::abort(403);
        }

        $from = array(
            'class' => '\ZeroPHP\Profile\Profile',
            'method' => 'updateForm',
        );
        $zerophp->response->addContent(Form::build($from));
    }

    public function updateForm() {
        $form = array();

        $user = zerophp_user();
        $form['email'] = array(
            '#name' => 'email',
            '#type' => 'markup',
            '#title' => zerophp_lang('Email'),
            '#value' => '<font>' . $user->email . '</font>',
        );

        $users = new \ZeroPHP\ZeroPHP\Users;
        $user_structure = $users->getStructure();
        $form['title'] = $user_structure['#fields']['title'];

        $profile_structure  = $this->getStructure();
        $form['address'] = $profile_structure['#fields']['address'];
        $form['mobile'] = $profile_structure['#fields']['mobile'];


        $form['#actions']['submit'] = array(
            '#name' => 'submit',
            '#type' => 'submit',
            '#value' => zerophp_lang('Submit'),
        );

        $form['#actions']['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#value' => zerophp_lang('Reset'),
        );

        $form['#submit'] = array(
            array(
                'class' => '\ZeroPHP\Profile\Profile',
                'method' => 'updateFormSubmit',
            ),
        );

        $form['#success_message'] = zerophp_lang('Your profile was updated successfully.');

        return $form;
    }

    public function updateFormSubmit($form_id, &$form, &$form_values) {
        $profile = new \stdClass;
        $profile->id = zerophp_userid();
        $profile->address = $form_values['address'];
        $profile->mobile = $form_values['mobile'];
        $this->saveEntity($profile);

        $user = new \stdClass;
        $user->id = zerophp_userid();
        $user->title = $form_values['title'];
        $user->updated_at = date('Y-m-d H:i:s');
        $user_obj = new \ZeroPHP\ZeroPHP\Users;
        $user_obj->saveEntity($user);
    }

    public function read($zerophp) {}







    

    function users_profile_update_form_alter($form_id, &$form) {
        $form['fullname'] = array(
            '#type' => 'text',
            '#name' => 'fullname',
            '#label' => zerophp_lang('Fullname'),
            '#required' => true,
            '#item' => array(
                '#name' => 'fullname',
                '#validate' => 'required',
                '#required' => 1,
                '#type' => 'text',
                'placeholder' => 'Nguyễn Văn Anh',
            ),
            '#error_messages' => zerophp_lang('Required field'),
        );

        $form['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#item' => array(
                '#name' => 'reset',
                '#type' => "reset",
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

        zerophp_get_instance()->response->breadcrumbs_add($crud);
        return parent::crud_update($entity, $url_prefix);
    }

    function users_profile_update_form_value_alter($form_id, $form, &$form_values) {
        $form_values['fullname'] = $this->CI->users->user_get()->title;
        $form_values['id'] = zerophp_userid();
    }

    function entity_exists($entity_id, $active = true, $cache = true) {
        if (!$entity_id || !is_numeric($entity_id)) {
            return false;
        }

        $entity = $this->loadEntity($entity_id, array('cache' => $cache));

        if (!empty($entity->id)) {
            return $entity;
        }
        else {
            // Create a new profile for user if not exists
            $user = $this->CI->users->loadEntity($entity_id);
            if (!empty($user->id)) {
                $entity = new \stdClass();
                $entity->id = $user->id;
                $entity_id = $this->saveEntity($entity);

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

        $empty = new \stdClass();
        $empty->title = '---';
        $empty->category_id = 0;
        $result[0] = $empty;

        if ($group) {
            $entity = new \ZeroPHP\Category\Category;
            $attributes = array(
                'order' => array(
                    'weight' => 'ASC',
                    '#title' => 'ASC',
                )
            );
            $categories = $this->CI->category->loadEntityAll_from_parent($group, $attributes);

            //fw_devel_print($categories);
            if (count($categories) > 1) {
                $categories = template_tree_build_option($categories, 0, 0, false);
            }
            $result = array_merge($result, $categories);
        }

        \Cache::put($cache_name, $result, ZEROPHP_CACHE_EXPIRE_TIME);
        return $result;
    }

    /*function district_get_from_local() {
        $data = $this->input->get();

        $this->load->library('users_profile');
        $structure = $this->users_profile->getStructure();

        if (!empty($data['province_id'])) {
            $structure['#fields']['district_id']['reference_option']['arguments']['group'] = $data['province_id'];
        }

        if (!empty($data['id'])) {
            $users_profile = $this->users_profile->loadEntity(intval($data['id']));

            if (!empty($users_profile->district_id)) {
                $structure['#fields']['district_id']['value'] = reset(array_keys($users_profile->district_id));
            }
        }

        $form_item = $this->form->form_item_generate($structure['#fields']['district_id']);

        $zerophp->content_set(form_render($form_item, null, null, false));
    }*/
}