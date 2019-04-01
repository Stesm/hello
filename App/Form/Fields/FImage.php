<?php
namespace App\Form\Fields;

use Core\Core;
use Core\Prototypes\FormField;

class FImage extends FormField
{
    protected $template = 'image';

    protected $subdir = '';

    /**
     * FImage constructor.
     * @param string $name
     * @param null $value
     * @param string $label
     * @param string $subdir
     */
    public function __construct(string $name, $value = null, string $label = '', string $subdir = '')
    {
        if (!preg_match('/[^a-z0-9\/_-]/is', $subdir))
            $this->subdir = $subdir;

        parent::__construct($name, $value, $label);
    }

    /**
     * @return mixed
     */
    public function getSubmitted()
    {
        return Core::$request->getFile($this->name, null);
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function saveAndGetPath()
    {
        if(
            ($src = $this->getSubmitted()) &&
            !$src['error'] &&
            $src['size'] > 0
        ){
            $path = Core::getConfig('PUBLIC_PATH', '');
            if(!$path)
                return '';

            $info = pathinfo($src['name']);
            $img_path = Core::getConfig('IMAGE_PATH', 'img');
            $subdir = trim($this->subdir, '/');
            $name = md5_file($src['tmp_name']);
            $ext = $info['extension'];

            $path = "{$path}/{$img_path}/{$subdir}/{$name}.{$ext}";

            if (!is_dir(dirname($path)))
                if (!mkdir(dirname($path),0777,true))
                    throw new \Exception('Cant move file to: '.$path);

            if(!move_uploaded_file($src['tmp_name'], $path))
                throw new \Exception('Cant move file to: '.$path);

            return str_replace(Core::getConfig('PUBLIC_PATH'),'',$path);
        }

        return '';
    }
}
