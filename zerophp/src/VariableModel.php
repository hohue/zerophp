<?php 
namespace ZeroPHP\ZeroPHP;

class VariableModel {
    public static function get($key, $default = null) {
        $db = \DB::table('variable');
        $db->select('variable_value');
        $db->where('variable_key', $key);
        $value = $db->first();

        if(isset($value->variable_value)) {
            return $value->variable_value;
        }

        return $default;
    }

    public static function set($key, $value) {
        $exist = self::get($key);

        if ($exist) {
            \DB::table('variable')
                ->where('variable_key', '=', $key)
                ->update(array('variable_value' => $value));
        }
        else {
            \DB::table('variable')
                ->insert(array(
                    'variable_key' => $key,
                    'variable_value' => $value,
                ));
        }
    }
}