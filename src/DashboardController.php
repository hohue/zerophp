<?php
namespace ZeroPHP\ZeroPHP;

class DashboardController {
    public function showHomepage($zerophp) {
        if ($zerophp->response->isAdminPanel()) {
            $zerophp->response->addContent(zerophp_view('dashboard-admin'));
        }
        else {
            $zerophp->response->addContent(zerophp_view('dashboard'));
        }
    }
}