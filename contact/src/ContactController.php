<?php

namespace ZeroPHP\Contact;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class ContactController {
    private function _unsetFormItem(&$form) {
        unset($form['contact_id'], $form['created_at'], $form['updated_at']);
    }

    function createForm($zerophp) {
        $entity = Entity::loadEntityObject('ZeroPHP\Contact\Contact');
        $form = $entity->crudCreateForm();
        $this->_unsetFormItem($form);

        //unset($form['contact_id']);

        //zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build($form));
    }

    function show($zerophp, $id){}

    function showList($zerophp){}

    function deleteForm($zerophp, $id) {}
}