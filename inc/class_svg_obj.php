<?php
namespace Donut_Game;

class SVG_Obj
{

    private static $svg_file_path = [
        'donut' => 'svg/donut.svg',
        'flag' => 'svg/flag.svg',
    ];

    /**
     * Возвращает содержимое SVG-файла
     *
     * @param string $file_name Имя SVG-файла, принимает название в виде строки:
     * - donut - SVG пончика
     * - flag - SVG флага
     * 
     * @return string Содержимое SVG-файла.
     */
    public static function get_svg_file($file_name)
    {
        $file_path = plugin_dir_path(__FILE__) . self::$svg_file_path[$file_name];
        ob_start();
        include $file_path;
        $file_content = ob_get_clean();
        return $file_content;
    }

}
