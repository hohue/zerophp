<?php
namespace ZeroPHP\ZeroPHP;

use ZeroPHP\ZeroPHP\Entity;

class Response {
    public $data = array(
        'header' => array(),
        'closure' => array(),
        'title' => array(),
        'page_title' => '',
        'messages' => '',
        'regions' => array(),
        'content' => array(),
        'body_class' => '',
        'breadcrumb' => array(),
        'tabs' => array(),
    );
    public $output_type = 'normal';
    
    private $js = array(
        'inline' => '',
        'settings' => array(),
    );

    /**
     *
     * @param string $template as template_filename|module_name
     * @param string $page_title // tieu de cua trang
     * @param array $data // du lieu truyen vao view
     */
    function addContent($content, $page_title = null) {
        if ($page_title) {
            $this->setPageTitle($page_title);
        }

        $this->data['content'][] = $content;
    }

    private function _buildData() {
            $this->_loadRegion(); // Load content of regions
            $this->data['content'] = implode('', $this->getContent());

            $this->data['header'] = implode('', $this->getHeader());

            //Build Breadcrumb
            if (count($this->data['breadcrumb'])) {
                $this->data['breadcrumb'] = template_item_list($this->data['breadcrumb']);
            }
            else {
                $this->data['breadcrumb'] = '';
            }

            //@todo 5 Tach thanh function buildClosure
            if ($this->js['settings']) {
                $this->data['closure'][] = '<script type="text/javascript">jQuery.extend(FW.settings, ' . json_encode(array_filter($this->js['settings'])) . ');</script>';
            }
            if ($this->js['inline']) {
                $this->data['closure'][] = $this->js['inline'];
            }

            $this->data['title'] = implode('', $this->getTitle());
            $this->data['closure'] = implode('', $this->getClosure());

            $this->setBodyClass();
            $this->data['body_class'] = trim($this->data['body_class']);
    }

    public function setBodyClass() {
        if (zerophp_is_frontpage()) {
            $this->addBodyClass('front');
        }
        elseif (zerophp_is_adminpanel()) {
            $this->addBodyClass('admin');
        }
        elseif (zerophp_is_userpanel()) {
            $this->addBodyClass('up');
        }
    }

    private function _loadRegion() {
        $regions = \Config::get('theme.regions', array());

        if (count($regions)) {
            $block = Entity::loadEntityObject('\ZeroPHP\ZeroPHP\Block');
            $blocks = $block->loadEntityAll();
            foreach ($regions as $region_key => $region_name) {
                if (isset($blocks[$region_key]) && count($blocks[$region_key])) {
                    foreach ($blocks[$region_key] as $block_value) {
                        if (!isset($this->data['regions'][$region_key])) {
                            $this->data['regions'][$region_key] = '';
                        }
                        $this->data['regions'][$region_key] .= $block->run($block_value);
                    }
                }
                else {
                    $this->data['regions'][$region_key] = '';
                }
            }
        }

        $body_class = array();
        if (!empty($this->data['regions']['user panel sidebar'])) {
            $body_class[] = 'userpanel';
        }

        if (!empty($this->data['regions']['left sidebar'])) {
            $body_class[] = 'left';
        }

        if (!empty($this->data['regions']['right sidebar'])) {
            $body_class[] = 'right';
        }

        if (count($body_class)) {
            $body_class = implode('_', $body_class) . '_sidebar';
            $this->addBodyClass($body_class);
        }
    }

    public function addBodyClass($class) {
        $this->data['body_class'] .= " $class";
    }

    public function getContent() {
        return $this->data['content'];
    }

    public function getTitle() {
        return $this->data['title'];
    }

    public function addTitle($title) {
        $this->setPageTitle($title);
        array_unshift($this->data['title'], $title);
    }

    public function setPageTitle($title) {
        $this->data['page_title'] = $title;
    }

    public function getPageTitle() {
        return $this->data['page_title'];
    }

    public function addHeader($item, $key = '') {
        if ($key) {
            $this->data['header'][$key] = $item;
        }
        else {
            $this->data['header'][] = $item;
        }
    }

    public function getHeader() {
        return $this->data['header'];
    }

    function addClosure($item, $key = '') {
        if ($key) {
            $this->data['closure'][$key] = $item;
        }
        else {
            $this->data['closure'][] = $item;
        }
    }

    function getClosure() {
        return $this->data['closure'];
    }

    function addMessage($message = null, $type = 'success') {
        // Set message
        if ($message) {
            $messages = \Session::get(__METHOD__, array());

            if (!count($messages)) {
                $messages = array();
            }

            $messages[$type][] = $message;
            \Session::put(__METHOD__, $messages);
        }
        // Get message
        else {
            $message = \Session::get(__METHOD__, array());
            \Session::forget(__METHOD__);

            return $message;
        }
    }

    function getMessage() {
        return $this->addMessage();
    }

    function addContentJSON($data = array()) {
        $this->data['content'] = array_merge($this->data['content'], $data);
    }

    function getOutputType() {
        return $this->output_type;
    }

    function setOutputType($type) {
        $this->output_type = $type;
    }

    public function getData() {
        return $this->data;
    }

    //@todo 9 Cho phep cai dat trang chu khac "/"
    public function isFrontPage() {
        return zerophp_get_instance()->request->url() == '/';
    }

    public function isAdminPanel() {
        return $this->getOutputType() == 'admin';
    }

    public function isUserPanel() {
        return $this->getOutputType() == 'up';
    }

    public function output() {
        $output = '';

        switch ($this->getOutputType()) {
            case 'ajax':
                $output = '<div class="ajax_html_return">' . implode('', $this->getContent()) . '</div>';
                break;

            case 'json':
                $output = \Response::json($this->getContent());
                break;
                
            case 'esi':
                $output = implode('', $this->getContent());
                break;
                
            case 'file':
                $output = \Response::download(reset($this->getContent()));
                break;
            
            default:
                if ($this->isAdminPanel()) {
                    $page = 'page-admin';
                }
                else {
                    $page = 'page';
                }

                $this->_buildData();
                $output = \View::make("layouts/$page", $this->getData());
                break;
        }

        return $output;
    }

    /**
     * @param array $items = array(
     *      0 => array(
     *          '#item' => '<a href="#"> Tat ca danh muc </a>'
     *      ),
     *      1 => array(
     *          '#item' => '<a href="#"> thoi trang nam </a>',
     *          '#children' => array(
     *              0 => array(
     *                  '#item' => '<a href="#"> Quan ao nam </a>'
     *              ),
     *              1 => array(
     *                  '#item' => '<a href="#"> giay dep nam </a>'
     *              ),
     *              2 => array(
     *                  '#item' => '<a href="#"> phu kien nam </a>'
     *              )
     *          )
     *      )
     *  );
     */
    function setBreadcrumb($items = array()) {
        $this->data['breadcrumb'] = $items;

        array_unshift($this->data['breadcrumb'], array(
            '#item' => zerophp_anchor(\URL::to('/'), zerophp_lang('Home')),
        ));
    }

    function getBreadcrumb() {
        return $this->data['breadcrumb'];
    }

    function showMessage($zerophp) {
        $vars = array(
            'messages' => zerophp_message(),
        );
        $zerophp->response->addContent(zerophp_view('response_message', $vars));
    }
}