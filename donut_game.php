<?php
/*
Plugin Name: Donut Mini Game
Description: A WordPress plugin to display a mini game.
Version: 1.0
Author: Dmitry Guzeev
*/

namespace Donut_Game;

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'inc/class_svg_obj.php';

class DonutMiniGame
{
    /**
     * Конструктор для класса DonutMiniGame.
     *
     * Регистрирует действия и хуки, включая добавление мини игры в контент,
     * отправку результатов игры, очистку результатов игры и создание таблицы результатов при активации плагина.
     * Также определяет функцию для создания выпадающего списка страниц и постов если они не существуют.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('the_content', [$this, 'display_mini_game_before_content']);
        add_action('wp_ajax_nopriv_submit_game_results', [$this, 'submit_game_results']);
        add_action('wp_ajax_submit_game_results', [$this, 'submit_game_results']);
        add_action('wp_ajax_clear_game_results', [$this, 'clear_game_results']);
        add_action('wp_ajax_nopriv_clear_game_results', [$this, 'clear_game_results']);
        register_activation_hook(__FILE__, [$this, 'create_results_table']);

        if (!function_exists('wp_dropdown_posts')) {
            function wp_dropdown_posts($args = '') {
                $defaults = array(
                    'post_type'        => 'post',
                    'selected'         => 0,
                    'name'             => 'post_id',
                    'id'               => '',
                    'show_option_none' => esc_html__('None', 'donut_game'),
                    'option_none_value'=> '0',
                    'echo'             => 1,
                );

                $r = wp_parse_args($args, $defaults);
                $posts = get_posts(array('post_type' => $r['post_type'], 'numberposts' => -1));

                $output = "<select name='" . esc_attr($r['name']) . "' id='" . esc_attr($r['id']) . "'>";
                if ($r['show_option_none']) {
                    $output .= "<option value='" . esc_attr($r['option_none_value']) . "'>" . esc_html($r['show_option_none']) . "</option>";
                }

                foreach ($posts as $post) {
                    $output .= "<option value='" . esc_attr($post->ID) . "' " . selected($r['selected'], $post->ID, false) . ">" . esc_html($post->post_title) . "</option>";
                }

                $output .= "</select>";

                if ($r['echo']) {
                    echo $output;
                } else {
                    return $output;
                }
            }
        }
    }

    /**
     * Добавляет страницу настроек мини-игры в меню админки.
     *
     * @return void
     */
    public function add_admin_menu()
    {
        add_options_page(esc_html__('Mini Game Settings', 'donut_game'), esc_html__('Mini Game', 'donut_game'), 'manage_options', 'mini-game', [$this, 'settings_page']);
    }

    /**
     * Регистрирует настройки мини-игры.
     *
     * Эта функция регистрирует настройки 'mini_game_page_id' и 'mini_game_post_id'.
     * Настройки принадлежат группе 'mini-game-settings-group'.
     *
     * @return void
     */
    public function register_settings()
    {
        register_setting('mini-game-settings-group', 'mini_game_page_id');
        register_setting('mini-game-settings-group', 'mini_game_post_id');
    }

