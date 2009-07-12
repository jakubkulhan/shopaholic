<?php
final class Back_DashboardPresenter extends Back_BasePresenter
{
    public function renderDefault()
    {
        $this->template->categories_count = mapper::categories()->countAll();
        $this->template->manufacturers_count = mapper::manufacturers()->countAll();
        $this->template->pages_count = mapper::pages()->countNotRef();
        $this->template->products_count = mapper::products()->countAll();
        $this->template->pictures_count = mapper::pictures()->countAll();
        $this->template->orders_count = mapper::orders()->countAll();
        $this->template->orders_initial_count = mapper::orders()->countWithInitialStatus();
    }
}
