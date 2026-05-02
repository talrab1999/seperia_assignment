import math
from typing import Any
from urllib.parse import urlencode


API_BASE_URL = "https://dummyjson.com/products"
PRODUCT_FIELDS = "id,title,description,price,rating,stock,brand,category,thumbnail,images"
DEFAULT_LIMIT = 10
LIMIT_OPTIONS = (5, 10, 20, 30)


def build_products_url(query: str, page: int, limit: int) -> str:
    skip = (max(1, page) - 1) * limit
    params: dict[str, Any] = {
        "limit": limit,
        "skip": skip,
        "select": PRODUCT_FIELDS,
    }

    if query:
        params["q"] = query
        endpoint = f"{API_BASE_URL}/search"
    else:
        endpoint = API_BASE_URL

    return f"{endpoint}?{urlencode(params)}"


def build_page_numbers(current: int, total_pages: int) -> list[int | str]:
    if total_pages <= 5:
        return list(range(1, total_pages + 1))

    pages: list[int | str] = [1]
    window_start = max(2, current - 2)
    window_end = min(total_pages - 1, current + 2)

    if window_start > 2:
        pages.append("...")
    pages.extend(range(window_start, window_end + 1))
    if window_end < total_pages - 1:
        pages.append("...")
    pages.append(total_pages)

    return pages


def calculate_pagination(total: int, page: int, limit: int, product_count: int) -> dict[str, int]:
    total_pages = math.ceil(total / limit) if total > 0 else 1
    return {
        "total_pages": total_pages,
        "start_item": ((page - 1) * limit) + 1 if product_count else 0,
        "end_item": min(total, ((page - 1) * limit) + product_count),
    }
