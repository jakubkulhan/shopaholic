<?php
final class Back_OrdersPresenter extends Back_BasePresenter
{
    public function actionStatus($id)
    {
        $this->template->status = mapper::order_statuses()->findById($id);
        if (!$this->template->status) {
            $this->redirect('Dashboard:');
            $this->terminate();
        }
    }

    public function actionShow($id)
    {
        $this->template->order = mapper::orders()->findById($id);
        if (!$this->template->order) {
            $this->redirect('Dashboard:');
            $this->terminate();
        }
        $this->template->products = mapper::orders()->findProducts($this->template->order);
    }

    public function actionEditStatus($id)
    {
        $id = intval($id);
        $this->template->status = mapper::order_statuses()->findById($id);
        if (!$this->template->status) {
            $this->redirect('manageStatuses');
            $this->terminate();
        }
    }

    public function actionEditPaymentType($id)
    {
        $id = intval($id);
        $this->template->payment_type = mapper::order_payment_types()->findById($id);
        if (!$this->template->payment_type) {
            $this->redirect('managePaymentTypes');
            $this->terminate();
        }
    }

    public function actionEditDeliveryType($id)
    {
        $id = intval($id);
        $this->template->delivery_type = mapper::order_delivery_types()->findById($id);
        if (!$this->template->delivery_type) {
            $this->redirect('manageDeliveryTypes');
            $this->terminate();
        }
    }

    public function renderStatus()
    {
        $this->template->title = __('Orders with status') . ' ' . 
            $this->template->status->getName();
        $this->template->orders = mapper::orders()->findByStatusId($this->template->status->getId());
    }

    public function renderShow()
    {
        $this->template->title = __('Order number') . ' ' . $this->template->order->getId();
        $this->template->change_order_status_form = $this->getComponent('changeOrderStatusForm');
        $this->template->change_order_status_form->setDefaults(array(
            'status_id' => $this->template->order->getStatus() ?
                $this->template->order->getStatus()->getId()
                : 0,
            'id' => $this->template->order->getId()
        ));

        $this->template->send_mail_form = $this->getComponent('sendMailForm');
        $this->template->send_mail_form->setDefaults(array(
            'to' => $this->template->order->getEmail(),
            'order_id' => $this->template->order->getId()
        ));

        $this->template->visited_products = mapper::orders()->findVisitedProducts($this->template->order);
        $this->template->sent_emails = mapper::order_emails()->findByOrderId($this->template->order->getId());
    }

    public function renderManageStatuses()
    {
        $this->template->title = __('Manage statuses');
        $this->template->form = $this->getComponent('manageStatusesForm');
    }

    public function renderAddStatus()
    {
        $this->template->title = __('Add status');
        $this->template->form = $this->getComponent('statusAddForm');
    }

    public function renderEditStatus()
    {
        $this->template->title = $this->template->status->getName() . ' – ' . __('Edit status');
        $this->template->form = $this->getComponent('statusEditForm');
        $this->template->form->setDefaults($this->template->status->__toArray());
    }

    public function renderManagePaymentTypes()
    {
        $this->template->title = __('Manage payment types');
        $this->template->form = $this->getComponent('managePaymentTypesForm');
    }

    public function renderAddPaymentType()
    {
        $this->template->title = __('Add payment type');
        $this->template->form = $this->getComponent('paymentTypeAddForm');
    }

    public function renderEditPaymentType()
    {
        $this->template->title = $this->template->payment_type->getName() . ' – ' . __('Edit payment type');
        $this->template->form = $this->getComponent('paymentTypeEditForm');
        $this->template->form->setDefaults($this->template->payment_type->__toArray());
    }

    public function renderManageDeliveryTypes()
    {
        $this->template->title = __('Manage delivery types');
        $this->template->form = $this->getComponent('manageDeliveryTypesForm');
    }

    public function renderAddDeliveryType()
    {
        $this->template->title = __('Add delivery type');
        $this->template->form = $this->getComponent('deliveryTypeAddForm');
    }

    public function renderEditDeliveryType()
    {
        $this->template->title = $this->template->delivery_type->getName() . ' – ' . __('Edit delivery type');
        $this->template->form = $this->getComponent('deliveryTypeEditForm');
        $this->template->form->setDefaults($this->template->delivery_type->__toArray());
    }

