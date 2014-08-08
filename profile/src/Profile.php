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
            '#class' => '\ZeroPHP\Profile\Profile',
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
                        'placeholder' => zerophp_lang('123 Binh Duong avenue'),
                    ),
                    '#required' => true,
                ),
                'birthday' => array(
                    '#name' => 'birthday',
                    '#title' => zerophp_lang('Birthday'),
                    '#validate' => 'required',
                    '#required' => true,
                    '#type' => 'date',
                    '#config' => array(
                        'form_type' => 'select_group',
                    ),
                    '#attributes' => array(
                        'day' => array('class' => 'date_birth'),
                        'month' => array('class' => 'date_birth'),
                        'year' => array('class' => 'birth', 'placeholder' => 1985),
                    ),
                ),
                'province_id' => array(
                    '#name' => 'province_id',
                    '#title' => zerophp_lang('Location'),
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category',
                        'class' => '\ZeroPHP\Category\Category',
                    ),
                    '#options_callback' => array(
                        'class' => '\ZeroPHP\Category\Category',
                        'method' => 'loadOptions',
                        'arguments' => array(
                            'category_group_id' => 'location_province',
                            'parent' => '',
                            'select_text' => '--- Province ---',
                        ),
                    ),
                    '#ajax' => array(
                        'path' => 'location/district',
                        'wrapper' => 'fii_district_id',
                        'autoload' => 1,
                    ),
                    '#list_hidden' => true,
                ),
                'district_id' => array(
                    '#name' => 'district_id',
                    '#type' => 'select',
                    '#reference' => array(
                        'name' => 'category',
                        'class' => '\ZeroPHP\Category\Category',
                    ),
                    '#options' => array('' => zerophp_lang('--- District ---')),
                    '#list_hidden' => true,
                ),
                'mobile' => array(
                    '#name' => 'mobile',
                    '#title' => zerophp_lang('Mobile'),
                    '#type' => 'text',
                    '#required' => true,
                ),
                'created_by' => array(
                    '#name' => 'created_by',
                    '#title' => zerophp_lang('Created by'),
                    '#type' => 'text',
                    '#form_hidden' => true,
                    '#list_hidden' => true,
                ),
                'updated_by' => array(
                    '#name' => 'updated_by',
                    '#title' => zerophp_lang('Updated by'),
                    '#type' => 'text',
                    '#form_hidden' => true,
                    '#list_hidden' => true,
                ),
            ),
        );
    }

    public function update($zerophp, $userid) {
        $user = new \ZeroPHP\ZeroPHP\Users;
        if ($userid == 'me') {
            $userid = zerophp_userid();
        }
        else {
            $userid = $user->loadEntity($userid);
            $userid = isset($userid->id) ? $userid->id : 0;
        }

        if (!$userid) {
            \App::abort(403);
        }

        $profile = $this->loadEntity($userid);
        $form_values = is_object($profile) ? zerophp_object_to_array($profile) : array();
        $form_values['district_id_value'] = isset($form_values['district_id']) ? $form_values['district_id'] : 0;

        $profile = $user->loadEntity($userid);
        $form_values['email'] = '<font>' . $profile->email . '</font>';
        $form_values['title'] = $profile->title;

        $from = array(
            'class' => '\ZeroPHP\Profile\Profile',
            'method' => 'updateForm',
        );
        $zerophp->response->addContent(Form::build($from, $form_values));
    }

    public function updateForm() {
        $form = array();

        // From Users vendor
        $user = zerophp_user();
        $form['email'] = array(
            '#name' => 'email',
            '#type' => 'markup',
            '#title' => zerophp_lang('Email'),
        );

        $users = new \ZeroPHP\ZeroPHP\Users;
        $user_structure = $users->getStructure();
        $form['title'] = $user_structure['#fields']['title'];

        // From Profile vendors
        $form = array_merge($form, $this->crudCreateForm());
        unset($form['id']);

        $form['district_id_value'] = array(
            '#name' => 'district_id_value',
            '#type' => 'hidden',
        );

        $form['#actions']['reset'] = array(
            '#name' => 'reset',
            '#type' => 'reset',
            '#value' => zerophp_lang('Reset'),
        );

        array_unshift($form['#submit'], array(
            'class' => '\ZeroPHP\Profile\Profile',
            'method' => 'updateFormSubmit',
        ));

        $form['#success_message'] = zerophp_lang('Your profile was updated successfully.');

        return $form;
    }

    public function updateFormSubmit($form_id, &$form, &$form_values) {
        // Update to Users vendor
        $user = new \stdClass;
        $user->id = zerophp_userid();
        $user->title = $form_values['title'];
        $user->updated_at = date('Y-m-d H:i:s');
        $user_obj = new \ZeroPHP\ZeroPHP\Users;
        $user_obj->saveEntity($user);

        // Update to Profile Vendor
        $form_values['id'] = zerophp_userid();
    }

    public function read($zerophp) {}
}