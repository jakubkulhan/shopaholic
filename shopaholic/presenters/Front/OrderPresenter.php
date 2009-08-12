<?php
final class Front_OrderPresenter extends Front_BasePresenter
{
    /**
     * Adds item to cart
     */
    public function actionAdd()
    {
        // check request
        $request = Environment::getHttpRequest();
        if (!$request->isMethod('POST')) {
            $this->redirectUri(Environment::getHttpRequest()->getReferer()->getAbsoluteUri());
            $this->terminate();
            return ;
        }

        // find product
        $product_id = $request->getPost('product_id', 0);
        $amount = intval($request->getPost('amount', 1));

        $cart = Environment::getSession(SESSION_ORDER_NS);
        $product = mapper::products()->findById($product_id);

        if ($product === NULL) {
            $this->redirectUri($request->getReferer()->getAbsoluteUri());
            $this->terminate();
            return ;
        }

        // add product
        if (!isset($cart->products)) {
            $cart->products = array();
        }

        if (!isset($cart->products[$product->getId()])) {
            $cart->products[$product->getId()] = 0;
        }

        $cart->products[$product->getId()] += $amount;

        // calculate total
        $cart->total = 0;

        mapper::products()->findByIds(array_keys($cart->products));
        foreach ($cart->products as $id => $amount) {
            $cart->total += intval(mapper::products()->findById($id)->getPrice()) * $amount;
        }

        // redirect
        //$this->redirectUri(Environment::getHttpRequest()->getReferer()->getAbsoluteUri());
        $this->redirect('showCart');
        $this->terminate();
        return ;
    }

    /**
     * Removes item from cart
     */
    public function actionDelete()
    {
        // check request
        $request = Environment::getHttpRequest();
        if (!$request->isMethod('POST')) {
            $this->redirectUri(Environment::getHttpRequest()->getReferer()->getAbsoluteUri());
            $this->terminate();
            return ;
        }

        // find product
        $product_id = $request->getPost('product_id', 0);
        $amount = intval($request->getPost('amount', 1));

        $cart = Environment::getSession(SESSION_ORDER_NS);
        $product = mapper::products()->findById($product_id);

        if ($product === NULL) {
            $this->redirectUri($request->getReferer()->getAbsoluteUri());
            $this->terminate();
            return ;
        }

        // delete product
        if (!isset($cart->products)) {
            $cart->products = array();
        }

        if (isset($cart->products[$product->getId()])) {
            $cart->products[$product->getId()] -= $amount;
            if ($cart->products[$product->getId()] < 1) {
                unset($cart->products[$product->getId()]);
            }
        }

        // calculate total
        $cart->total = 0;

        mapper::products()->findByIds(array_keys($cart->products));
        foreach ($cart->products as $id => $amount) {
            $cart->total += intval(mapper::products()->findById($id)->getPrice()) * $amount;
        }

        // redirect
        //$this->redirectUri($request->getReferer()->getAbsoluteUri());
        $this->redirect('showCart');
        $this->terminate();
        return ;
    }

    /**
     * Complete order and send to process
     */
    public function actionComplete()
    {
        $order = Environment::getSession(SESSION_ORDER_NS);
        if (!(isset($order->products) && !empty($order->products))) {
            $this->redirect('showCart');
            $this->terminate();
            return ;
        }
        if (!(isset($order->data) && !empty($order->data))) {
            $this->redirect('fillData');
            $this->terminate();
            return ;
        }
    }

    /**
     * Commit
     */
    public function actionCommit()
    {
        $order = Environment::getSession(SESSION_ORDER_NS);
        $data = $order->data;
        unset($data['same_delivery']);
        $data['delivery_type'] = mapper::order_delivery_types()->findById($data['delivery_type']);
        $data['payment_type'] = mapper::order_payment_types()->findById($data['payment_type']);
        $data['status'] = mapper::order_statuses()->findInitial();

        $new = new order($data);
        if (mapper::orders()->save($new, $order->products, $order->visited)) {
            unset($order->products, $order->data, $order->visited);
            $this->template->ok = TRUE;
        } else {
            $this->template->ok = FALSE;
        }
    }

    /**
     * Show cart with all items
     */
    public function renderShowCart()
    {
        $this->template->title = __('Cart');
        $this->template->products = array();
        $this->template->total = 0;

        // recount total
        $cart = Environment::getSession(SESSION_ORDER_NS);
        if (isset($cart->products)) {
            $this->template->products = mapper::products()->findByIds(array_keys($cart->products));
            $this->template->product_counts = $cart->products;
            $cart->total = 0;

            foreach ($this->template->products as $product) {
                $cart->total += $product->getPrice() * $cart->products[$product->getId()];
            }
            $this->template->total = $cart->total;
        }
    }

    /**
     * Fill data form
     */
    public function renderFillData()
    {
        $this->template->title = __('Paying options');
        $this->template->form = $this->getComponent('dataForm');
    }

    /**
     * Complete order
     */
    public function renderComplete()
    {
        $order = Environment::getSession(SESSION_ORDER_NS);

        $this->template->title = __('Complete order');
        $this->template->products = mapper::products()->findByIds(array_keys($order->products));
        $this->template->product_counts = $order->products;
        $this->template->total = $order->total = 0;

        // products
        foreach ($this->template->products as $product) {
            $order->total += $product->getPrice() * $order->products[$product->getId()];
        }

        $this->template->total = $order->total;

        // delivery & payment
        $this->template->delivery_type =
            mapper::order_delivery_types()->findById($order->data['delivery_type']);
        $this->template->total += $this->template->delivery_type->getPrice();

        $this->template->payment_type =
            mapper::order_payment_types()->findById($order->data['payment_type']);
        $this->template->total += $this->template->payment_type->getPrice();

        // data
        $this->template->data = $order->data;
    }

