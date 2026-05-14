<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Compare_Assignment_Products_Api
{
    private const API_BASE_URL = 'https://dummyjson.com/products';
    private const PRODUCT_FIELDS = 'id,title,description,price,rating,stock,brand,category,thumbnail,images';
    private const REQUEST_TIMEOUT = 10;

    public function load_products(string $query, int $page, int $limit): array
    {
        $url = $this->build_api_url($query, $page, $limit);
        $result = $this->fetch_json($url);

        if ($result['error']) {
            return $this->product_model([], 0, $page, $limit, $query, $url, $result['error']);
        }

        $products = isset($result['data']['products']) && is_array($result['data']['products'])
            ? $result['data']['products']
            : [];
        $total = isset($result['data']['total']) ? (int) $result['data']['total'] : 0;
        $total_pages = $total > 0 ? max(1, (int) ceil($total / $limit)) : 1;

        if ($total > 0 && $page > $total_pages) {
            $page = $total_pages;
            $url = $this->build_api_url($query, $page, $limit);
            $result = $this->fetch_json($url);

            if ($result['error']) {
                return $this->product_model([], 0, $page, $limit, $query, $url, $result['error']);
            }

            $products = isset($result['data']['products']) && is_array($result['data']['products'])
                ? $result['data']['products']
                : [];
            $total = isset($result['data']['total']) ? (int) $result['data']['total'] : $total;
        }

        return $this->product_model($products, $total, $page, $limit, $query, $url);
    }

    private function build_api_url(string $query, int $page, int $limit): string
    {
        $endpoint = $query !== '' ? self::API_BASE_URL . '/search' : self::API_BASE_URL;
        $params = [
            'limit' => $limit,
            'skip' => (max(1, $page) - 1) * $limit,
            'select' => self::PRODUCT_FIELDS,
        ];

        if ($query !== '') {
            $params['q'] = $query;
        }

        return add_query_arg($params, $endpoint);
    }

    private function fetch_json(string $url): array
    {
        $response = wp_remote_get($url, [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'e-commerce-product-shop/1.0',
            ],
        ]);

        if (is_wp_error($response)) {
            return [
                'data' => null,
                'error' => 'Could not reach DummyJSON: ' . $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return [
                'data' => null,
                'error' => 'DummyJSON returned HTTP ' . $code . '.',
            ];
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($decoded)) {
            return [
                'data' => null,
                'error' => 'DummyJSON returned invalid JSON.',
            ];
        }

        return [
            'data' => $decoded,
            'error' => null,
        ];
    }

    private function product_model(
        array $products,
        int $total,
        int $page,
        int $limit,
        string $query,
        string $api_url,
        ?string $error = null
    ): array {
        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'query' => $query,
            'api_url' => $api_url,
            'error' => $error,
            'total_pages' => $total > 0 ? max(1, (int) ceil($total / $limit)) : 1,
            'start_item' => empty($products) ? 0 : (($page - 1) * $limit) + 1,
            'end_item' => min($total, (($page - 1) * $limit) + count($products)),
        ];
    }
}
