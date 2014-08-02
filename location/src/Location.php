<?php
namespace ZeroPHP\Location;

class Location {
    function change($zerophp, $location_id) {
        if (is_numeric($location_id)) {
            $zerophp->request->addFilter('location_id', $location_id);
        }
        
        return zerophp_redirect($zerophp->request->url());
    }
}