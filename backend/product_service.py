from typing import Any

import httpx

from backend.models import Product, ProductPage
from backend.utils import build_products_url, calculate_pagination


class ProductServiceError(RuntimeError):
    """DummyJSON cannot be reached or returns unexpected data."""


async def fetch_json(url: str) -> dict[str, Any]:
    try:
        async with httpx.AsyncClient(timeout=10.0) as client:
            response = await client.get(url)
            response.raise_for_status()
            data = response.json()
    except httpx.HTTPStatusError as exc:
        raise ProductServiceError(f"DummyJSON returned HTTP {exc.response.status_code}.") from exc
    except httpx.RequestError as exc:
        raise ProductServiceError(f"Could not reach DummyJSON: {exc}") from exc
    except ValueError as exc:
        raise ProductServiceError("DummyJSON returned invalid JSON.") from exc

    if not isinstance(data, dict):
        raise ProductServiceError("DummyJSON returned an unexpected response.")

    return data


async def fetch_products(query: str, page: int, limit: int) -> ProductPage:
    url = build_products_url(query=query, page=page, limit=limit)
    data = await fetch_json(url)

    products_data = data.get("products", [])
    if not isinstance(products_data, list):
        raise ProductServiceError("Product data was not a list.")

    products = [Product(**product) for product in products_data]
    total = data.get("total", len(products))
    pagination = calculate_pagination(total=total, page=page, limit=limit, product_count=len(products))

    if total > 0 and page > pagination["total_pages"]:
        page = pagination["total_pages"]
        url = build_products_url(query=query, page=page, limit=limit)
        data = await fetch_json(url)
        products_data = data.get("products", [])
        products = [Product(**product) for product in products_data]
        total = data.get("total", total)
        pagination = calculate_pagination(total=total, page=page, limit=limit, product_count=len(products))

    return ProductPage(
        products=products,
        total=total,
        page=page,
        limit=limit,
        total_pages=pagination["total_pages"],
        start_item=pagination["start_item"],
        end_item=pagination["end_item"],
        query=query,
        api_url=url,
    )
