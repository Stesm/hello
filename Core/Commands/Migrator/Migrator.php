<?
namespace Core\Commands\Migrator;
use Core\Core;
use Core\Helpers\DBRes;

/**
 * Пакет для работы с миграциями
 * @package Core\Commands\Migrator
 */
class Migrator {

    private $table  = 'migrations';
    private $dir    = '/Core/Migrations/';

    public function __construct()
    {
        $this->init();
    }

    private function init(){
        if(!$this->check_install())
            $this->install();

        $path = APP_ROOT.$this->dir;
        if(!is_dir($path))
            if(!mkdir($path, 0777, true))
                echo "\033[1;31mCan't create migrations dir.\033[0m\n";
    }

    private function check_install(){
        $sql = "
            SELECT
                count(*) as cnt
            FROM
                `information_schema`.`TABLES`
            WHERE
                `TABLE_NAME` = '{$this->table}'
                AND `TABLE_SCHEMA` = '".Core::getConfig('DB')['connect']['db']."'";
        return boolval(Core::$db->exec($sql)->fetch()['cnt']);
    }

    private function install(){
        Core::$db->exec("
            CREATE TABLE {$this->table} (
                id INT(5) PRIMARY KEY AUTO_INCREMENT,
                filename VARCHAR(255),
                stage INT(5),
                date_migrate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Применяет миграции
     * @throws \Exception
     */
    public function up(){
        $already = $this->getIsset();
        $stage = ($r = Core::$db->exec("SELECT stage + 1 m FROM {$this->table} ORDER BY stage DESC LIMIT 1")->fetch()) ? $r['m'] : 0;
        $already = array_keys($already);
        $path = APP_ROOT.$this->dir;

        if(is_dir($path)){
            $list = scandir($path);
            foreach($list as $k => $file)
                if(!$this->check_file($file))
                    unset($list[$k]);

            usort($list, function($a, $b){
                $a = $this->parse_name($a);
                $b = $this->parse_name($b);

                return $a['date'] <=> $b['date'];
            });

            foreach($list as $file){
                if(in_array($file, $already, 1) || !$this->check_file($file))
                    continue;

                $migration = $this->parse_name($file);
                require_once "{$path}{$file}";

                $object = new \ReflectionClass("\\Core\\Migrations\\".$migration['name']);

                if($object && $object->hasMethod('migrate')){
                    $object->getMethod('migrate')->invoke($object->newInstance());
                    Migration::add([
                        'filename' => $file,
                        'stage' => $stage
                    ]);
                }
                echo "\033[1;32m{$file}\033[0m ... successful.\n";
            }
        }else
            echo "\033[1;31mCan't detect migrations dir.\033[0m\n";
    }

    /**
     * Откатывает миграции
     * @throws \Exception
     */
    public function down(){
        $stage = ($m = Migration::getList([], ['stage' => 'desc'],1)->fetch()) ? $m['stage'] : 0;
        $files = $this->getByStage($stage);
        $path = APP_ROOT.$this->dir;

        if(!is_dir($path)){
            echo "\033[1;31mCan't detect migrations dir.\033[0m\n";
            return;
        }

        if($files)
            foreach($files as $file){
                require_once "{$path}{$file['filename']}";

                $migration = $this->parse_name($file['filename']);
                $object = new \ReflectionClass("\\Core\\Migrations\\".$migration['name']);

                if($object->hasMethod('rollback')){
                    $object->getMethod('rollback')->invoke($object->newInstance());
                    Migration::drop($file['id']);
                }

                echo "\033[1;32m{$file['filename']}\033[0m ... rolled back.\n";
            }
        else
            echo "\033[1;32mNothing to rollback.\033[0m\n";
    }



    /**
     * @param $name
     * @return bool
     */
    private function check_file($name) : bool
    {
        if(in_array($name, ['.', '..']))
            return false;

        if(!preg_match('/[0-9]{8,14}_[a-z0-9_]+\.php/i', $name))
            return false;

        return true;
    }

    /**
     * @param $name
     * @return array
     */
    private function parse_name($name) : array
    {
        if(!preg_match('/([0-9]{8,14})_([a-z0-9_]+?)\.php/i', $name, $m))
            return [];
        else
            return [
                'name' => $m[2],
                'date' => sprintf("%'0-14s", $m[1])
            ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getIsset(){
        return (array) Migration::getList()->absorb(DBRes::ASSOC_KEY_FORCE, 'filename');
    }

    /**
     * @param $stage
     * @return array
     * @throws \Exception
     */
    private function getByStage($stage){
        return (array) Migration::getList(['stage' => $stage], ['id' => 'desc'])->absorb(DBRes::ASSOC_KEY_FORCE, 'filename');
    }
}
