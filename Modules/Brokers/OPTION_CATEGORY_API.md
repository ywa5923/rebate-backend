# Option Category API Documentation

## Overview

The Option Category API provides endpoints for managing option categories in the broker system. Option categories are used to organize broker options into logical groups.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

All endpoints require authentication. Include your API token in the Authorization header:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Endpoints

### 1. List Option Categories

Retrieve a list of option categories with optional filtering, pagination, and sorting.

**Endpoint:** `GET /option-categories`

#### Query Parameters

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `per_page` | integer | No | Number of items per page (default: 15) | `10` |
| `page` | integer | No | Page number (default: 1) | `2` |
| `search` | string | No | Search term for name or description | `trading` |
| `language_code` | string | No | Language code for translations | `en` |
| `sort_by` | string | No | Sort field (name, position, default_language, created_at, updated_at) | `position` |
| `sort_direction` | string | No | Sort direction (asc, desc) | `asc` |

#### Example Request

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories?per_page=10&page=1&search=trading&language_code=en&sort_by=position&sort_direction=asc" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

#### Example Response (Paginated)

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Trading Features",
        "description": "Trading platform features and capabilities",
        "slug": "trading-features",
        "default_language": "en",
        "position": 1,
        "options": [
          {
            "id": 1,
            "slug": "minimum_deposit",
            "name": "Minimum Deposit",
            "data_type": "number",
            "form_type": "input",
            "required": true,
            "placeholder": "Enter minimum deposit",
            "tooltip": "Minimum amount required to open an account",
            "min_constraint": "0",
            "max_constraint": "1000000",
            "meta_data": null
          }
        ]
      },
      {
        "id": 2,
        "name": "Account Types",
        "description": "Different types of trading accounts",
        "slug": "account-types",
        "default_language": "en",
        "position": 2,
        "options": []
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25,
      "from": 1,
      "to": 10
    }
  }
}
```

#### Example Response (Non-paginated)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Trading Features",
      "description": "Trading platform features and capabilities",
      "slug": "trading-features",
      "default_language": "en",
      "position": 1,
      "options": []
    }
  ]
}
```

### 2. Create Option Category

Create a new option category.

**Endpoint:** `POST /option-categories`

#### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `name` | string | Yes | Category name | `"Trading Features"` |
| `description` | string | No | Category description | `"Trading platform features"` |
| `icon` | string | No | Icon class or identifier | `"fas fa-chart-line"` |
| `color` | string | No | Primary color | `"#007bff"` |
| `background_color` | string | No | Background color | `"#f8f9fa"` |
| `border_color` | string | No | Border color | `"#dee2e6"` |
| `text_color` | string | No | Text color | `"#212529"` |
| `font_weight` | string | No | Font weight | `"bold"` |
| `position` | integer | No | Display position | `1` |
| `default_language` | string | No | Default language | `"en"` |

#### Example Request

```bash
curl -X POST "https://your-domain.com/api/v1/option-categories" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Trading Features",
    "description": "Trading platform features and capabilities",
    "icon": "fas fa-chart-line",
    "color": "#007bff",
    "background_color": "#f8f9fa",
    "border_color": "#dee2e6",
    "text_color": "#212529",
    "font_weight": "bold",
    "position": 1,
    "default_language": "en"
  }'
```

#### Example Response

```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Trading Features",
    "description": "Trading platform features and capabilities",
    "slug": "trading-features",
    "default_language": "en",
    "position": 1,
    "options": [],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  },
  "message": "Option category created successfully"
}
```

### 3. Get Option Category

Retrieve a specific option category by ID.

**Endpoint:** `GET /option-categories/{id}`

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Option category ID |

#### Example Request

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

