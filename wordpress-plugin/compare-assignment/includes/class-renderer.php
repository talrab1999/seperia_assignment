<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Compare_Assignment_Renderer
{
    private array $limit_options;

    public function __construct(array $limit_options)
    {
        $this->limit_options = $limit_options;
    }

    public function render(array $model): string
    {
        ob_start();
        ?>
        <div class="compare-assignment-plugin">
            <div class="compare-page-shell">
                <header class="compare-page-header">
                    <div>
                        <p class="compare-eyebrow">Seperia EShop</p>
                        <h2>Products Page</h2>
                    </div>
                </header>

                <form class="compare-toolbar" action="<?php echo esc_url(get_permalink()); ?>" method="get">
                    <div class="compare-search-field">
                        <label for="compare-q">Search Bar</label>
                        <input id="compare-q" name="compare_q" type="search" value="<?php echo esc_attr($model['query']); ?>" placeholder="Search Product">
                    </div>
                    <div class="compare-limit-field">
                        <label for="compare-limit">Per page</label>
                        <select id="compare-limit" name="compare_limit">
                            <?php foreach ($this->limit_options as $option) : ?>
                                <option value="<?php echo esc_attr((string) $option); ?>" <?php selected($option, $model['limit']); ?>>
                                    <?php echo esc_html((string) $option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="compare-search-button" type="submit">Search</button>
                    <?php if ($model['query'] !== '') : ?>
                        <a class="compare-clear-link" href="<?php echo esc_url(get_permalink()); ?>">Clear</a>
                    <?php endif; ?>
                </form>

                <?php echo $this->render_status($model); ?>

                <div class="compare-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Description</th>
                                <th scope="col">Price</th>
                                <th scope="col">Rating</th>
                                <th scope="col">Stock</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Category</th>
                                <th scope="col">Thumbnail</th>
                                <th scope="col">Gallery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $this->render_rows($model['products']); ?>
                        </tbody>
                    </table>
                </div>

                <?php echo $this->render_pagination($model); ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    private function render_status(array $model): string
    {
        if ($model['error']) {
            return '<div class="compare-notice compare-error" role="alert">' . esc_html($model['error']) . '</div>';
        }

        if ($model['total'] === 0) {
            if ($model['query'] !== '') {
                return '<p class="compare-result-count">No products match "' . esc_html($model['query']) . '".</p>';
            }
            return '<p class="compare-result-count">No products are available.</p>';
        }

        $noun = $model['total'] === 1 ? 'product' : 'products';
        $query_text = $model['query'] !== '' ? ' for "' . esc_html($model['query']) . '"' : '';

        return sprintf(
            '<p class="compare-result-count">Showing %d-%d of %d %s%s.</p>',
            (int) $model['start_item'],
            (int) $model['end_item'],
            (int) $model['total'],
            esc_html($noun),
            $query_text
        );
    }

    private function render_rows(array $products): string
    {
        if (empty($products)) {
            return '<tr><td class="compare-empty-state" colspan="9">No products found.</td></tr>';
        }

        $html = '';
        foreach ($products as $product) {
            $product_id = compare_assignment_text($product['id'] ?? '');
            $title = compare_assignment_text($product['title'] ?? null);

            $html .= '<tr class="compare-product-row">';
            $html .= '<td class="compare-title-cell">' . esc_html($title) . '</td>';
            $html .= '<td class="compare-description-cell">' . esc_html(compare_assignment_text($product['description'] ?? null)) . '</td>';
            $html .= '<td>' . esc_html(compare_assignment_money($product['price'] ?? null)) . '</td>';
            $html .= '<td>' . esc_html(compare_assignment_rating($product['rating'] ?? null)) . '</td>';
            $html .= '<td>' . esc_html(compare_assignment_text($product['stock'] ?? null)) . '</td>';
            $html .= '<td>' . esc_html(compare_assignment_text($product['brand'] ?? null)) . '</td>';
            $html .= '<td>' . esc_html(compare_assignment_text($product['category'] ?? null)) . '</td>';
            $html .= '<td>' . $this->render_thumbnail($product, $title) . '</td>';
            $html .= '<td><button class="compare-gallery-toggle" type="button" aria-expanded="false" aria-controls="compare-gallery-' . esc_attr($product_id) . '">Gallery</button></td>';
            $html .= '</tr>';
            $html .= $this->render_gallery($product, $product_id, $title);
        }

        return $html;
    }

    private function render_thumbnail(array $product, string $title): string
    {
        $thumbnail = $product['thumbnail'] ?? '';
        if (!compare_assignment_is_http_url($thumbnail)) {
            return '<span class="compare-muted">No image</span>';
        }

        return sprintf(
            '<img class="compare-thumbnail" src="%s" alt="%s thumbnail" loading="lazy">',
            esc_url($thumbnail),
            esc_attr($title)
        );
    }

    private function render_gallery(array $product, string $product_id, string $title): string
    {
        $images = compare_assignment_images($product);

        if (empty($images)) {
            $gallery_content = '<p class="compare-gallery-empty">No gallery images available.</p>';
        } else {
            $gallery_content = '';
            foreach ($images as $index => $image) {
                $gallery_content .= sprintf(
                    '<img src="%s" alt="%s gallery image %d" loading="lazy">',
                    esc_url($image),
                    esc_attr($title),
                    $index + 1
                );
            }
        }

        return sprintf(
            '<tr class="compare-gallery-row" id="compare-gallery-%s" hidden><td colspan="9"><div class="compare-gallery-panel" role="region" aria-label="Gallery for %s">%s</div></td></tr>',
            esc_attr($product_id),
            esc_attr($title),
            $gallery_content
        );
    }

    private function render_pagination(array $model): string
    {
        if ($model['total'] <= 0) {
            return '';
        }

        $current = (int) $model['page'];
        $total_pages = (int) $model['total_pages'];
        $pages = $this->pagination_pages($current, $total_pages);
        $html = '<nav class="compare-pagination" aria-label="Product pages">';

        $html .= $this->page_control('Previous', max(1, $current - 1), $model, $current === 1);
        foreach ($pages as $page) {
            if ($page === '...') {
                $html .= '<span class="compare-page-gap" aria-hidden="true">...</span>';
                continue;
            }

            if ((int) $page === $current) {
                $html .= '<span class="compare-page-control compare-current" aria-current="page">' . esc_html((string) $page) . '</span>';
            } else {
                $html .= $this->page_control((string) $page, (int) $page, $model);
            }
        }
        $html .= $this->page_control('Next', min($total_pages, $current + 1), $model, $current === $total_pages);
        $html .= '</nav>';

        return $html;
    }

    private function pagination_pages(int $current, int $total_pages): array
    {
        if ($total_pages <= 7) {
            return range(1, $total_pages);
        }

        $pages = [1];
        $window_start = max(2, $current - 2);
        $window_end = min($total_pages - 1, $current + 2);

        if ($window_start > 2) {
            $pages[] = '...';
        }
        for ($page = $window_start; $page <= $window_end; $page++) {
            $pages[] = $page;
        }
        if ($window_end < $total_pages - 1) {
            $pages[] = '...';
        }
        $pages[] = $total_pages;

        return $pages;
    }

    private function page_control(string $label, int $page, array $model, bool $disabled = false): string
    {
        if ($disabled) {
            return '<span class="compare-page-control compare-disabled" aria-disabled="true">' . esc_html($label) . '</span>';
        }

        $params = [
            'compare_page' => $page,
            'compare_limit' => (int) $model['limit'],
        ];

        if ($model['query'] !== '') {
            $params['compare_q'] = $model['query'];
        }

        $url = add_query_arg($params, get_permalink());

        return '<a class="compare-page-control" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }
}
