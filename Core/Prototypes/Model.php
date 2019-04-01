<?
namespace Core\Prototypes;

use App\Models\SitePreferenceValue;
use Core\Core;
use Core\Helpers\DBRaw;
use Core\Helpers\DBRes;

abstract class Model {
    protected static $table = "";
    protected static $fields = [];
    protected static $primary = 'id';

    /**
     * @param $id
     * @return array|bool
     * @throws \Exception
     */
    public static function find($id){
        if (!$id)
            return false;

        $sql = sprintf('SELECT %s FROM %s WHERE %s = %s', implode(', ', static::$fields), static::$table, static::$primary, $id);
        return Core::$db->exec($sql)->fetch();
    }

    /**
     * @param array $filter
     * @param array $sort
     * @param $limit
     * @param $cnt
     * @return DBRes
     * @throws \Exception
     */
    public static function getList($filter = [], $sort = [], $limit = 0, $cnt = 0){
        $logic_list = [];
        foreach($filter as $key => $value)
            if(preg_match('/^(~|!|>|<)/i', $key, $ma)){
                unset($filter[$key]);
                $key = preg_replace('/^(~|!|>|<)/i', '', $key);
                $logic_list[$key] = $ma[1];
                $filter[$key] = $value;
            }

        $keys = array_intersect(array_keys($filter), static::$fields);
        $o_keys = array_intersect(array_keys($sort), static::$fields);
        $cnt = intval($cnt);
        $limit = intval($limit);
        $where = [];
        $order = [];

        if(count($keys)) foreach ($keys as $key){
            $logic = '=';
            if(is_array($filter[$key])){
                $logic =  (isset($logic_list[$key]) && $logic_list[$key] == '!') ? 'NOT IN' : 'IN';
                $where[] = sprintf("%s %s ('%s')", $key, $logic, implode("', '", $filter[$key]));
            }else{
                if(isset($logic_list[$key]) && $logic_list[$key] == '~')
                    $logic = 'LIKE';
                elseif(isset($logic_list[$key]))
                    $logic = $logic_list[$key];

                $value_placement = is_numeric($filter[$key]) ? '%s' : "'%s'";
                $where[] = sprintf("%s %s {$value_placement}", $key, $logic, str_replace("'", "\\'",$filter[$key]));
            }
        }

        if($o_keys) foreach ($o_keys as $key)
            if(in_array(strtolower($sort[$key]), ['asc', 'desc']))
                $order[] = sprintf("%s %s", $key, $sort[$key]);

        $sql = sprintf('
            SELECT SQL_CALC_FOUND_ROWS
                %s
            FROM %s
            %s
            %s
            %s',
            implode(', ', static::$fields),
            static::$table,
            count($where) ? "WHERE\n ".implode("\nAND ", $where) : '',
            count($order) ? "ORDER BY\n ".implode(",\n", $order) : '',
            !$cnt ? ($limit > 0 ? "LIMIT {$limit}" : '') : "LIMIT {$limit}, {$cnt}"
        );
        return Core::$db->exec($sql);
    }

    /**
     * @param int $page_size
     * @param array $filter
     * @param array $sort
     * @return array
     * @throws \Exception
     */
    public static function paginate($page_size = 15, $filter = [], $sort = [])
    {
        $page = 1;
        if(array_key_exists('page', $_GET) && (int) $_GET['page'] > 0)
            $page = (int) $_GET['page'];

        $page_size = abs((int) $page_size);
        $page_size = $page_size ?? 15;

        static::getList($filter, $sort);
        $rows = Core::$db->exec('SELECT FOUND_ROWS() cnt')->fetch()['cnt'];
        $pages = ceil($rows / $page_size);

        $page = $page > $pages ? 1 : $page;

        return [
            'list' => static::getList($filter, $sort, ($page - 1) * $page_size, $page_size),
            'page' => $page,
            'pages' => $pages,
            'size' => $page_size
        ];
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function drop($id){
        $sql = sprintf('DELETE FROM %s WHERE %s = %s', static::$table, static::$primary, $id);
        return Core::$db->exec($sql)->a_rows ? true : false;
    }

    /**
     * @param $id
     * @param $fields
     * @return bool
     * @throws \Exception
     */
    public static function update($id, $fields){
        if(!$fields || !$id)
            return false;

        if(array_key_exists(static::$primary, $fields))
            unset($fields[static::$primary]);

        $keys = array_intersect(array_keys($fields), static::$fields);

        if(count($keys)){
            $data = [];
            foreach($keys as $key)
                if (is_numeric($fields[$key]) || $fields[$key] instanceof DBRaw)
                    $data[] = sprintf("%s = %s", $key, $fields[$key]);
                else
                    $data[] = sprintf("%s = '%s'", $key, str_replace("'", "\\'",$fields[$key]));

            $sql = sprintf('UPDATE %s SET %s WHERE %s = %s', static::$table, implode(', ', $data), static::$primary, $id);
            return Core::$db->exec($sql)->a_rows ? true : false;
        }else
            return false;
    }

    /**
     * @param $fields
     * @return bool
     * @throws \Exception
     */
    public static function add($fields){
        if(!$fields)
            return false;

        if(array_key_exists(static::$primary, $fields))
            unset($fields[static::$primary]);

        $keys = array_intersect(array_keys($fields), static::$fields);
        if(count($keys)){
            $data = [];
            foreach($keys as $key){
                if (is_numeric($fields[$key]) || $fields[$key] instanceof DBRaw)
                    $data[] = $fields[$key];
                else
                    $data[] = sprintf('\'%s\'', str_replace("'", "\\'",$fields[$key]));
            }

            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', static::$table, implode(', ', $keys), implode(', ', $data));
            return ($r = Core::$db->exec($sql)) && $r->a_rows ? $r->ins_id : false;
        }else
            return false;
    }

    public static function getFieldsList()
    {
        return self::$fields;
    }
}
