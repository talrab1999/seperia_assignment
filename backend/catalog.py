from pathlib import Path
from urllib.parse import urlencode

from fastapi import APIRouter, Query, Request
from fastapi.responses import HTMLResponse
from fastapi.templating import Jinja2Templates

from backend.models import ProductPage
from backend.product_service import ProductServiceError, fetch_products
from backend.utils import DEFAULT_LIMIT, LIMIT_OPTIONS, build_page_numbers, calculate_pagination


PROJECT_ROOT = Path(__file__).resolve().parents[1]
TEMPLATE_DIR = PROJECT_ROOT / "frontend" / "templates"

router = APIRouter()
templates = Jinja2Templates(directory=TEMPLATE_DIR)


def page_href(query: str, page: int, limit: int) -> str:
    params = {"page": page, "limit": limit}
    if query:
        params["q"] = query
    return f"/?{urlencode(params)}"


templates.env.globals.update(page_href=page_href)


@router.get("/", response_class=HTMLResponse)
async def catalog(
    request: Request,
    q: str = Query(default="", max_length=80),
    page: int = Query(default=1, ge=1),
    limit: int = Query(default=DEFAULT_LIMIT, ge=1, le=100),
) -> HTMLResponse:
    query = q.strip()

    try:
        model = await fetch_products(query=query, page=page, limit=limit)
    except ProductServiceError as exc:
        pagination = calculate_pagination(total=0, page=page, limit=limit, product_count=0)
        model = ProductPage(
            products=[],
            total=0,
            page=page,
            limit=limit,
            total_pages=pagination["total_pages"],
            start_item=pagination["start_item"],
            end_item=pagination["end_item"],
            query=query,
            api_url="",
            error=str(exc),
        )

    return templates.TemplateResponse(
        request,
        "index.html",
        {
            "request": request,
            "limit_options": LIMIT_OPTIONS,
            "model": model,
            "pages": build_page_numbers(model.page, model.total_pages),
        },
    )
