# Company API Documentation

## Overview
The Company API provides CRUD operations for managing companies with many-to-many relationships with brokers.

## Base URL
`/api/v1/companies`

## Endpoints

### 1. Get All Companies
**GET** `/api/v1/companies`

**Query Parameters:**
- `broker_id` (optional): Filter by broker ID
- `status` (optional): Filter by status (published, pending, rejected)
- `search` (optional): Search in name and licence_number
- `sort_by` (optional): Sort field (name, status, year_founded, created_at, updated_at)
- `sort_direction` (optional): asc or desc (default: desc)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number (default: 1)
- `language_code` (optional): Language for translations (default: en)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Example Company",
            "name_p": "Example Company P",
            "licence_number": "LIC123456",
            "status": "published",
            "brokers": [...],
            "translations": [...],
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### 2. Get Company Form Data
**GET** `/api/v1/companies/create`

**Response:**
```json
{
    "success": true,
    "message": "Create form endpoint",
    "data": {
        "brokers": [
            {"id": 1, "name": "Broker 1"},
            {"id": 2, "name": "Broker 2"}
        ],
        "zones": [
            {"id": 1, "name": "Zone 1"},
            {"id": 2, "name": "Zone 2"}
        ],
        "status_options": {
            "published": "Published",
            "pending": "Pending",
            "rejected": "Rejected"
        }
    }
}
```

### 3. Create Company
**POST** `/api/v1/companies`

**Request Body:**
```json
{
    "name": "New Company",
    "name_p": "New Company P",
    "licence_number": "LIC789012",
    "licence_number_p": "LIC789012P",
    "banner": "https://example.com/banner.jpg",
    "banner_p": "https://example.com/banner_p.jpg",
    "description": "Company description",
    "description_p": "Company description P",
    "year_founded": "2020",
    "year_founded_p": "2020",
    "employees": "100-500",
    "employees_p": "100-500",
    "headquarters": "New York, USA",
    "headquarters_p": "New York, USA",
    "offices": "London, Tokyo",
    "offices_p": "London, Tokyo",
    "status": "published",
    "status_reason": null,
    "broker_ids": [1, 2, 3],
    "zone_code": "US"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Company created successfully",
    "data": {
        "id": 1,
        "name": "New Company",
        "brokers": [...],
        "translations": [...],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 4. Get Single Company
**GET** `/api/v1/companies/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Example Company",
        "brokers": [...],
        "translations": [...],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 5. Update Company
**PUT** `/api/v1/companies/{id}`

**Request Body:** (same as create, but all fields are optional)

**Response:**
```json
{
    "success": true,
    "message": "Company updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Company",
        "brokers": [...],
        "translations": [...],
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 6. Delete Company
**DELETE** `/api/v1/companies/{id}`

**Response:**
```json
{
    "success": true,
    "message": "Company deleted successfully"
}
```

## Error Responses

All endpoints return error responses in this format:

```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message"
}
```

## Validation Rules

### Required Fields (for creation):
- `name`: string, max 250 characters

### Optional Fields:
- `name_p`: string, max 250 characters
- `licence_number`: string
- `licence_number_p`: string, max 250 characters
- `banner`: string
- `banner_p`: string
- `description`: string
- `description_p`: string
- `year_founded`: string
- `year_founded_p`: string
- `employees`: string
- `employees_p`: string
- `headquarters`: string, max 1000 characters
- `headquarters_p`: string, max 1000 characters
- `offices`: string, max 1000 characters
- `offices_p`: string, max 1000 characters
- `status`: enum (published, pending, rejected)
- `status_reason`: string, max 1000 characters
- `broker_ids`: array of existing broker IDs
- `zone_code`: string, max 200 characters

## Notes

- The `broker_ids` field handles the many-to-many relationship with brokers
- The `zone_code` field is stored in the pivot table (`broker_company`)
- All text fields support translations through the `_p` suffix
- The API supports pagination and filtering
- Companies can be associated with multiple brokers 