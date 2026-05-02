<?php

if (!defined('ABSPATH')) {
    exit;
}

function compare_assignment_text($value): string
{
    if ($value === null) {
        return '-';
    }

    $text = trim((string) $value);
    return $text !== '' ? $text : '-';
}

function compare_assignment_money($value): string
{
    return is_numeric($value) ? '$' . number_format((float) $value, 2) : '-';
}

function compare_assignment_rating($value): string
{
    return is_numeric($value) ? number_format((float) $value, 2) : '-';
}

function compare_assignment_images(array $product): array
{
    $images = $product['images'] ?? [];
    if (!is_array($images)) {
        return [];
    }

    $valid_images = [];
    foreach ($images as $image) {
        if (compare_assignment_is_http_url($image)) {
            $valid_images[] = $image;
        }

        if (count($valid_images) === 3) {
            break;
        }
    }

    return $valid_images;
}

function compare_assignment_is_http_url($value): bool
{
    return is_string($value) && preg_match('/^https?:\/\//i', $value) === 1;
}
