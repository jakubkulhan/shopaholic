<?php
final class Back_SettingsPresenter extends Back_BasePresenter
{
    public function renderDefault()
    {
        $this->template->form = $this->getComponent('settingsForm');
        $this->template->form->setDefaults(Environment::getVariables());
    }

    public function renderChangeLogin()
    {
        $this->template->form = $this->getComponent('changeLoginForm');
        $this->template->form->setDefaults(array(
            'username' => Environment::expand('%adminUsername%')
        ));
    }

    public function createComponent($name)
    {
        switch ($name) {
            case 'settingsForm':
                $form = new AppForm($this, $name);

                // basic
                $form->addGroup(__('Basic'));
                $form->addText('shopName', __('Shop name:'))
                    ->addRule(Form::FILLED, __('You have to entry shop name.'));
                $form->addText('shopSlogan', __('Shop slogan:'))
                    ->addRule(Form::FILLED, __('You have to entry shop slogan.'));
                $form->addText('shopEmail', __('Contact e-mail:'))
                    ->addRule(Form::FILLED, __('You have to entry contact e-mail.'));

                // address
                $form->addGroup(__('Shop address'));
                $form->addText('shopAddressCompany', __('Company:'));
                $form->addText('shopAddressName', __('Name:'))
                    ->addRule(Form::FILLED, __('You have to entry name.'));
                $form->addText('shopAddressStreet', __('Street:'))
                    ->addRule(Form::FILLED, __('You have to entry street.'));
                $form->addText('shopAddressCity', __('City:'))
                    ->addRule(Form::FILLED, __('You have to entry city.'));
                $form->addText('shopAddressPostcode', __('Post code:'))
                    ->addRule(Form::FILLED, __('You have to entry post code.'));

                // miscellaneous
                $form->addGroup(__('Miscellaneous'));
                $form->addText('metaKeywords', __('META keywords:'))
                    ->addRule(Form::FILLED, __('You have to entry META keywords.'));
                $form->addText('metaDescription', __('META description:'))
                    ->addRule(Form::FILLED, __('You have to entry META description.'));
                $form->addText('currency', __('Currency:'))
                    ->addRule(Form::FILLED, __('You have to entry currency.'));
                $form->addText('itemsPerPage', __('Items per page:'))
                    ->addRule(Form::FILLED, __('You have to entry items per page.'))
                    ->addRule(Form::FILLED, __('Items per page has to be a number.'));
                    
                $themes = array(0 => __('–––'));
                foreach (new DirectoryIterator(Environment::expand('%themeDir%')) as $_) {
                    if (!$_->isDot() && $_->isDir()) {
                        $themes[$_->getFilename()] = $_->getFilename();
                    }
                }
                $form->addSelect('theme', __('Theme:'), $themes)->skipFirst();
                $form['theme']->addRule(Form::FILLED, __('You have to select theme.'));

                // submit
                $form->setCurrentGroup(NULL);
                $form->addSubmit('ok', '✔ ' . __('Save'));
                $form->onSubmit[] = array($this, 'onSettingsFormSubmit');
            break;

            case 'changeLoginForm':
                $form = new AppForm($this, $name);
                $form->addText('username', __('Username:'))
                    ->addRule(Form::FILLED, __('You have to entry username.'));
                $form->addPassword('old_password', __('Old password:'))
                    ->addRule(Form::FILLED, __('You have to entry old password.'));
                $form->addPassword('new_password', __('New password:'))
                    ->addRule(Form::FILLED, __('You have to entry new password.'));
                $form->addPassword('new_password_again', __('New password (again):'))
                    ->addRule(Form::FILLED, __('You have to entry new password again.'))
                    ->addRule(Form::EQUAL, __('New passwords have to match.'), $form['new_password']);
                $form->addSubmit('ok', '✔ ' . __('Change'));
                $form->onSubmit[] = array($this, 'onChangeLoginFormSubmit');
            break;

            default:
                parent::createComponent($name);
        }
    }

    public function onSettingsFormSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        $content = "<?php\nreturn " . var_export($form->getValues(), TRUE) . ";\n";

        if (!@file_put_contents(Environment::expand('safe://%settingsFile%'), $content)) {
            $form->addError(__('Cannot write settings.'));
            return ;
        }

        adminlog::log(__('Updated settings'));

        $this->redirect('this');
        $this->terminate();
    }

    public function onChangeLoginFormSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        if ($form['old_password']->getValue() !== Environment::expand('%adminPassword%')) {
            $form->addError(__('Bad old password.'));
            return ;
        }

        $content = "<?php\nreturn " .
            var_export(array(
                    'username' => $form['username']->getValue(),
                    'password' => $form['new_password']->getValue()
            ), TRUE) .
            ";\n";
        if (!@file_put_contents(Environment::expand('%adminLoginFile%'), $content)) {
            $form->addError(__('Cannot write new login settings.'));
            return ;
        }

        Environment::getUser()->signOut(TRUE);

        adminlog::log(__('Changed login credentials, logging out'));

        $this->redirect('this');
        $this->terminate();
    }
}
