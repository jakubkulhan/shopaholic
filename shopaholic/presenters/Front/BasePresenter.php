<?php
abstract class Front_BasePresenter extends /*Nette\Application\*/Presenter
{
    public function startup()
    {
        // preload
        mapper::manufacturers()->findAll();
        mapper::categories()->findMain();
        mapper::pages()->findNotRef();
    }

    public function beforeRender()
    {
        // curly brackets
        $this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');

        // texy
        $texy = new Texy;
        $this->template->registerHelper('texy', array($texy, 'process'));

        // side
        $side = array();
        $side['categories'] = mapper::categories()->findMain();
        $side['manufacturers'] = mapper::manufacturers()->findAll();
        $side['pages'] = mapper::pages()->findNotRef();
        $side['cart'] = Environment::getSession(SESSION_ORDER_NS);

        $this->template->side = (object) $side;
    }
}
