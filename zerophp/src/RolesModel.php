<?php
namespace ZeroPHP\ZeroPHP;

class RolesModel {
    function access_get_list() {
        $this->db->select('*');
        $query = $this->db->get('permissions');

        $result = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $result[$row->access_key][$row->role_id] = $row->access_value;
            }
        }

        return $result;
    }

    function access_set_list($data) {
        $this->db->truncate('permissions');

        if (count($data)) {
            $this->db->insert_batch('permissions', $data);
        }
    }
}
