from typing import Optional

from pydantic import BaseModel, Field


class Product(BaseModel):
    id: int
    title: str
    description: str
    price: float
    rating: Optional[float] = None
    stock: Optional[int] = None
    brand: Optional[str] = None
    category: Optional[str] = None
    thumbnail: Optional[str] = None
    images: list[str] = Field(default_factory=list)


class ProductPage(BaseModel):
    products: list[Product]
    total: int
    page: int
    limit: int
    total_pages: int
    start_item: int
    end_item: int
    query: str
    api_url: str
    error: str | None = None
