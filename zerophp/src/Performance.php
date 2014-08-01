<?php
namespace ZeroPHP\ZeroPHP;

class Performance {
    function clearCache($zerophp) {
        \Cache::flush();

        $zerophp->addMessage(zerophp_lang('The cache was deleted successfully.'));

        \Redirect::to('/admin');
    }

    function clearOPCache($zerophp) {
        opcache_reset();

        $zerophp->addMessage(zerophp_lang('The opcache was deleted successfully.'));

        \Redirect::to('/admin');
    }
}

//Cache