    /**
     * Отображает страницу настроек мини-игры.
     *
     * Эта функция генерирует HTML для страницы настроек мини-игры.
     * Она включает форму для выбора страницы и поста для отображения мини-игры.
     * Выбранное значение берется из настройки WordPress с помощью функции `get_option`.
     *
     * @return void
     */
    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?= esc_html__('Mini Game Settings', 'donut_game'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('mini-game-settings-group'); ?>
                <?php do_settings_sections('mini-game-settings-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?= esc_html__('Select page to display Mini Game', 'donut_game'); ?></th>
                        <td>
                            <?php
                            wp_dropdown_pages([
                                'selected' => get_option('mini_game_page_id'),
                                'name' => 'mini_game_page_id',
                                'show_option_none' => esc_html__('None', 'donut_game'),
                                'option_none_value' => '0',
                            ]);
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?= esc_html__('Select post to display Mini Game', 'donut_game'); ?></th>
                        <td>
                            <?php
                            $post_id = get_option('mini_game_post_id');
                            wp_dropdown_posts([
                                'selected' => $post_id,
                                'name' => 'mini_game_post_id',
                                'show_option_none' => esc_html__('None', 'donut_game'),
                                'option_none_value' => '0',
                            ]);
                            ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Подключает необходимые стили и скрипты для мини-игры.
     *
     * Эта функция подключает необходимые стили и скрипты для мини-игры.
     * Также эта функция локализирует JavaScript-файл, добавляя переводы для элементов интерфейса мини-игры.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('mini-game-style', plugin_dir_url(__FILE__) . 'css/style.css');
        wp_enqueue_script('mini-game-app', plugin_dir_url(__FILE__) . 'js/app.js', [], false, true);
        wp_enqueue_script('mini-game-script', plugin_dir_url(__FILE__) . 'js/script.js', ['jquery'], false, true);
        wp_localize_script('mini-game-script', 'miniGameAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'i18n' => [
                'gameStart' => esc_html__('Start Game', 'donut_game'),
                'gameResult' => esc_html__('Game Result', 'donut_game'),
                'yourResult' => esc_html__('Your result', 'donut_game'),
                'retry' => esc_html__('Retry', 'donut_game'),
                'go' => esc_html__('Go', 'donut_game'),
                'noResults' => esc_html__('No results yet', 'donut_game'),
                'rockPosition' => esc_html__('Rock Position', 'donut_game'),
                'runTime' => esc_html__('Run Time', 'donut_game'),
                'jumpDistance' => esc_html__('Jump Distance', 'donut_game'),
                'rockSize' => esc_html__('Rock Size', 'donut_game'),
                'runResult' => esc_html__('Run Result', 'donut_game'),
                'success' => esc_html__('Success', 'donut_game'),
                'failure' => esc_html__('Failure', 'donut_game'),
                'confirmClearResults' => esc_html__('Are you sure you want to clear all results?', 'donut_game'),
            ],
        ]);
    }

    /**
     * Выводит мини-игру перед содержимым страницы или поста.
     *
     * @param string $content Отображаемый контент страницы или поста.
     * @return string Контент страницы или поста с добавленным блоком мини-игры.
     */
    public function display_mini_game_before_content($content)
    {
        $page_id = get_option('mini_game_page_id');
        $post_id = get_option('mini_game_post_id');

        if ((is_page($page_id) || is_single($post_id)) && (get_the_ID() == $page_id || get_the_ID() == $post_id)) {
            $game_html = $this->get_mini_game_html();
            return $game_html . $content;
        }

        return $content;
    }

    /**
     * Возвращает HTML для мини-игры и таблицы результатов.
     *
     * Эта функция генерирует HTML-содержимое мини-игры, включая игровый контейнер,
     * игровый приложение, аннотацию, SVG-изображение пончика, SVG камня, SVG флага,
     * и кнопку начала игры. Также функция принимает результаты из таблицы результатов
     * и отображает их в виде таблицы. Если таблица результатов пуста, функция отображает
     * сообщение о том, что таблица пуста. Функция возвращает полученный HTML как строку.
     *
     * @return string HTML для мини-игры и таблицы результатов.
     */
    private function get_mini_game_html()
    {
        ob_start();
        ?>
        <div class="game-container" style="margin:0!important;margin-left:calc(-1* var(--wp--style--root--padding-right))!important;">
            <div id="app">
                <div class="annotation">
                    <div class="annotation-wrapper">Hey!</div>
                </div>
                <?= SVG_Obj::get_svg_file('donut') ?>
                <img class="rock" src="<?php echo plugin_dir_url(__FILE__); ?>images/rock.png" alt="rock">
                <?= SVG_Obj::get_svg_file('flag') ?>
                <div id="game-overlay">
                    <div class="game-start">
                        <button class="btn start-game" id="start-game"><?= esc_html__('Start Game', 'donut_game') ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php

        global $wpdb;
        $results_table = $wpdb->prefix . 'mini_game_results';
        $results = $wpdb->get_results("SELECT * FROM $results_table ORDER BY id DESC");

        echo '<div id="result-table-container">';
        if ($results) {
            echo '<table id="result-table">';
            echo '<thead><tr><th>'. esc_html__('Rock Position', 'donut_game') .'</th><th>'. esc_html__('Run Time', 'donut_game') .'</th><th>'. esc_html__('Jump Distance', 'donut_game').'</th><th>'. esc_html__('Rock Size', 'donut_game') .'</th><th>'. esc_html__('Run Result', 'donut_game').'</th></tr></thead>';
            echo '<tbody>';
            foreach ($results as $result) {
                echo '<tr>';
                echo '<td>' . esc_html($result->rock_position) . '</td>';
                echo '<td>' . esc_html($result->run_time) . '</td>';
                echo '<td>' . esc_html($result->jump_distance) . '</td>';
                echo '<td>' . esc_html($result->rock_size) . '</td>';
                echo '<td>' . esc_html($result->run_result) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>'. __('No results yet', 'donut_game') .'</p>';
        }
        echo '</div>';
        echo '<div class="container btn-container"><button class="btn clear-results" id="clear-results">'. esc_html__('Clear Results', 'donut_game') .'</button></div>';

        return ob_get_clean();
    }

    /**
     * Отправляет результаты игры в базу данных и возвращает JSON-ответ.
     *
     * @global \wpdb $wpdb Объект базы данных WordPress.
     * @return void
     */
    public function submit_game_results() {
        global $wpdb;
        $results_table = $wpdb->prefix . 'mini_game_results';

        $data = array(
            'rock_position' => intval($_POST['rock_position']),
            'run_time' => floatval($_POST['run_time']),
            'jump_distance' => intval($_POST['jump_distance']),
            'rock_size' => intval($_POST['rock_size']),
            'run_result' => sanitize_text_field($_POST['run_result']),
        );

        $wpdb->insert($results_table, $data);

        if ($wpdb->insert_id) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Очищает все результаты игры в базе данных.
     *
     * @global \wpdb $wpdb Объект базы данных WordPress.
     * @return void
     */
    public function clear_game_results()
    {
        global $wpdb;
        $results_table = $wpdb->prefix . 'mini_game_results';
        $wpdb->query("TRUNCATE TABLE $results_table");

        if ($wpdb->last_error) {
            wp_send_json_error();
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Создаёт таблицу 'mini_game_results' в базе данных WordPress если она не существует.
     *
     * @global \wpdb $wpdb Объект базы данных WordPress.
     * @return void
     */
    public function create_results_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mini_game_results';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                rock_position int NOT NULL,
                run_time float NOT NULL,
                jump_distance int NOT NULL,
                rock_size int NOT NULL,
                run_result varchar(10) NOT NULL,
                run_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}

new DonutMiniGame();
?>
