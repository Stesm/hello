<?php

namespace Core\Helpers;

use Core\Core;

class Grid
{
    /** @var null Данные грида */
    private $data = null;

    /** @var int Всего страниц */
    private $pages = 1;

    /** @var int Текущая страница */
    private $page = 1;

    /** @var array Колонки гида */
    private $columns = [];

    /**
     * Grid constructor.
     * @param string $model
     * @param array $filter
     * @param array $sort
     * @param int $page_size
     * @throws \ReflectionException
     */
    public function __construct(string $model, array $filter = null, array $sort = null, int $page_size = 15)
    {
        $func = new \ReflectionMethod("{$model}::paginate");
        $pager_data = $func->invoke(new \ReflectionClass($model), $page_size, (array) $filter, (array) $sort);

        $this->data = $pager_data['list']->absorb();
        $this->pages = $pager_data['pages'];
        $this->page = $pager_data['page'];
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param string $name
     */
    public function setRouteName(string $name)
    {
        $this->route = $name;
    }

    /**
     * @return string
     */
    public function render() : string
    {
        if (!$this->columns)
            $this->columns = array_combine(
                array_keys(current($this->data)),
                array_fill(0, count(current($this->data)),null)
            );

        return Core::view('admin/grid', [
            'rows' => !$this->data ? [] : $this->data,
            'pages' => $this->pages,
            'page' => $this->page,
            'columns' => $this->columns
        ]);
    }
}
