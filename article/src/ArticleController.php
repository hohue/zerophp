<?php

namespace ZeroPHP\Article;

use ZeroPHP\ZeroPHP\Entity;
use ZeroPHP\ZeroPHP\Form;

class ArticleController {
    private function _unsetFormItem(&$form) {
        //@todo 4 Viet chuc nang image cho article
        unset($form['image'], $form['created_at'], $form['updated_at'], $form['active']);
    }

    function createForm($zerophp) {
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $form = $entity->crudCreateForm();
        $this->_unsetFormItem($form);

        unset($form['article_id']);

        //zerophp_devel_print($form);

        $zerophp->response->addContent(Form::build($form));
    }

    function show($zerophp, $article_id){
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $article = $entity->loadEntity($article_id);

        $zerophp->response->addContent(zerophp_view('article_read', zerophp_object_to_array($article)));
    }

    function updateForm($zerophp, $article_id) {
        $entity = Entity::loadEntityObject('ZeroPHP\Article\Article');
        $form = $entity->crudCreateForm();
        $this->_unsetFormItem($form);

        $zerophp->response->addContent(Form::build($form));
    }

    function showList($zerophp){}

    function deleteForm($zerophp, $article_id) {}
}