#### Example Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Trading Features",
    "description": "Trading platform features and capabilities",
    "slug": "trading-features",
    "default_language": "en",
    "position": 1,
    "options": [
      {
        "id": 1,
        "slug": "minimum_deposit",
        "name": "Minimum Deposit",
        "data_type": "number",
        "form_type": "input",
        "required": true,
        "placeholder": "Enter minimum deposit",
        "tooltip": "Minimum amount required to open an account",
        "min_constraint": "0",
        "max_constraint": "1000000",
        "meta_data": null
      },
      {
        "id": 2,
        "slug": "maximum_leverage",
        "name": "Maximum Leverage",
        "data_type": "number",
        "form_type": "input",
        "required": true,
        "placeholder": "Enter maximum leverage",
        "tooltip": "Maximum leverage available",
        "min_constraint": "1",
        "max_constraint": "1000",
        "meta_data": null
      }
    ]
  }
}
```

### 4. Update Option Category

Update an existing option category.

**Endpoint:** `PUT /option-categories/{id}`

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Option category ID |

#### Request Body

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `name` | string | No | Category name | `"Updated Trading Features"` |
| `description` | string | No | Category description | `"Updated trading platform features"` |
| `icon` | string | No | Icon class or identifier | `"fas fa-chart-line"` |
| `color` | string | No | Primary color | `"#28a745"` |
| `background_color` | string | No | Background color | `"#f8f9fa"` |
| `border_color` | string | No | Border color | `"#dee2e6"` |
| `text_color` | string | No | Text color | `"#212529"` |
| `font_weight` | string | No | Font weight | `"bold"` |
| `position` | integer | No | Display position | `2` |
| `default_language` | string | No | Default language | `"en"` |

#### Example Request

```bash
curl -X PUT "https://your-domain.com/api/v1/option-categories/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Trading Features",
    "description": "Updated trading platform features and capabilities",
    "color": "#28a745",
    "position": 2
  }'
```

#### Example Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Trading Features",
    "description": "Updated trading platform features and capabilities",
    "slug": "trading-features",
    "default_language": "en",
    "position": 2,
    "options": [
      {
        "id": 1,
        "slug": "minimum_deposit",
        "name": "Minimum Deposit",
        "data_type": "number",
        "form_type": "input",
        "required": true,
        "placeholder": "Enter minimum deposit",
        "tooltip": "Minimum amount required to open an account",
        "min_constraint": "0",
        "max_constraint": "1000000",
        "meta_data": null
      }
    ],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T11:45:00.000000Z"
  },
  "message": "Option category updated successfully"
}
```

### 5. Delete Option Category

Delete an option category.

**Endpoint:** `DELETE /option-categories/{id}`

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Option category ID |

#### Example Request

```bash
curl -X DELETE "https://your-domain.com/api/v1/option-categories/3" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"
```

#### Example Response

```json
{
  "success": true,
  "message": "Option category deleted successfully"
}
```

## Error Responses

### Validation Error (422)

```json
{
  "success": false,
  "message": "The name field is required."
}
```

### Not Found Error (404)

```json
{
  "success": false,
  "message": "Option category not found"
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "Error retrieving option categories: Database connection failed"
}
```

## Data Models

### OptionCategory

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique identifier |
| `name` | string | Category name |
| `description` | string | Category description |
| `slug` | string | URL-friendly identifier |
| `icon` | string | Icon class or identifier |
| `color` | string | Primary color |
| `background_color` | string | Background color |
| `border_color` | string | Border color |
| `text_color` | string | Text color |
| `font_weight` | string | Font weight |
| `position` | integer | Display position |
| `default_language` | string | Default language |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Last update timestamp |
| `options` | array | Related broker options |

### BrokerOption (in options array)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique identifier |
| `slug` | string | Option slug |
| `name` | string | Option name |
| `data_type` | string | Data type (number, string, boolean, etc.) |
| `form_type` | string | Form type (input, select, checkbox, etc.) |
| `required` | boolean | Whether the option is required |
| `placeholder` | string | Input placeholder text |
| `tooltip` | string | Help tooltip text |
| `min_constraint` | string | Minimum value constraint |
| `max_constraint` | string | Maximum value constraint |
| `meta_data` | object | Additional metadata |

## Usage Examples

### 1. Get all option categories with pagination

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories?per_page=5&page=1" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 2. Search for categories containing "trading"

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories?search=trading" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 3. Get categories sorted by position in ascending order

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories?sort_by=position&sort_direction=asc" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 4. Get categories with English translations

```bash
curl -X GET "https://your-domain.com/api/v1/option-categories?language_code=en" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

### 5. Create a new category with minimal data

```bash
curl -X POST "https://your-domain.com/api/v1/option-categories" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Account Features",
    "description": "Account-related features and settings"
  }'
```

### 6. Update only the position of a category

```bash
curl -X PUT "https://your-domain.com/api/v1/option-categories/1" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "position": 3
  }'
```

## Notes

- All timestamps are in ISO 8601 format (UTC)
- The `slug` field is automatically generated from the `name` field
- When deleting a category, ensure it doesn't have any associated broker options
- The `options` array in responses includes all broker options that belong to the category
- Language filtering affects the translation of category names and descriptions
- Position values determine the display order of categories in the UI 