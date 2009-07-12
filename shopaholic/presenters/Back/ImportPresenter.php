<?php
final class Back_ImportPresenter extends Back_BasePresenter
{
    public function renderDefault()
    {
        $this->template->form = $this->getComponent('importForm');
    }

    public function createComponent($name)
    {
        switch ($name) {
            case 'importForm':
                $form = new AppForm($this, $name);
                $form->addFile('file', __('File:'))
                    ->addRule(Form::FILLED, __('You have to entry file.'));
                $form->addText('provision', __('Provision (%):'))
                    ->addRule(Form::FILLED, __('You have to entry provison.'))
                    ->addRule(Form::NUMERIC, __('Provison has to be a number.'))
                    ->addRule(Form::RANGE, __('Provision has to be between 0 and 99.'), array(0, 99));
                $form->addCheckbox('update_only', __('only update existing products'));

                // availability_id
                $availabilities = array(0 => __('–––'));
                foreach (mapper::product_availabilities()->findAll() as $_) {
                    $availabilities[$_->getId()] = $_->getName();
                }
                $form->addSelect('availability_id', __('Default availability:'), $availabilities);
                
                $form->addSubmit('ok', '✔ ' . __('Import'));
                $form->setDefaults(array('provision' => 0, 'update_only' => TRUE));
                $form->onSubmit[] = array($this, 'onImportFormSubmit');
            break;

            default:
                parent::createComponent($name);
        }
    }

    public function onImportFormSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        if (!($handle = @fopen('safe://' . $form['file']->getValue()->getTemporaryFile(), 'r'))) {
            $form->addError(__('Cannot read file.'));
            return ;
        }

        // provision
        $provision = intval($form['provision']->getValue());

        // read file
        $import = array();
        $codes = array();
        while (($_ = fgetcsv($handle)) !== FALSE) {
            $product = array();
            list($product['code']/*0*/,
                $product['manufacturer']/*1*/,
                $product['name']/*2*/,
                $product['category']/*3*/,
                /*4*/,
                /*5*/,
                $product['price']/*6*/) = $_;
            $product['price'] = intval(round($product['price'] / (100 - $provision) * 100));
            $import[$product['code']] = $product;
            $codes[] = $product['code'];
        }
        fclose($handle);

        // update in db
        foreach (mapper::products()->findByCodes($codes) as $product) {
            $values = array(
                'id' => $product->getId(),
                'price' => $import[$product->getCode()]['price']
            );
            mapper::products()->updateOne($values);
            unset($import[$product->getCode()]);
        }

        // update only?
        if ($form['update_only']->getValue()) {
            $this->redirect('this');
            $this->terminate();
        }

        // manufacturers & categories
        $manufacturers = array();
        $categories = array();
        foreach ($import as $k => $_) {
            $m_key = String::webalize($_['manufacturer']);
            $manufacturers[$m_key] = $_['manufacturer'];
            $import[$k]['manufacturer'] = $m_key;
            $c_key = String::webalize($_['category']);
            $categories[$c_key] = $_['category'];
            $import[$k]['category'] = $c_key;
        }

        foreach ($manufacturers as $nice_name => $name) {
            if (($_ = mapper::manufacturers()->findByNiceName($nice_name)) === NULL) {
                mapper::manufacturers()->insertOne(array(
                    'nice_name' => $nice_name,
                    'name' => $name
                ));
                $manufacturers[$nice_name] = mapper::manufacturers()->findByNiceName($nice_name)->getId();
            } else {
                $manufacturers[$nice_name] = $_->getId();
            }
            $manufacturers[$nice_name] = intval($manufacturers[$nice_name]);
        }

        foreach ($categories as $nice_name => $name) {
            if (($_ = mapper::categories()->findByNiceName($nice_name)) === NULL) {
                mapper::categories()->addOne(array(
                    'nice_name' => $nice_name,
                    'name' => $name
                ));
                $categories[$nice_name] = mapper::categories()->findByNiceName($nice_name)->getId();
            } else {
                $categories[$nice_name] = $_->getId();
            }
            $categories[$nice_name] = intval($categories[$nice_name]);
        }

        // other
        $other = array();
        if ($form['availability_id']->getValue() != 0) {
            $other['availability_id'] = intval($form['availability_id']->getValue());
        }

        // insert products
        foreach ($import as $_) {
            $_['manufacturer_id'] = $manufacturers[$_['manufacturer']]; unset($_['manufacturer']);
            $_['category_id'] = $categories[$_['category']]; unset($_['category']);
            $_['nice_name'] = String::webalize($_['name']);
            $_ = array_merge($_, $other);
            mapper::products()->insertOne($_);
        }

        // all done
        $this->redirect('this');
        $this->terminate();
    }
}