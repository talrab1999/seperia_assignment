# E-Commerce Product Shop

FastAPI web application for an e-commerce product shop. It fetches products from DummyJSON, renders a paginated product table, supports searching, and uses JavaScript for the row gallery interaction.

WordPress plugin version is included under `wordpress-plugin/compare-assignment`.

## Requirements

- FastAPI, HTTPX, Uvicorn, and Jinja2, which are installed from `requirements.txt`

## Installation

Clone or download the project, then run it from the project directory:

```bash
python -m pip install -r requirements.txt
python -m uvicorn web_app.backend.main:app --reload
```

Open http://localhost:8000/

You can choose a different host or port:

```bash
python -m uvicorn web_app.backend.main:app --host 127.0.0.1 --port 8080
```

To close the app simply press ctrl+c in the terminal

## Project Structure

```text
web_app/
  backend/
    catalog.py          Product catalog page route
    main.py             FastAPI app setup
    models.py           Pydantic models
    product_service.py  Async DummyJSON fetching and product loading
    utils.py            DummyJSON URL and pagination helpers
  frontend/
    static/
      styles.css        CSS styling file
      gallery.js        JS file for controlling the gallery button
    templates/
      index.html        HTML template used by the backend renderer
wordpress-plugin/
  compare-assignment/
    compare-assignment.php
    includes/
      class-plugin.php
      class-products-api.php
      class-renderer.php
      helpers.php
    assets/
      compare-assignment.css
      compare-assignment.js
```

## How It Works

- FastAPI app setup and static file mounting in `web_app/backend/main.py`.
- The product catalog page route in `web_app/backend/catalog.py`.
- Async DummyJSON API requests in `web_app/backend/product_service.py`.
- Pydantic models in `web_app/backend/models.py`.
- Jinja2 renders the HTML template through FastAPI's `Jinja2Templates`.
- The HTML table includes title, description, price, rating, stock, brand, category, thumbnail, and a gallery action.
- The JavaScript in `web_app/frontend/static/gallery.js` only toggles the gallery row.

## WordPress Plugin

The bonus plugin lives in:

```text
wordpress-plugin/compare-assignment
```

To use it:

1. Copy the `compare-assignment` folder into `wp-content/plugins/`.
2. Activate **Compare Assignment** from the WordPress admin plugins screen.
3. On activation, the plugin creates a published page titled **Compare Assignment**.
4. That page contains the `[compare_assignment]` shortcode, which renders the search bar, product table, gallery buttons, and pagination.

The main `compare-assignment.php` file is the WordPress entry point. The plugin logic is split into `includes/`: plugin hooks and activation, DummyJSON fetching, rendering, and shared helpers.

## Author
Built by Tal Rabinovich
