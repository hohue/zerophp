<?php
namespace ZeroPHP\Location;

class Location {
    function change($zerophp, $location_id) {
        if (is_numeric($location_id)) {
            $zerophp->request->addFilter('location_id', $location_id);
        }
        
        return zerophp_redirect($zerophp->request->url());
    }

    public function getDistrict($zerophp) {
        $parent = $zerophp->request->query('province_id');

        $result = '';
        if ($parent) {
            $form = array();
            $form['district_id'] = array(
                    '#name' => 'district_id',
                    '#type' => 'select',
                    /*'#reference' => array(
                        'name' => 'category',
                        'class' => '\ZeroPHP\Category\Category',
                        'options' => array(
                            'class' => '\ZeroPHP\Category\Category',
                            'method' => 'loadOptions',
                            'arguments' => array(
                                'category_group_id' => 'location_district',
                                'parent' => 0,
                                'select_text' => '--- District ---',
                            ),
                        ),
                    ),
                    '#display_hidden' => 1,*/
                );
            $form['district_id']['#options'] = $options;
            $form['district_id']['#reference']['options']['arguments']['parent'] = $parent;
            $result = zerophp_form_render('district_id', $form);
        }

        $zerophp->response->addContent($result);
    }
}