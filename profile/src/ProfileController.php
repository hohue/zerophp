<?php
namespace ZeroPHP\Profile;

class ProfileController {


    
    function district_get_from_local() {
        $data = $this->input->get();

        $this->load->library('users_profile');
        $structure = $this->users_profile->getStructure();

        if (!empty($data['local_id'])) {
            $structure['#fields']['district_id']['reference_option']['arguments']['group'] = $data['local_id'];
        }

        if (!empty($data['id'])) {
            $users_profile = $this->users_profile->loadEntity(intval($data['id']));

            if (!empty($users_profile->district_id)) {
                $structure['#fields']['district_id']['value'] = reset(array_keys($users_profile->district_id));
            }
        }

        $form_item = $this->form->form_item_generate($structure['#fields']['district_id']);

        $zerophp->content_set(form_render($form_item, null, null, false));
    }
}