<?php
abstract class Back_BasePresenter extends /*Nette\Application\*/Presenter
{
    public function startup()
    {
        if (!Environment::getUser()->isAuthenticated()) {
            $this->redirect('Login:default');
            $this->terminate();
        }
    }

    public function beforeRender()
    {
        // curly brackets
        $this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');

        // texy
        $texy = new Texy;
        $this->template->registerHelper('texy', array($texy, 'process'));

        // user
        $this->template->user = Environment::getUser();

        // order statuses
        $this->template->order_statuses = mapper::order_statuses()->findAll();

        // date
        $this->template->registerHelper('date', array(__CLASS__, 'date'));
    }

    public static function date($str)
    {
        return date(Environment::expand('%datetimeFormat%'), strtotime($str));
    }
}