    /**
     * A little componen factory
     * @param string
     */
    public function createComponent($name)
    {
        switch ($name) {
            case 'dataForm':
                $data = isset(Environment::getSession(SESSION_ORDER_NS)->data)
                    ? Environment::getSession(SESSION_ORDER_NS)->data
                    : array();
                $form = new AppForm($this, $name);

                // contacts
                $form->addGroup(__('Contacts'));
                $form->addText('email', __('E-mail:'))
                    ->setEmptyValue('@')
                    ->addRule(Form::FILLED, __('You have to enter your e-mail.'))
                    ->addRule(Form::EMAIL, __('This is not an e-mail address.'));
                $form->addText('phone', __('Phone number:'))
                    ->addRule(Form::FILLED, __('You have to enter your phone number.'))
                    ->addRule(Form::NUMERIC, __('Phone number has to be number.'));

                // payer
                $form->addGroup(__('Payer'));
                $form->addText('payer_name', __('Name:'))
                    ->addRule(Form::FILLED, __('You have to enter your name.'));
                $form->addText('payer_lastname', __('Last name:'))
                    ->addRule(Form::FILLED, __('You have to enter your last name.'));
                $form->addText('payer_company', __('Company:'));
                $form->addText('payer_street', __('Street:'))
                    ->addRule(Form::FILLED, __('You have to enter your street.'));
                $form->addText('payer_city', __('City:'))
                    ->addRule(Form::FILLED, __('You have to enter your city.'));
                $form->addText('payer_postcode', __('Post code:'))
                    ->addRule(Form::FILLED, __('You have to enter your post code.'))
                    ->addRule(Form::NUMERIC, __('Post code has to be number.'));
                $form->addCheckbox('same_delivery', __('deliver at same address (you do not need to fill Delivery address below)'))
                    ->setValue(TRUE);

                // delivery address
                $form->addGroup(__('Delivery address'));
                $form->addText('delivery_name', __('Name:'))
                    ->addConditionOn($form['same_delivery'], Form::EQUAL, FALSE)
                        ->addRule(Form::FILLED, __('You have to enter your name.'));
                $form->addText('delivery_lastname', __('Last name:'))
                    ->addConditionOn($form['same_delivery'], Form::EQUAL, FALSE)
                        ->addRule(Form::FILLED, __('You have to enter your last name.'));
                $form->addText('delivery_street', __('Street:'))
                    ->addConditionOn($form['same_delivery'], Form::EQUAL, FALSE)
                        ->addRule(Form::FILLED, __('You have to enter your street.'));
                $form->addText('delivery_city', __('City:'))
                    ->addConditionOn($form['same_delivery'], Form::EQUAL, FALSE)
                        ->addRule(Form::FILLED, __('You have to enter your city.'));
                $form->addText('delivery_postcode', __('Post code:'))
                    ->addConditionOn($form['same_delivery'], Form::EQUAL, FALSE)
                        ->addRule(Form::FILLED, __('You have to enter your post code.'))
                        ->addRule(Form::NUMERIC, __('Post code has to be number.'));

                // delivery type
                $form->addGroup(__('Delivery type'));
                $delivery_types = array();
                foreach (mapper::order_delivery_types()->findAll() as $delivery_type) {
                    $delivery_types[$delivery_type->getId()] = $delivery_type->getName() .
                        Environment::expand(' (' . $delivery_type->getPrice() . ' %currency%)');
                }
                $form->addSelect('delivery_type', __('Type:'), $delivery_types);

                // payment type
                $form->addGroup(__('Payment type'));
                $payment_types = array();
                foreach (mapper::order_payment_types()->findAll() as $payment_type) {
                    $payment_types[$payment_type->getId()] = $payment_type->getName() .
                        Environment::expand(' (' . $payment_type->getPrice() . ' %currency%)');
                }
                $form->addSelect('payment_type', __('Type:'), $payment_types);

                // comment
                $form->addGroup(__('Comment'));
                $form->addTextarea('comment', __('Comment:'));

                // submit
                $form->setCurrentGroup(NULL);
                $form->addSubmit('ok', '(3/3) ' . __('Complete order »'));
                $form['ok']->setRendered(TRUE);
                $form->onSubmit[] = array($this, 'onDataFormSubmit');

                // defaults
                if (isset(Environment::getSession(SESSION_ORDER_NS)->data)) {
                    $form->setDefaults(Environment::getSession(SESSION_ORDER_NS)->data);
                }
            break;

            default:
                parent::createComponent($name);
        }
    }

    /**
     * Data form
     * @param string
     */
    public function onDataFormSubmit(Form $form)
    {
        // check for validity
        if (!$form->isValid()) {
            return ;
        }

        // get data
        $order = Environment::getSession(SESSION_ORDER_NS);
        $order->data = $form->getValues();
        if ($order->data['same_delivery']) {
            foreach ($order->data as $k => $v) {
                if (strncmp($k, 'payer_', strlen('payer_')) === 0) {
                    $order->data['delivery_' . substr($k, strlen('payer_'))] = $v;
                }
            }
        }
        $this->redirect('complete');
    }
}
