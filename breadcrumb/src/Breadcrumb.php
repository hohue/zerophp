<?php

namespace ZeroPHP\Breadcrumb;

use ZeroPHP\ZeroPHP\EntityInterface;
use ZeroPHP\ZeroPHP\Entity;

class Breadcrumb {
    function __config() {
        return array(
            '#id' => 'breadcrumb_id',
            '#name' => 'breadcrumb',
            '#class' => '\ZeroPHP\Breadcrumb\Breadcrumb',
            '#title' => zerophp_lang('Breadcrumb'),
            '#links' => array(
                'list' => 'admin/breadcrumb/list',
                'create' => 'admin/breadcrumb/create',
                'clone' => 'admin/breadcrumb/%/clone',
                'update' => 'admin/breadcrumb/%/update',
                'delete' => 'admin/breadcrumb/%/delete',
            ),
            '#fields' => array(
                'breadcrumb_id' => array(
                    '#name' => 'breadcrumb_id',
                    '#title' => zerophp_lang('ID'),
                    '#type' => 'hidden',
                ),
                'path' => array(
                    '#name' => 'title',
                    '#title' => zerophp_lang('Title'),
                    '#type' => 'text',
                ),
                'class' => array(
                    '#name' => 'class',
                    '#title' => zerophp_lang('Class'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'method' => array(
                    '#name' => 'method',
                    '#title' => zerophp_lang('Method'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'arguments' => array(
                    '#name' => 'arguments',
                    '#title' => zerophp_lang('Arguments'),
                    '#type' => 'text',
                    '#list_hidden' => 1,
                ),
                'active' => array(
                    '#name' => 'active',
                    '#title' => zerophp_lang('Active'),
                    '#type' => 'radios',
                    '#options' => array(
                        1 => zerophp_lang('Enable'),
                        0 => zerophp_lang('Disable'),
                    ),
                    '#validate' => 'required|numeric',
                    '#default' => 1,
                ),
            ),
        );
    }
}