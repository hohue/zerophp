<?php
namespace ZeroPHP\ZeroPHP;

class PerformanceController {




    
    function cache_clear_system() {
        $this->cachef->clean_system();

        $this->response->messages_add($this->lang->line('The system cache was deleted successfully.'));

        $zerophp->response->addContent('dashboard', zerophp_lang('Clear cache: System'));
    }

    function cache_clear_content() {
        $this->cachef->clean();

        $this->response->messages_add($this->lang->line('The content cache was deleted successfully.'));

        $zerophp->response->addContent('dashboard', zerophp_lang('Clear cache: Content'));
    }

    function cache_clear_file() {
        $this->cachef->clean_file();

        $this->response->messages_add($this->lang->line('The file cache was deleted successfully.'));

        $zerophp->response->addContent('dashboard', zerophp_lang('Clear cache: File'));
    }

    function cache_clear_opcache() {
        opcache_reset();

        $this->response->messages_add($this->lang->line('The opcache was deleted successfully.'));

        $zerophp->response->addContent('dashboard', zerophp_lang('Clear cache: Opcache'));
    }
}