    public function createComponent($name)
    {
        switch ($name) {
            case 'manageStatusesForm':
                $form = new AppForm($this, $name);
                $statuses = array(0 => __('–––'));
                foreach (mapper::order_statuses()->findAll() as $_) {
                    $statuses[$_->getId()] = $_->getName() . ($_->getInitial() ? ' ✱' : '');
                }
                $form->addSelect('id', __('Status:'), $statuses)->skipFirst();
                $form['id']->addRule(Form::FILLED);
                $form->addSubmit('edit', '✎ ' . __('Edit'))
                    ->onClick[] = array($this, 'onManageStatusesFormEdit');
            break;

            case 'statusAddForm':
            case 'statusEditForm':
                $prefix_len = strlen('status');
                $action = substr($name, $prefix_len);
                $action = substr($action, 0, strlen($action) - 4);

                $form = new AppForm($this, $name);
                if (substr($name, strlen($name) - 8) === 'EditForm') {
                    $form->addHidden('id');
                }
                $form->addText('name', __('Name:'))
                    ->addRule(Form::FILLED, __('You have to entry status name.'));
                $form->addCheckbox('initial', __('initial for orders'));
                $form->addSubmit('ok', '✔ ' . __($action));
                $form->onSubmit[] =  array($this,
                        'on' . substr($name, 0, strlen($name) - 4) . 'Submit');
            break;

            case 'managePaymentTypesForm':
                $form = new AppForm($this, $name);
                $types = array(0 => __('–––'));
                foreach (mapper::order_payment_types()->findAll() as $_) {
                    $types[$_->getId()] = $_->getName();
                }
                $form->addSelect('id', __('Payment type:'), $types)->skipFirst();
                $form['id']->addRule(Form::FILLED);
                $form->addSubmit('edit', '✎ ' . __('Edit'))
                    ->onClick[] = array($this, 'onManagePaymentTypesFormEdit');
            break;
            
            case 'manageDeliveryTypesForm':
                $form = new AppForm($this, $name);
                $types = array(0 => __('–––'));
                foreach (mapper::order_delivery_types()->findAll() as $_) {
                    $types[$_->getId()] = $_->getName();
                }
                $form->addSelect('id', __('Delivery type:'), $types)->skipFirst();
                $form['id']->addRule(Form::FILLED);
                $form->addSubmit('edit', '✎ ' . __('Edit'))
                    ->onClick[] = array($this, 'onManageDeliveryTypesFormEdit');
            break;

            case 'paymentTypeAddForm':
            case 'paymentTypeEditForm':
                $prefix_len = strlen('paymentType');
            case 'deliveryTypeAddForm':
            case 'deliveryTypeEditForm':
                if (!isset($prefix_len)) {
                    $prefix_len = strlen('deliveryType');
                }
                $action = substr($name, $prefix_len);
                $action = substr($action, 0, strlen($action) - 4);


                $form = new AppForm($this, $name);
                if (substr($name, strlen($name) - 8) === 'EditForm') {
                    $form->addHidden('id');
                }
                $form->addText('name', __('Name:'))
                    ->addRule(Form::FILLED, __('You have to entry name.'));
                $form->addText('price', __('Price:'))
                    ->addRule(Form::FILLED, __('You have to entry price.'))
                    ->addRule(Form::NUMERIC, __('Price has to be a number.'));
                $form->addSubmit('ok', '✔ ' . __($action));
                $form->onSubmit[] =  array($this,
                        'on' . substr($name, 0, strlen($name) - 4) . 'Submit');
            break;

            case 'changeOrderStatusForm':
                $form = new AppForm($this, $name);
                $statuses = array(0 => __('–––'));
                foreach (mapper::order_statuses()->findAll() as $_) {
                    $statuses[$_->getId()] = $_->getName();
                }
                $form->addSelect('status_id', __('Status:'), $statuses)->skipFirst()
                    ->addRule(Form::FILLED, __('You have to entry status.'));
                $form->addHidden('id');
                $form->addSubmit('ok', '✔ ' . __('Change'));
                $form->onSubmit[] = array($this, 'onChangeOrderStatusFormSubmit');
            break;

            case 'sendMailForm':
                $form = new AppForm($this, $name);
                $form->addText('to', __('To:'))
                    ->addRule(Form::EMAIL, __('E-mail has to be an e-mail.'));
                $form->addText('subject', __('Subject:'))
                    ->addRule(Form::FILLED, __('You have to entry subject.'));
                $form->addTextArea('body', __('Body:'))
                    ->addRule(Form::FILLED, __('You have to entry text.'));
                $form->addSubmit('ok', '✔ ' . __('Send'));
                $form->onSubmit[] = array($this, 'onSendMailFormSubmit');
                $form->addHidden('order_id');
            break;

            default:
                parent::createComponent($name);
        }
    }

