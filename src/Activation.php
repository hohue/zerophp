<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class Activation extends Entity implements  EntityInterface {
    public function __config() {
        return array(
            '#id' => 'activation_id',
            '#name' => 'activation',
            '#class' => '\ZeroPHP\ZeroPHP\Activation',
            '#title' => zerophp_lang('Activation'),
            '#fields' => array(
                'activation_id' => array(
                    '#name' => 'activation_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'destination_id' => array(
                    '#name' => 'destination_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'hash' => array(
                    '#name' => 'hash',
                    '#title' => zerophp_lang('Activation hash'),
                    '#type' => 'text',
                ),
                'expired' => array(
                    '#name' => 'expired',
                    '#title' => zerophp_lang('Expired'),
                    '#type' => 'text',
                ),
                'type' => array(
                    '#name' => '#type',
                    '#title' => zerophp_lang('Activation type'),
                    '#type' => 'text',
                ),
            ),
        );
    }

    function setHash($destination_id, $type) {
        // Load activation
        $activation = $this->loadEntityByDestination($destination_id, array(
            'where' => array(
                'expired >=' => date('Y-m-d H:i:s'),
            ),
        ));

        if (!isset($activation->hash)) {
            $activation = new \stdClass();
            $activation->destination_id = $destination_id;
            $activation->expired = date('Y-m-d H:i:s', time() + zerophp_variable_get('activation expired', 172800)); // 2 days
            $activation->hash = md5($activation->destination_id . $activation->expired . mt_rand());
            $activation->type = $type;

            //@todo 8 Them ma kich hoat cho phep thanh vien co the nhap truc tiep tu trang web
            $this->saveEntity($activation);
        }

        return $activation;
    }

    function loadEntityByHash($hash, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['hash'] = $hash;
        $attributes['where']['expired >='] = date('Y-m-d H:i:s');

        $entity = $this->loadEntityExecutive(null, $attributes);
        return reset($entity);
    }

    function loadEntityByDestination($destination_id, $attributes = array()) {
        $attributes['load_all'] = false;
        $attributes['where']['destination_id'] = $destination_id;
        $attributes['where']['expired >='] = date('Y-m-d H:i:s');

        $entity = $this->loadEntityExecutive(null, $attributes);
        return reset($entity);
    }
}

//Checked