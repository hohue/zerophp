<?php
namespace ZeroPHP\ZeroPHP;

class DashboardController {
    public function showHomepage($zerophp) {
        if ($zerophp->response->isAdminPanel()) {
            $zerophp->response->addContent(zerophp_view('dashboard-admin'), zerophp_lang('Homepage'));
        }
        else {
            $zerophp->response->addContent(zerophp_view('dashboard'), zerophp_lang('Homepage'));
        }
    }
}