<?php
final class Back_SystemPresenter extends Back_BasePresenter
{
    public function renderSearchStats()
    {
        $this->template->title = __('Search statistics');
        searchlog::init(SEARCHLOG_DIR);
        $this->template->stats = searchlog::stats();
    }
}
