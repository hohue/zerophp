<?php
class LocationController extends Controller {
    function change($location_id) {
        if (is_numeric($location_id)) {
            $this->CI->session->set_userdata('location_current', $location_id);
        }
        
        redirect(site_url($_GET['destination']));
    }
}