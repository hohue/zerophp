<?php 
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class LanguageTranslate extends Entity {

    function __construct() {
        $this->setStructure(array(
            '#id' => 'language_translate_id',
            '#name' => 'language_translate',
            '#class' => 'ZeroPHP\ZeroPHP\LanguageTranslate',
            '#title' => zerophp_lang('Language Translate'),
            '#fields' => array(
                'language_translate_id' => array(
                    '#name' => 'language_translate_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'en' => array(
                    '#name' => 'en',
                    '#title' => zerophp_lang('English'),
                    '#type' => 'textarea',
                ),
                'vi' => array(
                    '#name' => 'vi',
                    '#title' => zerophp_lang('Vietnamese'),
                    '#type' => 'textarea',
                ),
            ),
        ));
    }

    function loadEntityAllByLanguage($language, $attributes = array()) {
        $cache_name = __METHOD__ . $language;
        if ($cache = \Cache::get($cache_name)) {
            return $cache;
        }

        $attributes = array(
            'order' => array(
                'en' => 'ASC',
            )
        );

        $languages = parent::loadEntityAll($attributes);

        $result = array();
        foreach ($languages as $value) {
            $result[$value->en] = !empty($value->$language) ? $value->$language : $value->en;
        }

        \Cache::forever($cache_name, $result);
        return $result;
    }
}