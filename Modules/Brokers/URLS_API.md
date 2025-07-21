# Account Type URLs API Documentation

This document describes the REST API endpoints for managing URLs related to Account Types.

---

## Get URLs for an Account Type (Grouped by Type)

**Endpoint:**
```
GET /api/v1/account-types/{id}/urls
```

**Path Parameters:**
- `id` (integer, required): The ID of the account type.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "mobile": [
      { "id": 1, "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
    ],
    "webplatform": [
      { "id": 2, "url": "https://web.example.com", "name": "Web", "slug": "web" }
    ]
  }
}
```

---

## Add (Create) URLs for an Account Type

**Endpoint:**
```
POST /api/v1/account-types/{id}/urls
```

**Path Parameters:**
- `id` (integer, required): The ID of the account type.

**Request Body (Grouped Example):**
```json
{
  "mobile": [
    { "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
  ],
  "webplatform": [
    { "url": "https://web.example.com", "name": "Web", "slug": "web" }
  ]
}
```

**Request Body (Single Example):**
```json
{
  "url_type": "mobile",
  "url": "https://m.example.com",
  "name": "Mobile",
  "slug": "mobile"
}
```

**Response Example:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "url_type": "mobile", "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" },
    { "id": 2, "url_type": "webplatform", "url": "https://web.example.com", "name": "Web", "slug": "web" }
  ]
}
```

---

## Update URLs for an Account Type

**Endpoint:**
```
PUT /api/v1/account-types/{id}/urls
```

**Path Parameters:**
- `id` (integer, required): The ID of the account type.

**Request Body (Grouped Example):**
```json
{
  "mobile": [
    { "id": 1, "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
  ]
}
```

**Request Body (Single Example):**
```json
{
  "id": 1,
  "url_type": "mobile",
  "url": "https://m.example.com",
  "name": "Mobile",
  "slug": "mobile"
}
```

**Response Example:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "url_type": "mobile", "url": "https://m.example.com", "name": "Mobile", "slug": "mobile" }
  ]
}
```

---

## Error Responses

- If the account type is not found:
```json
{
  "success": false,
  "message": "Account type not found"
}
```

---

## Notes
- You can send either a single URL object or a grouped object by `url_type` for both create and update endpoints.
- All responses return the created or updated URLs as an array.
- URLs are grouped by `url_type` when fetched. 