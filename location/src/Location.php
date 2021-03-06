<?php
namespace ZeroPHP\Location;

use ZeroPHP\ZeroPHP\Form;

class Location {
    function changeProvince($zerophp, $location_id) {
        if (is_numeric($location_id)) {
            $zerophp->request->addFilter('location_id', $location_id);
        }
        
        return zerophp_redirect($zerophp->request->url());
    }

    public function getDistrict($zerophp) {
        $parent = $zerophp->request->query('province_id');
        $parent = $parent ? $parent : 0;
        $value = $zerophp->request->query('district_id_value');
        $value = $value ? $value : 0;

        $form = array();
        $form['district_id'] = array(
            '#name' => 'district_id',
            '#type' => 'select',
            '#options_callback' => array(
                'class' => '\ZeroPHP\Category\Category',
                'method' => 'loadOptions',
                'arguments' => array(
                    'category_group_id' => 'location_district',
                    'parent' => $parent,
                    'select_text' => '--- District ---',
                ),
            ),
            '#value' => $value,
            '#attributes' => array(
                'required' => '',
            ),
            '#validate' => 'required|numeric',
        );

        $form['district_id'] = Form::buildItem($form['district_id']);

        $zerophp->response->addContent(zerophp_form_render('district_id', $form));
    }
}