    public function onManageStatusesFormEdit(SubmitButton $b)
    {
        $form = $this->getComponent('manageStatusesForm');
        $this->redirect('editStatus', $form['id']->getValue());
        $this->terminate();
    }

    public function onStatusAddSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_statuses()->insertOne($form->getValues());
        adminlog::log(__('Added order status "%s"'), $form['name']->getValue());
        $this->redirect('manageStatuses');
        $this->terminate();
    }

    public function onStatusEditSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_statuses()->updateOne($form->getValues());
        adminlog::log(__('Updated order status "%s"'), $form['name']->getValue());
        $this->redirect('manageStatuses');
        $this->terminate();
    }

    public function onManagePaymentTypesFormEdit(SubmitButton $b)
    {
        $form = $this->getComponent('managePaymentTypesForm');
        $this->redirect('editPaymentType', $form['id']->getValue());
        $this->terminate();
    }

    public function onPaymentTypeAddSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_payment_types()->insertOne($form->getValues());
        adminlog::log(__('Added order payment type "%s"'), $form['name']->getValue());
        $this->redirect('managePaymentTypes');
        $this->terminate();
    }

    public function onPaymentTypeEditSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_payment_types()->updateOne($form->getValues());
        adminlog::log(__('Updated order payment type "%s"'), $form['name']->getValue());
        $this->redirect('managePaymentTypes');
        $this->terminate();
    }

    public function onManageDeliveryTypesFormEdit(SubmitButton $b)
    {
        $form = $this->getComponent('manageDeliveryTypesForm');
        $this->redirect('editDeliveryType', $form['id']->getValue());
        $this->terminate();
    }

    public function onDeliveryTypeAddSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_delivery_types()->insertOne($form->getValues());
        adminlog::log(__('Added order delivery type "%s"'), $form['name']->getValue());
        $this->redirect('manageDeliveryTypes');
        $this->terminate();
    }

    public function onDeliveryTypeEditSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        mapper::order_delivery_types()->updateOne($form->getValues());
        adminlog::log(__('Updated order delivery type "%s"'), $form['name']->getValue());
        $this->redirect('manageDeliveryTypes');
        $this->terminate();
    }

    public function onChangeOrderStatusFormSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        if ($status = mapper::order_statuses()->findById($form['status_id']->getValue())) {
            mapper::orders()->updateOne($form->getValues());
            adminlog::log(__('Changed status of order "%d" to "%s"'), $form['id']->getValue(), $status->getName());
        }
        $this->redirect('this');
        $this->terminate();
    }

    public function onSendMailFormSubmit(Form $form)
    {
        if (!$form->isValid()) {
            return ;
        }

        $active = FALSE;

        try {
            dibi::begin();
            $active = TRUE;
            mapper::order_emails()->insertOne(array(
                'order_id' => $form['order_id']->getValue(),
                'subject' => $form['subject']->getValue(),
                'body' => $form['body']->getValue()
            ));

            $mail = new Mail;
            $mail->setFrom(Environment::expand('%shopName% <%shopEmail%>'))
                ->addTo($form['to']->getValue())
                ->setSubject($form['subject']->getValue())
                ->setBody($form['body']->getValue())
                ->send();

            adminlog::log(__('Sent e-mail to "%s" with subject "%s"'), $form['to']->getValue(), $form['subject']->getValue());

            $this->redirect('this');
            $this->terminate();
            
        } catch (RedirectingException $e) {
            dibi::commit();
            throw $e;

        } catch (Exception $e) {
            if ($active) dibi::rollback();
            $form->addError(__('Cannot send e-mail.'));
        }
    }
}
