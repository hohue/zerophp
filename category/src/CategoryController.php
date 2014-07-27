<?php 
namespace ZeroPHP\Category;

class CategoryController {



    
    function parent_get_from_group() {
        $data = $this->input->get();

        //fw_devel_print($data);

        $this->load->library('category');
        $structure = $this->category->getStructure();

        if (!empty($data['category_group_id'])) {
            $structure['fields']['parent']['reference_option']['arguments']['group'] = $data['category_group_id'];
        }

        if (!empty($data['category_id'])) {
            $category = $this->category->loadEntity(intval($data['category_id']));

            if (!empty($category->parent)) {
                $structure['fields']['parent']['value'] = reset(array_keys($category->parent));
            }
        }

        $form_item = $this->form->form_item_generate($structure['fields']['parent']);

        if (!empty($category->category_id) && !empty($form_item['#item']['options'][$category->category_id])) {
            unset($form_item['#item']['options'][$category->category_id]);
        }

        $this->response->content_set(form_render($form_item, null, null, false));
    }

    function children_get_from_parent() {
        $data = $this->input->get();

        $this->load->library('users_profile');
        $structure = $this->users_profile->getStructure();

        if (!empty($data['local_id'])) {
            $structure['fields']['district_id']['reference_option']['arguments']['group'] = $data['local_id'];
        }

        if (!empty($data['user_id'])) {
            $users_profile = $this->users_profile->loadEntity(intval($data['user_id']));

            if (!empty($users_profile->district_id)) {
                $structure['fields']['district_id']['value'] = reset(array_keys($users_profile->district_id));
            }
        }

        $form_item = $this->form->form_item_generate($structure['fields']['district_id']);

        $this->response->content_set(form_render($form_item, null, null, false));
    }
}