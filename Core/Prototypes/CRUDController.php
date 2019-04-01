<?
namespace Core\Prototypes;

use Core\Core;
use Core\Helpers\Form;
use Core\Helpers\Grid;
use Core\Helpers\Route;

/**
 * Class CRUDController
 * @package Core\Prototypes
 */
abstract class CRUDController extends Controller {

    /** @var null|Model */
    protected $model = null;

    /** @var null|Form */
    protected $form = null;

    /** @var null|string */
    protected $route = null;

    /** @var string */
    protected $title = '';

    /** @var array  */
    protected $filter = [];

    /** @var array  */
    protected $order_by = [];

    /** @var int  */
    protected $page_size = 20;

    protected $edit_acton = 'edit';
    protected $create_acton = 'create';
    protected $delete_acton = 'delete';

    /** @var null|Form */
    private $form_object = null;

    /**
     * @param null $action
     * @param null $id
     * @return string
     * @throws \Exception
     */
    public function index($action = null, $id = null)
    {
        if ($action){
            if (!method_exists($this, $action))
                throw new \Exception('Action '.$action.' not exists');

            if ($id)
                return $this->{$action}($id);
            else
                return $this->{$action}();
        }

        $grid = new Grid($this->model, $this->filter, $this->order_by, $this->page_size);

        $grid->setRouteName($this->route);

        if ($this->getColumns()){
            $columns = $this->getColumns();
            $columns[] = [
                'label' => '',
                'column-class' => 'actions',
                'callback' => function($row) {
                    $delete = Route::buildUri($this->route, ['action' => $this->delete_acton, 'id' => $row['id']]);
                    $edit = Route::buildUri($this->route, ['action' => $this->edit_acton, 'id' => $row['id']]);

                    return
                        "<div style='text-align: right'>".
                            "<a href='{$delete}' class='btn btn-danger btn-sm fa fa-times'></a>".
                            "<a href='{$edit}' class='btn btn-primary btn-sm fa fa-pencil'></a>".
                        "</div>";
                }
            ];

            $grid->setColumns($columns);
        }

        return Core::view('admin/crud.index', [
            'grid' => $grid,
            'create_link' => Route::buildUri($this->route, ['action' => 'create']),
            'title' => $this->title
        ]);
    }

    /**
     * @param int|null $id
     * @return string
     * @throws \Exception
     */
    protected function edit(int $id = null)
    {
        $form = $this->getForm($id);
        if ($form->submit()) {
            Core::$route->redirect(Route::buildUri($this->route));
        }

        return Core::view('admin/crud.form', [
            'form' => $this->getForm($id),
            'title' => $this->title,
            'action' => 'Изменение'
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function create()
    {
        $form = $this->getForm();
        if ($form->submit()) {
            Core::$route->redirect(Route::buildUri($this->route));
        }

        return Core::view('admin/crud.form', [
            'form' => $this->getForm(),
            'title' => $this->title,
            'action' => 'Создание'
        ]);
    }

    /**
     * @return array
     */
    protected function getColumns() : array
    {
        return [];
    }

    /**
     * @param int $object_id
     * @return Form
     * @throws \Exception
     */
    protected function getForm(int $object_id = null) : Form
    {
        if (!$this->form_object){
            /** @var Form $form */
            $this->form_object = new $this->form();

            if ($object_id){
                $object = $this->model::find($object_id);
                $this->form_object->loadValues($object ?? []);
            }
        }

        return $this->form_object;
    }
}
