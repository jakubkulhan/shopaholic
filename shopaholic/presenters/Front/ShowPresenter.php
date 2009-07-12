<?php
final class Front_ShowPresenter extends Front_BasePresenter
{
    public static $expandCommandsFns = array();

    public function actionDefault($path = '', $page_number = 0)
    {
        // get nice name
        $path = explode('/', rtrim($path, '/'));
        $last = array_pop($path);
        $rest = implode('/', $path);

        // what's with rest?
        if (!empty($rest)) {
            $this->redirect(301, 'this', $last);
            $this->terminate();
            return ;
        }

        // default page?
        if (empty($last)) {
            $this->setView('default');
            $this->template->page = NULL;
            return ;
        }

        $this->template->path = $last;

        // try to find it in database
        $page = mapper::pages()->findByNiceName($last);
        $this->template->page = $page;
        if ($page === NULL) {
            $this->setView('notfound');
            Environment::getHttpResponse()->setCode(404);
        } else {
            if ($page->getRef() === NULL) {
                $this->setView('page');
            } else {
                $this->setView(get_class($page->getRef()));
                $this->template->{get_class($page->getRef())} = $page->getRef();
            }

            $this->template->title = $page->getName();
        }

        $this->template->page_number = $page_number;
    }

    public function renderDefault()
    {
        $this->template->registerHelper('expand_commands', array(__CLASS__, 'expandCommands'));
        self::$expandCommandsFns = array(
            'products' => array($this, 'listProducts'),
            'random_products' => array($this, 'listRandomProducts')
        );
    }

    public function renderCategory()
    {
        $this->template->paginator = new Paginator;
        $this->template->paginator->setItemCount(
            mapper::products()->countByCategory($this->template->category));
        $this->template->paginator->setItemsPerPage(
            Environment::getVariable('itemsPerPage', 30));
        $this->template->paginator->setPage($this->template->page_number);

        $this->template->products = mapper::products()
            ->findByCategory($this->template->category,
                $this->template->paginator->getLength(),
                $this->template->paginator->getOffset());
        $this->template->subcategories = mapper::categories()
            ->findSubcategories($this->template->category);
    }

    public function renderManufacturer()
    {
        $this->template->paginator = new Paginator;
        $this->template->paginator->setItemCount(
            mapper::products()->countByManufacturer($this->template->manufacturer));
        $this->template->paginator->setItemsPerPage(
            Environment::getVariable('itemsPerPage', 30));
        $this->template->paginator->setPage($this->template->page_number);

        $this->template->products = mapper::products()
            ->findByManufacturer($this->template->manufacturer,
                $this->template->paginator->getLength(),
                $this->template->paginator->getOffset());
    }

    public function renderProduct()
    {
        $this->template->nav = mapper::categories()
            ->findForNavByProductId($this->template->product->getId());
    }

    public static function expandCommands($text)
    {
        return preg_replace_callback('~\{([A-Za-z0-9_]+)\s*(.*?)\s*\}~',
            array(__CLASS__, 'expand'), $text);
    }

    public static function expand($_)
    {
        list(, $fn, $params) = $_;
        $params = preg_split('~\s*,\s*~', $params);

        if (isset(self::$expandCommandsFns[$fn])) {
            return call_user_func_array(self::$expandCommandsFns[$fn], $params);
        }

        return $_[0];
    }

    public function listProducts($codes)
    {
        if (!is_array($codes)) {
            $codes = func_get_args();
        }

        $template = clone $this->template;
        $template->setFile(Environment::expand('%templatesDir%/FrontModule/@products.phtml'));
        $template->presenter = $this;
        $template->control = $this;
        $template->products = mapper::products()->findByCodes($codes);
        return "/---html\n" . $template->__toString() . "\n\\---\n";
    }

    public function listRandomProducts($count)
    {
        $count = intval($count);

        $template = clone $this->template;
        $template->setFile(Environment::expand('%templatesDir%/FrontModule/@products.phtml'));
        $template->presenter = $this;
        $template->control = $this;
        $template->products = mapper::products()->findRandom($count);
        return "/---html\n" . $template->__toString() . "\n\\---\n";
    }
}
