<?php
final class Front_SearchPresenter extends Front_BasePresenter
{
    public function startup()
    {
        parent::startup();
        fulltext::init(FULLTEXT_DIR);
        searchlog::init(SEARCHLOG_DIR);
    }

    public function renderDefault($q, $page = 1)
    {
        try {
            $ids = array();
            foreach (fulltext::index()->find($q) as $hit) {
                $ids[] = $hit->getDocument()->id;
            }

            $this->template->paginator = new Paginator;
            $this->template->paginator->setItemCount(count($ids));
            $this->template->paginator->setItemsPerPage(Environment::getVariable('itemsPerPage', 30));
            $this->template->paginator->setPage($page);

            $this->template->products = mapper::products()->findByIds(array_slice($ids, 
                $this->template->paginator->getOffset(),
                $this->template->paginator->getLength()));

            $this->template->title = __('Search results for ``%s"', $q);
            $this->template->q = $q;

            Environment::getSession(SESSION_SEARCH_NS)->last = $q;

            searchlog::log($q);

        } catch (Exception $e) {
            $this->template->products = array();
        }
    } 
}
