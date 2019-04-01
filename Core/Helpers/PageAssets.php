<?
namespace Core\Helpers;

use Core\Core;

/**
 * Class PageAssets
 * @package Helpers
 */
class PageAssets {

    /** @var string $CSS */
    public const CSS = 'css';

    /** @var string $JS */
    public const JS = 'js';

    /**
     * @var array
     */
    private static $JS_assets = [];

    /**
     * @var array
     */
    private static $CSS_assets = [];

    /**
     * @var array
     */
    private static $accepted_ext = ['css', 'js'];

    /**
     * @param $file_path
     */
    public static function push($file_path) : void
    {
        $info = pathinfo($file_path);
        if (!array_key_exists('extension', $info) || !in_array($info['extension'], self::$accepted_ext))
            return;

        $abs_path = preg_replace('/\/{2,}/', '/', PUBLIC_ROOT.'/'.$file_path);
        if (!file_exists($abs_path) && !preg_match('/^https?:\/\//is', $file_path))
            return;

        $file_path = !preg_match('/^https?:\/\//is', $file_path) ? preg_replace('/\/{2,}/', '/', '/'.$file_path) : $file_path;
        $stack = strtoupper($info['extension']).'_assets';

        if(!in_array($file_path, self::$$stack))
            array_push(self::$$stack, $file_path);
    }

    public static function pushNoExtensionFile($file_path, $type) : void
    {
        if (!in_array($type, [self::CSS, self::JS]))
            return;

        if ($type == self::CSS)
            array_push(self::$CSS_assets, $file_path);
        else
            array_push(self::$JS_assets, $file_path);
    }

    /**
     * @param array $files
     */
    public static function pushArray(array $files) : void
    {
        foreach ($files as $file)
            self::push($file);
    }

    public static function showJS() : void
    {
        if (Core::getConfig('COMPRESS_STATIC'))
            self::mergeJS();

        foreach (self::$JS_assets as $file){
            if (!preg_match('/^https?:\/\//is', $file)) {
                ?><script type="text/javascript" defer="defer" src="<?=$file?>?<?=AntiCache::getFileTime($file)?>"></script><?
            } else {
                ?><script type="text/javascript" defer="defer" src="<?=$file?>"></script><?
            }
        }
    }

    public static function showCSS() : void
    {
        if (Core::getConfig('COMPRESS_STATIC'))
            self::mergeCSS();

        foreach (self::$CSS_assets as $file){
            if (!preg_match('/^https?:\/\//is', $file)) {
                ?><link rel="stylesheet" type="text/css" href="<?=$file?>?<?=AntiCache::getFileTime($file)?>"><?
            } else {
                ?><link rel="stylesheet" type="text/css" href="<?=$file?>"><?
            }
        }
    }

    public static function clearJS() : void
    {
        self::$JS_assets = [];
    }

    public static function clearCSS() : void
    {
        self::$CSS_assets = [];
    }

    /**
     * @return array
     */
    public static function getJSList() : array
    {
        return self::$JS_assets;
    }

    /**
     * @return array
     */
    public static function getCSSList() : array
    {
        return self::$CSS_assets;
    }

    /**
     * Добвляет ресурс к странице из любого места, копируя его а публичное asset хранилище
     *
     * @param string $path Путь до оригинального файла
     * @param string $file_name Имя файла без расширения, оно будет скопировано с оригинального файла
     */
    public static function asset(string $path, string $file_name = '') : void
    {
        if ($asset_path = self::assetFile($path, $file_name))
            self::push($asset_path);
    }

