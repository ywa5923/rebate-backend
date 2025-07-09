# OptionValue API Documentation

## Overview
The OptionValue API provides CRUD operations for managing option values with relationships to brokers, broker options, and zones. It supports both single and bulk operations.

## Base URL
`/api/v1/option-values`

## Endpoints

### 1. Get All Option Values
**GET** `/api/v1/option-values`

**Query Parameters:**
- `broker_id` (optional): Filter by broker ID
- `broker_option_id` (optional): Filter by broker option ID
- `option_slug` (optional): Filter by option slug
- `status` (optional): Filter by status (true/false)
- `search` (optional): Search in value, public_value, and option_slug
- `sort_by` (optional): Sort field (option_slug, value, status, created_at, updated_at)
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
            "option_slug": "minimum_deposit",
            "value": "100",
            "public_value": "$100",
            "status": true,
            "status_message": "Active option",
            "default_loading": true,
            "type": "number",
            "metadata": {"unit": "USD", "currency": "USD"},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_id": 1,
            "broker_option_id": 1,
            "zone_id": 1,
            "broker": {...},
            "option": {...},
            "zone": {...},
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

### 2. Get Option Value Form Data
**GET** `/api/v1/option-values/create`

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
        "broker_options": [
            {"id": 1, "name": "Minimum Deposit", "slug": "minimum_deposit"},
            {"id": 2, "name": "Maximum Leverage", "slug": "max_leverage"}
        ],
        "zones": [
            {"id": 1, "name": "Zone 1"},
            {"id": 2, "name": "Zone 2"}
        ],
        "status_options": {
            "true": "Active",
            "false": "Inactive"
        },
        "type_options": {
            "text": "Text",
            "number": "Number",
            "boolean": "Boolean",
            "select": "Select",
            "multiselect": "Multi Select"
        }
    }
}
```

### 3. Create Single Option Value
**POST** `/api/v1/option-values`

**Request Body:**
```json
{
    "option_slug": "minimum_deposit",
    "value": "100",
    "public_value": "$100",
    "status": true,
    "status_message": "Active option",
    "default_loading": true,
    "type": "number",
    "metadata": {
        "unit": "USD",
        "currency": "USD"
    },
    "is_invariant": true,
    "delete_by_system": false,
    "broker_id": 1,
    "broker_option_id": 1,
    "zone_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Option value created successfully",
    "data": {
        "id": 1,
        "option_slug": "minimum_deposit",
        "value": "100",
        "public_value": "$100",
        "status": true,
        "broker_id": 1,
        "broker_option_id": 1,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 4. Create Multiple Option Values for Broker
**POST** `/api/v1/brokers/{broker_id}/option-values`

**Request Body:**
```json
{
    "option_values": [
        {
            "option_slug": "minimum_deposit",
            "value": "100",
            "public_value": "$100",
            "status": true,
            "broker_option_id": 1
        },
        {
            "option_slug": "maximum_leverage",
            "value": "500",
            "public_value": "1:500",
            "status": true,
            "broker_option_id": 2
        },
        {
            "option_slug": "spread_type",
            "value": "fixed",
            "public_value": "Fixed",
            "status": true,
            "broker_option_id": 3
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Option values created successfully",
    "data": [
        {
            "id": 1,
            "option_slug": "minimum_deposit",
            "value": "100",
            "broker_id": 1,
            "created_at": "2024-01-01T00:00:00.000000Z"
        },
        {
            "id": 2,
            "option_slug": "maximum_leverage",
            "value": "500",
            "broker_id": 1,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### 5. Get Option Value by ID
**GET** `/api/v1/option-values/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "option_slug": "minimum_deposit",
        "value": "100",
        "public_value": "$100",
        "status": true,
        "broker_id": 1,
        "broker_option_id": 1,
        "broker": {...},
        "option": {...},
        "zone": {...},
        "translations": [...],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 6. Get Edit Form Data
**GET** `/api/v1/option-values/{id}/edit`

**Response:**
```json
{
    "success": true,
    "message": "Edit form endpoint",
    "data": {
        "option_value": {
            "id": 1,
            "option_slug": "minimum_deposit",
            "value": "100",
            "broker_id": 1
        },
        "form_data": {
            "brokers": [...],
            "broker_options": [...],
            "zones": [...],
            "status_options": {...},
            "type_options": {...}
        }
    }
}
```

### 7. Update Single Option Value
**PUT** `/api/v1/option-values/{id}`

**Request Body:**
```json
{
    "option_slug": "minimum_deposit",
    "value": "200",
    "public_value": "$200",
    "status": true,
    "status_message": "Updated option",
    "default_loading": true,
    "type": "number",
    "metadata": {
        "unit": "USD",
        "currency": "USD"
    },
    "is_invariant": true,
    "delete_by_system": false,
    "broker_id": 1,
    "broker_option_id": 1,
    "zone_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Option value updated successfully",
    "data": {
        "id": 1,
        "option_slug": "minimum_deposit",
        "value": "200",
        "public_value": "$200",
        "status": true,
        "broker_id": 1,
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 8. Update Multiple Option Values for Broker
**PUT** `/api/v1/brokers/{broker_id}/option-values`

**Request Body:**
```json
{
    "option_values": [
        {
            "id": 1,
            "option_slug": "minimum_deposit",
            "value": "200",
            "public_value": "$200",
            "status": true,
            "broker_option_id": 1
        },
        {
            "id": 2,
            "option_slug": "maximum_leverage",
            "value": "1000",
            "public_value": "1:1000",
            "status": true,
            "broker_option_id": 2
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Option values updated successfully",
    "data": [
        {
            "id": 1,
            "option_slug": "minimum_deposit",
            "value": "200",
            "broker_id": 1,
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        {
            "id": 2,
            "option_slug": "maximum_leverage",
            "value": "1000",
            "broker_id": 1,
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### 9. Delete Option Value
**DELETE** `/api/v1/option-values/{id}`

**Response:**
```json
{
    "success": true,
    "message": "Option value deleted successfully"
}
```

## Sample Data Examples

### Sample POST Data for Single Option Value
```json
{
    "option_slug": "minimum_deposit",
    "value": "100",
    "public_value": "$100",
    "status": true,
    "status_message": "Active option",
    "default_loading": true,
    "type": "number",
    "metadata": {
        "unit": "USD",
        "currency": "USD",
        "min_value": 0,
        "max_value": 10000
    },
    "is_invariant": true,
    "delete_by_system": false,
    "broker_id": 1,
    "broker_option_id": 1,
    "zone_id": 1
}
```

### Sample PUT Data for Single Option Value
```json
{
    "option_slug": "minimum_deposit",
    "value": "200",
    "public_value": "$200",
    "status": true,
    "status_message": "Updated minimum deposit",
    "default_loading": true,
    "type": "number",
    "metadata": {
        "unit": "USD",
        "currency": "USD",
        "min_value": 0,
        "max_value": 20000
    },
    "is_invariant": true,
    "delete_by_system": false,
    "broker_id": 1,
    "broker_option_id": 1,
    "zone_id": 1
}
```

### Sample POST Data for Multiple Option Values
```json
{
    "option_values": [
        {
            "option_slug": "minimum_deposit",
            "value": "100",
            "public_value": "$100",
            "status": true,
            "status_message": "Active minimum deposit",
            "default_loading": true,
            "type": "number",
            "metadata": {"unit": "USD"},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_option_id": 1,
            "zone_id": 1
        },
        {
            "option_slug": "maximum_leverage",
            "value": "500",
            "public_value": "1:500",
            "status": true,
            "status_message": "Active maximum leverage",
            "default_loading": true,
            "type": "number",
            "metadata": {"unit": "ratio"},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_option_id": 2,
            "zone_id": 1
        },
        {
            "option_slug": "spread_type",
            "value": "fixed",
            "public_value": "Fixed Spread",
            "status": true,
            "status_message": "Fixed spread type",
            "default_loading": true,
            "type": "select",
            "metadata": {"options": ["fixed", "variable", "raw"]},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_option_id": 3,
            "zone_id": 1
        }
    ]
}
```

### Sample PUT Data for Multiple Option Values
```json
{
    "option_values": [
        {
            "id": 1,
            "option_slug": "minimum_deposit",
            "value": "200",
            "public_value": "$200",
            "status": true,
            "status_message": "Updated minimum deposit",
            "default_loading": true,
            "type": "number",
            "metadata": {"unit": "USD"},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_option_id": 1,
            "zone_id": 1
        },
        {
            "id": 2,
            "option_slug": "maximum_leverage",
            "value": "1000",
            "public_value": "1:1000",
            "status": true,
            "status_message": "Updated maximum leverage",
            "default_loading": true,
            "type": "number",
            "metadata": {"unit": "ratio"},
            "is_invariant": true,
            "delete_by_system": false,
            "broker_option_id": 2,
            "zone_id": 1
        }
    ]
}
```

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Failed to create option value",
    "error": "The option slug field is required."
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Option value not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Failed to retrieve option values",
    "error": "Database connection error"
}
```

## Notes

1. **Bulk Operations**: The API supports creating and updating multiple option values for a broker in a single request using the `/brokers/{broker_id}/option-values` endpoints.

2. **Relationships**: Option values are linked to brokers, broker options, and zones. The API automatically loads these relationships when requested.

3. **Translations**: Option values support translations through the morphMany relationship with the Translation model.

4. **Metadata**: The metadata field accepts JSON objects for storing additional configuration data.

5. **Validation**: All endpoints include comprehensive validation for required fields and data types.

6. **Filtering**: The index endpoint supports filtering by broker_id, broker_option_id, option_slug, status, and zone_code.

7. **Search**: Full-text search is available across value, public_value, and option_slug fields.

8. **Sorting**: Results can be sorted by option_slug, value, status, created_at, and updated_at fields. 