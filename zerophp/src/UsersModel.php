<?php
namespace ZeroPHP\ZeroPHP;

class UsersModel {

    function user_id_get($email) {
        $this->db->select('user_id');
        $this->db->where('email', $email);
        $query = $this->db->get('users');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->user_id;
            }
        }

        return 0;
    }

    function activation_get_user_id($activation_id) {
        $this->db->select('user_id');
        $this->db->where('activation_id', $activation_id);
        $this->db->where('field', 'activation');
        $query = $this->db->get('users_activation');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->user_id;
            }
        }

        return 0;
    }
}