    /**
     * Мержит текущий стэк CSS в 1 файл
     */
    private static function mergeCSS()
    {
        $root = PUBLIC_ROOT.'/';
        $data_hash = array_map(function ($path) use ($root) {
            if (preg_match('/^https?:\/\//is', $path))
                return false;

            return md5_file($root.trim($path, '/'));
        }, self::$CSS_assets);
        $data_hash = md5(implode('-', array_filter($data_hash)));

        $path = $root.'assets';
        if (!is_dir($path))
            if (!mkdir($path, 0777, true))
                return;

        $asset = "{$path}/{$data_hash}.css";
        if (!file_exists($asset)) {
            $fp = fopen($asset, 'wb+');

            foreach (self::$CSS_assets as $_asset) {
                if (preg_match('/^https?:\/\//is', $_asset))
                    continue;

                $content = file_get_contents($root.trim($_asset, '/'));
                preg_match_all('/(\.\.\/[a-z0-9]+.+?)(\'|")/is', $content, $matches);
                if ($matches[1]) {
                    $abs_path = dirname(dirname($_asset));
                    $mapping = array_combine(
                        $matches[1],
                        array_map(function ($item) use ($abs_path) {
                            return str_replace('..', $abs_path, $item);
                        },$matches[1])
                    );

                    $content = strtr($content, $mapping);
                }

                fwrite($fp, $content);
            }

            fclose($fp);
        }

        self::$CSS_assets = array_filter(self::$CSS_assets, function ($item) {
            return preg_match('/^https?:\/\//is', $item);
        });
        self::$CSS_assets = array_merge(self::$CSS_assets,["/assets/{$data_hash}.css"]);
    }

    /**
     * Мержит текущий стэк CSS в 1 файл
     */
    private static function mergeJS()
    {
        $root = PUBLIC_ROOT.'/';
        $data_hash = array_map(function ($path) use ($root) {
            if (preg_match('/^https?:\/\//is', $path))
                return false;

            return md5_file($root.trim($path, '/'));
        }, self::$JS_assets);
        $data_hash = md5(implode('-', array_filter($data_hash)));

        $path = $root.'assets';
        if (!is_dir($path))
            if (!mkdir($path, 0777, true))
                return;

        $asset = "{$path}/{$data_hash}.js";
        if (!file_exists($asset)) {
            $fp = fopen($asset, 'wb+');

            foreach (self::$JS_assets as $_asset) {
                if (preg_match('/^https?:\/\//is', $_asset))
                    continue;

                $content = file_get_contents($root.trim($_asset, '/'));
                fwrite($fp, "// ======> {$_asset}\n");
                fwrite($fp, "{$content}\n");
            }

            fclose($fp);
        }

        self::$JS_assets = array_filter(self::$JS_assets, function ($item) {
            return preg_match('/^https?:\/\//is', $item);
        });
        self::$JS_assets = array_merge(self::$JS_assets,["/assets/{$data_hash}.js"]);
    }


    /**
     * Копирует ресурс в публичную часть и возвращает путь до него
     *
     * @param string $path Путь до оригинального файла
     * @param string $file_name Имя файла без расширения, оно будет скопировано с оригинального файла
     * @return string
     */
    public static function assetFile(string $path, string $file_name = '') : string
    {
        if (!file_exists($path))
            return '';

        $info = pathinfo($path);
        if($file_name){
            $file_name = pathinfo($file_name);

            if (
                array_key_exists('dirname', $file_name) &&
                $file_name['dirname'] != './'
            )
                $file_name['dirname'] = "{$file_name['dirname']}/";
            else
                $file_name['dirname'] = '';
        }

        if (!array_key_exists('extension', $info) || !in_array($info['extension'], self::$accepted_ext))
            return '';

        $asset_path = PUBLIC_ROOT.'/assets/'.(!$file_name ? basename($path) : "{$file_name['dirname']}{$file_name['basename']}.{$info['extension']}");
        $asset_path = preg_replace('/\/{2,}/', '/', $asset_path);

        if (!file_exists($asset_path) || (md5_file($path) != md5_file($asset_path))){
            if (!is_dir(dirname($asset_path)))
                mkdir(dirname($asset_path), 0777, true);

            copy($path, $asset_path);
        }

        return str_replace(PUBLIC_ROOT, '', $asset_path);
    }
}
