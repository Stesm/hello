<?php
namespace App\Form\Fields;

use Core\Helpers\PageAssets;

class FHTML extends FText
{
    protected $template = 'html';

    protected $rows = 10;

    public function __construct(string $name, $value = null, string $label = '')
    {
        parent::__construct($name, $value, $label);

        PageAssets::pushArray([
            '/admin/plugins/summernote/summernote.css',
            '/admin/plugins/summernote/summernote.js',
            '/admin/plugins/summernote/lang/summernote-ru-RU.min.js'
        ]);
    }
}
