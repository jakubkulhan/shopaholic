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

    public function createComponent($name)
    {
        switch ($name) {
            case 'searchForm':
                $form = new AppForm($this, $name);
                $form->addText('q', __('Query:'));
                $form['q']->getControlPrototype()->onclick(
                    'if (this.value === "' . __('Input search keywords') . '") {
                        this._default = this.value;
                        this.value = "";
                    }');
                $form['q']->getControlPrototype()->onblur(
                    'if (this.value === "" && this._default !== undefined) {
                        this.value = this._default;
                    }');
                $form->setAction($this->link('Search:default'));
                $form->setMethod('get');
                $form->setDefaults(array('q' => __('Input search keywords')));
            break;

            default:
                return parent::createComponent($name);
        }
    }
}
