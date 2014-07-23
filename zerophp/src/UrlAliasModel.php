<?php
namespace ZeroPHP\ZeroPHP;

class UrlAliasModel {

    function get_from_real($url_real) {
        return $this->_get_from(array(
            'url_real' => $url_real,
        ));
    }

    function get_from_alias($url_real, $url_alias) {
        return $this->_get_from(array(
            'url_real <>' => "$url_real",
            'url_alias' => $url_alias,
        ));
    }

    private function _get_from($where) {
        foreach ($where as $key => $val) {
            $this->db->where($key, $val);
        }

        $this->db->limit(1);
        $query = $this->db->get('url_alias');

        if ($row = reset($query->result())) {
            return $row;
        }

        return new stdClass();
    }
}