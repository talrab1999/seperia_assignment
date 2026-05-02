<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Compare_Assignment_Plugin
{
    private const PAGE_TITLE = 'Compare Assignment';
    private const PAGE_SLUG = 'compare-assignment';
    private const PAGE_ID_OPTION = 'compare_assignment_page_id';
    private const SHORTCODE = 'compare_assignment';
    private const DEFAULT_LIMIT = 10;
    private const LIMIT_OPTIONS = [5, 10, 20, 30];

    public static function init(): void
    {
        add_shortcode(self::SHORTCODE, [self::class, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [self::class, 'register_assets']);
    }

    public static function activate(): void
    {
        $existing_page_id = (int) get_option(self::PAGE_ID_OPTION);
        $existing_page = $existing_page_id > 0 ? get_post($existing_page_id) : null;

        if ($existing_page && $existing_page->post_status !== 'trash') {
            self::ensure_shortcode_on_page($existing_page_id);
            return;
        }

        $page = get_page_by_path(self::PAGE_SLUG, OBJECT, 'page');

        if ($page && $page->post_status !== 'trash') {
            update_option(self::PAGE_ID_OPTION, (int) $page->ID);
            self::ensure_shortcode_on_page((int) $page->ID);
            return;
        }

        $page_id = wp_insert_post([
            'post_title' => self::PAGE_TITLE,
            'post_name' => self::PAGE_SLUG,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => '[' . self::SHORTCODE . ']',
        ]);

        if (!is_wp_error($page_id)) {
            update_option(self::PAGE_ID_OPTION, (int) $page_id);
        }
    }

    public static function register_assets(): void
    {
        wp_register_style(
            'compare-assignment',
            COMPARE_ASSIGNMENT_PLUGIN_URL . 'assets/compare-assignment.css',
            [],
            COMPARE_ASSIGNMENT_VERSION
        );

        wp_register_script(
            'compare-assignment',
            COMPARE_ASSIGNMENT_PLUGIN_URL . 'assets/compare-assignment.js',
            [],
            COMPARE_ASSIGNMENT_VERSION,
            true
        );
    }

    public static function render_shortcode(): string
    {
        wp_enqueue_style('compare-assignment');
        wp_enqueue_script('compare-assignment');

        $params = self::request_params();
        $products_api = new Compare_Assignment_Products_Api();
        $renderer = new Compare_Assignment_Renderer(self::LIMIT_OPTIONS);
        $model = $products_api->load_products($params['query'], $params['page'], $params['limit']);

        return $renderer->render($model);
    }

    private static function ensure_shortcode_on_page(int $page_id): void
    {
        $page = get_post($page_id);

        if (!$page || has_shortcode($page->post_content, self::SHORTCODE)) {
            update_option(self::PAGE_ID_OPTION, $page_id);
            return;
        }

        wp_update_post([
            'ID' => $page_id,
            'post_content' => trim($page->post_content) . "\n\n[" . self::SHORTCODE . ']',
        ]);
        update_option(self::PAGE_ID_OPTION, $page_id);
    }

    private static function request_params(): array
    {
        $query = isset($_GET['compare_q'])
            ? sanitize_text_field(wp_unslash($_GET['compare_q']))
            : '';
        $query = preg_replace('/\s+/', ' ', trim($query)) ?? '';
        $query = substr($query, 0, 80);

        $page = isset($_GET['compare_page']) ? absint($_GET['compare_page']) : 1;
        $page = max(1, $page);

        $limit = isset($_GET['compare_limit']) ? absint($_GET['compare_limit']) : self::DEFAULT_LIMIT;
        if (!in_array($limit, self::LIMIT_OPTIONS, true)) {
            $limit = self::DEFAULT_LIMIT;
        }

        return [
            'query' => $query,
            'page' => $page,
            'limit' => $limit,
        ];
    }
}
