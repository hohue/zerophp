<?php
namespace ZeroPHP\Location;

class LocationController {


    
    function change($location_id) {
        if (is_numeric($location_id)) {
            $this->CI->session->set_userdata('location_current', $location_id);
        }
        
        redirect(\URL::to($zerophp->request->query('destination')));
    }
}