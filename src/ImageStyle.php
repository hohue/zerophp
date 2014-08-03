<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\EntityInterface;
use Intervention\Image\ImageManagerStatic as Image;

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
                    '#validate' => 'required|max:64',
                ),
                'width' => array(
                    '#name' => 'width',
                    '#title' => zerophp_lang('Width'),
                    '#type' => 'text',
                    '#validate' => 'numeric|between:0,2000',
                ),
                'height' => array(
                    '#name' => 'height',
                    '#title' => zerophp_lang('Height'),
                    '#type' => 'text',
                    '#validate' => 'numeric|between:0,2000',
                ),
                'type' => array(
                    '#name' => 'type',
                    '#title' => zerophp_lang('Type'),
                    '#type' => 'select',
                    '#options' => array(
                        'scale and crop' => zerophp_lang('Scale and crop'),
                        'scale' => zerophp_lang('Scale'),
                    ),
                    '#required' => true,
                    '#validate' => 'required',
                ),
            ),
            '#can_not_delete' => array(1, 2),
        );
    }

    public function image($file_original, $style = 'normal') {
        $image_style = new \ZeroPHP\ZeroPHP\ImageStyle;
        $image_style = $image_style->loadEntity($style);

        if (!isset($image_style->style)) {
            $img = Image::make(MAIN . $file_original);

            $image = array(
                'path' => $file_original,
                'width' => $img->width(),
                'height' => $img->height(),
            );
        }
        else {
            $path_file = \Config::get('file.path');
            $path_style = "$path_file/styles/$style";
            $file_style = "$path_style/" . str_replace("$path_file/images/", '', $file_original);

            if (!file_exists(MAIN . $file_style)) {
                $this->_createDirectory(MAIN . $file_style);
                $img = Image::make(MAIN . $file_original);

                switch ($image_style->type) {
                    case 'scale and crop':
                        $img->resize($image_style->width, $image_style->height, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $img->crop($image_style->width, $image_style->height);

                        $img->fill(\Config::get('file.image_background', '#ffffff'), 0, 0);

                        break;

                    case 'scale':
                        $image_style->width = !empty($image_style->width) ? $image_style->width : null;
                        $image_style->height = !empty($image_style->height) ? $image_style->height : null;

                        if (!empty($image_style->upsize)) {
                            $img->resize($image_style->width, $image_style->height, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                        }
                        else {
                            $img->resize($image_style->width, $image_style->height, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }

                        break;
                }

                $img->save(MAIN . $file_style, \Config::get('file.image_quality', 90));
            }
            else {
                $img = Image::make(MAIN . $file_style);
            }

            $image = array(
                'path' => $file_style,
                'width' => $img->width(),
                'height' => $img->height(),
            );
        }

        return $image;
    }

    private function _createDirectory($path_style) {
        //remove file name
        $path_style = substr($path_style, 0, strrpos($path_style, '/'));
        
        if (\File::isDirectory($path_style)) {
            return;
        }

        $path_style = explode('/', $path_style);
        $path = array_shift($path_style);
        if (count($path_style)) {
            foreach ($path_style as $path_sub) {
                $path .= "/$path_sub";
                if (!\File::isDirectory($path)) {
                    \File::makeDirectory($path);
                }
            }
        }
    }
}

//Checked