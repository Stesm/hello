<?php
namespace Core\Commands;

/**
 * Пакет для создания компонентов системы
 * @package Core\Commands\Generator
 */
class Generator
{
    /**
     * Создает контроллер.
     * Параметры: ControllerName
     * @param $class_name
     * @throws \Exception
     */
    public static function controller($class_name)
    {
        $path = APP_ROOT.'/App/Controllers';
        $namespace = 'App\Controllers';

        if(preg_match("/(\\/|\\\\)/si", $class_name, $ma)){
            $class = explode($ma[1], $class_name);
            $class_name = current(array_splice($class, -1));
            $path .= '/'.implode('/', $class);
            $namespace .= '\\'.implode('\\', $class);
        }

        $path .= "/{$class_name}.php";

        if(is_file($path))
            throw new \Exception("File already exists.");

        if(!$fp = fopen($path, 'w'))
            throw new \Exception("Can't get access to Controllers dir.");

        fwrite($fp,
<<<DOC
<?
namespace {$namespace};

use Core\Core;
use Core\Prototypes\Controller;

/**
 * Class $class_name
 * @package App\Controllers{$namespace}
 */
class {$class_name} extends Controller {

    /**
     * @return string
     */
    public static function index()
    {
        return Core::view('index', []);
    }
}
DOC
        );
        fclose($fp);

        $rel_path = str_replace(APP_ROOT.'/', '', $path);
        echo "\n{$rel_path} -\033[1;32m successfully created!\033[0m\n";
        exec('git add '.str_replace(APP_ROOT.'/', '', $path),$null, $status);

        if ($status === 0)
            echo "{$rel_path} -\033[1;32m successfully added to git!\033[0m\n\n";
        else
            echo "{$rel_path} -\033[1;31m adding to git failed.\033[0m\n\n";
    }

    /**
     * Создает модель.
     * Параметры: ModelName table_name field1 field2 ...
     * @param $class_name
     * @param string $table_name
     * @param array $fields
     * @throws \Exception
     */
    public static function model($class_name, $table_name, ...$fields)
    {
        $path = APP_ROOT.'/App/Models';
        $namespace = '';

        if(preg_match("/(\\/|\\\\)/si", $class_name, $ma)){
            $class = explode($ma[1], $class_name);
            $class_name = current(array_splice($class, -1));
            $path .= '/'.implode('/', $class);
            $namespace = '\\'.implode('\\', $class);
        }

        $path .= "/{$class_name}.php";
        $table_name = is_string($table_name) ? $table_name : '';
        $fields = is_array($fields) ? implode("', '", array_filter($fields)) : '';


        if(is_file($path))
            throw new \Exception("File already exists.");

        if(!$fp = fopen($path, 'w'))
            throw new \Exception("Can't get access to models dir.");

        fwrite($fp,
<<<DOC
<?
namespace App\Models$namespace;

use Core\Prototypes\Model;

/**
 * Class $class_name
 * @package App\Models$namespace
 */
class $class_name extends Model {
    protected static \$table = '$table_name';
    protected static \$fields = ['$fields'];
}
DOC
        );
        fclose($fp);

        $rel_path = str_replace(APP_ROOT.'/', '', $path);
        echo "\n{$rel_path} -\033[1;32m successfully created!\033[0m\n";
        exec('git add '.str_replace(APP_ROOT.'/', '', $path),$null, $status);

        if ($status === 0)
            echo "{$rel_path} -\033[1;32m successfully added to git!\033[0m\n\n";
        else
            echo "{$rel_path} -\033[1;31m adding to git failed.\033[0m\n\n";
    }

    /**
     * Создает миграцию.
     * Параметры: MigrationName
     * @param $class_name
     * @throws \Exception
     */
    public static function migration($class_name)
    {
        $dir = '/Core/Migrations';
        $path = APP_ROOT.$dir;

        foreach (scandir($path) as $file)
            if (preg_match('/([0-9]{8,14})_([a-z0-9_]+?)\.php/i',$file, $matches)) {
                if($matches[2] == $class_name)
                    throw new \Exception("Class already exists.");
            }

        $file_name = date('YmdHis').'_'.$class_name.'.php';
        $fp = fopen($path."/".$file_name, 'w');
        if(!$fp)
            throw new \Exception("Can't get access to migrations dir.");

        fwrite($fp,
<<<DOC
<?
namespace Core\Migrations;

use Core\Core;
use Core\Commands\Migrator\Prototype as Migration;

class {$class_name} extends Migration {

    /**
     * @throws \Exception
     */
    public function migrate(){
//        Core::\$db->exec("
//            SOME SQL
//        ");
    }

    /**
     * @throws \Exception
     */
    public function rollback(){
//        Core::\$db->exec("
//            SOME SQL
//        ");
    }
}
DOC
        );
        fclose($fp);

        echo "\n{$file_name} -\033[1;32m successfully created!\033[0m\n";
        exec('git add '.trim($dir,'/').'/'.$file_name,$null, $status);

        if ($status === 0)
            echo "{$file_name} -\033[1;32m successfully added to git!\033[0m\n\n";
        else
            echo "{$file_name} -\033[1;31m adding to git failed.\033[0m\n\n";
    }
}
