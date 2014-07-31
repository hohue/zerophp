<?php
namespace ZeroPHP\ZeroPHP;

class UsersModel {

    function id_get($email) {
        $this->db->select('id');
        $this->db->where('email', $email);
        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->id;
            }
        }

        return 0;
    }

    function activation_get_id($activation_id) {
        $this->db->select('id');
        $this->db->where('activation_id', $activation_id);
        $this->db->where('field', 'activation');
        $query = $this->db->get('users_activation');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->id;
            }
        }

        return 0;
    }
}
