<?php
final class Back_LoginPresenter extends /*Nette\Application\*/Presenter
{
    public function startup()
    {
        if (Environment::getUser()->isAuthenticated() && $this->getAction() !== 'logout') {
            $this->redirect('Dashboard:default');
            $this->terminate();
        }
    }

    public function actionLogout()
    {
        Environment::getUser()->signOut(TRUE);
        $this->redirect('Dashboard:default');
        $this->terminate();
    }

    public function beforeRender()
    {
        // curly brackets
        $this->template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');

        // texy
        $texy = new Texy;
        $this->template->registerHelper('texy', array($texy, 'process'));
    }
    
    public function renderDefault()
    {
        $this->template->form = $this->getComponent('loginForm');
        $referer = Environment::getHttpRequest()->getReferer();
        if ($referer) {
            if ($referer->path === $this->link('Settings:changeLogin')) {
                $this->template->msg = __('Now login with new username and password.');
            }
        }
    }

    public function createComponent($name)
    {
        switch ($name) {
            case 'loginForm':
                $form = new AppForm($this, $name);
                $form->addText('username', __('Username:'));
                $form->addPassword('password', __('Password:'));
                $form->addSubmit('ok', __('Login'));
                $form->onSubmit[] = array($this, 'onLoginFormSubmit');
            break;

            default:
                return parent::createComponent($name);
        }
    }

    public function onLoginFormSubmit(Form $form)
    {
        $user = Environment::getUser();
        $user->setAuthenticationHandler(new SimpleAuthenticator(array(
            ADMIN_USERNAME => ADMIN_PASSWORD
        )));

        $values = $form->getValues();
        try {
            $user->authenticate($values['username'], $values['password']);
            $this->redirect('Dashboard:default');
            $this->terminate();
        } catch (AuthenticationException $e) {
            $this->template->error = $e;
        }
    }
}
