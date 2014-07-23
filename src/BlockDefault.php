<?php 
namespace ZeroPHP\ZeroPHP;

class BlockDefault {
    // @todo 9 Get Admin link from DB
    function admin_menu($block) {
        return zerophp_view('admin_block_menu', $data);
    }

    function admin_menu_access($block) {
        return zerophp_is_adminpanel();
    }
}