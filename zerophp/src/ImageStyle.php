<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;

class ImageStyle extends Entity implements  EntityInterface {
    function __config() {
        return array(
            '#id' => 'style',
            '#name' => 'image_style',
            '#class' => 'ZeroPHP\ZeroPHP\ImageStyle',
            '#title' => zerophp_lang('Image style'),
            '#fields' => array(
                'style' => array(
                    '#name' => 'style',
                    '#title' => zerophp_lang('Style name'),
                    '#type' => 'text',
                    '#required' => true,
                    '#validate' => 'required|max_length[64]',
                ),
                'width' => array(
                    '#name' => 'width',
                    '#title' => zerophp_lang('Width'),
                    '#type' => 'text',
                    '#validate' => 'numeric|less_than[2000]',
                ),
                'height' => array(
                    '#name' => 'height',
                    '#title' => zerophp_lang('Height'),
                    '#type' => 'text',
                    '#validate' => 'numeric|less_than[2000]',
                ),
                'type' => array(
                    '#name' => 'type',
                    '#title' => zerophp_lang('Type'),
                    '#type' => 'select_build',
                    '#options' => array(
                        'scale and crop' => zerophp_lang('Scale and crop'),
                        'scale' => zerophp_lang('Scale'),
                    ),
                    '#required' => true,
                    '#validate' => 'required|max:12',
                ),
            ),
            '#can_not_delete' => array(1, 2),
        );
    }

    function image_show($path, $style = 'normal') {
        $path_style = "files/styles/$style" . str_replace('files/images', '', $path);

        $this->CI->load->config('image');
        $config = config_item('image');

        $entity = ;

        if (!file_exists($path_style)) {
            $style = $this->loadEntity($style);
            if (empty($style->style)) {
                $style = $this->loadEntity('normal');
            }

            $this->_create_directory($path_style);

            $config['source_image'] = $path;
            $config['new_image'] = $path_style;
            $config['maintain_ratio'] = TRUE;

            if (!empty($style->width)) {
                $config['width'] = $style->width;
            }

            if (!empty($style->height)) {
                $config['height'] = $style->height;
            }

            $error = false;
            switch ($style->#type) {
                case 'scale and crop':
                    $config['width'] = !empty($config['width']) ? $config['width'] : (!empty($config['height']) ? $config['height'] : 100);
                    $config['height'] = !empty($config['height']) ? $config['height'] : $config['width'];

                    $this->CI->image_lib->initialize($config);
                    $image = $this->CI->image_lib->get_image_properties('', true);
                    if (($image['width'] / $config['width']) >= ($image['height'] / $config['height'])) {
                        $config['master_dim'] = 'height';
                        $axis = 'x';
                    }
                    else {
                        $config['master_dim'] = 'width';
                        $axis = 'y';
                    }

                    // Scale
                    $this->CI->image_lib->initialize($config);
                    if (!$this->CI->image_lib->resize() && ENVIRONMENT == 'development') {
                        $error = true;
                    }
                    // Crop
                    else {
                        $config['source_image'] = $path_style;
                        $this->CI->image_lib->initialize($config);
                        $image = $this->CI->image_lib->get_image_properties('', true);

                        if ($axis == 'x') {
                            $config['x_axis'] = floor(($image['width'] - $config['width']) / 2);
                        }
                        else {
                            $config['y_axis'] = floor(($image['height'] - $config['height']) / 2);
                        }

                        unset($config['master_dim']);
                        $config['maintain_ratio'] = FALSE;
                        $this->CI->image_lib->initialize($config);
                        if (!$this->CI->image_lib->crop() && ENVIRONMENT == 'development') {
                            $error = true;
                        }
                    }

                    break;

                case 'scale':
                    if (empty($config['width']) && empty($config['height'])) {
                        $config['width'] = 100;
                    }
                    $this->CI->image_lib->initialize($config);
                    if (!$this->CI->image_lib->resize() && ENVIRONMENT == 'development') {
                        $error = true;
                    }
                    break;
            }

            if ($error) {
                zerophp_get_instance()->response->addMessage($this->CI->image_lib->display_errors(), 'error');
            }
        }

        $config['source_image'] = $path_style;
        $this->CI->image_lib->initialize($config);
        $image = $this->CI->image_lib->get_image_properties('', true);

        return '<img class="lazy loading" data-original="/'.$path_style.'" width="'.$image['width'].'" height="'.$image['height'].'" />';
    }

    private function _create_directory($path_style) {
        $path_style = explode('/', $path_style);
        array_pop($path_style);

        if (is_dir(implode('/', $path_style))) {
            return;
        }

        $path = array_shift($path_style);
        if (count($path_style)) {
            foreach ($path_style as $path_sub) {
                $path .= "/$path_sub";
                if (!is_dir($path)) {
                    mkdir($path, 0777);
                }
            }
        }
    }
}