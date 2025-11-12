---
outline: deep
---

# Stickle API Endpoints

Stickle provides a comprehensive REST API for tracking events, retrieving analytics data, and managing segments. All endpoints are prefixed with `/stickle/api` by default (configurable via `stickle.routes.api.prefix`).

## Authentication & Middleware

API endpoints use the middleware configuration defined in `stickle.routes.api.middleware` (defaults to `['api']`). Configure authentication middleware as needed for your application.

## Endpoints Overview

| Endpoint                         | Method | Description                                    |
| -------------------------------- | ------ | ---------------------------------------------- |
| `/track`                         | POST   | Ingest tracking events and page views          |
| `/models`                        | GET    | List models with StickleEntity trait           |
| `/models-statistics`             | GET    | Get aggregate statistics for model attributes  |
| `/segments`                      | GET    | List available segments                        |
| `/segment-models`                | GET    | List models belonging to a specific segment    |
| `/segment-statistics`            | GET    | Get time-series statistics for segment         |
| `/model-relationship`            | GET    | Get related models via relationship            |
| `/model-relationship-statistics` | GET    | Get statistics for model relationships         |
| `/model-attribute-audit`         | GET    | Get audit history for model attribute changes  |

---

## Event Tracking

### POST `/track`

Ingests tracking events and page views for analytics processing.

**Request Body:**
```json
{
  "payload": [
    {
      "type": "track|page",
      "model": "User",
      "object_uid": "123",
      "name": "clicked:button",
      "url": "https://example.com/page",
      "data": {},
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ]
}
```

**Parameters:**
- `payload` (required, array) - Array of tracking events
- `payload.*.type` (required, string) - Event type: `track` or `page`
- `payload.*.model` (optional, string) - Model class name (auto-detected from authenticated user)
- `payload.*.object_uid` (optional, string) - Object ID (auto-detected from authenticated user)
- `payload.*.name` (required for track, string) - Event name (alphanumeric with dashes/underscores)
- `payload.*.url` (required for page, string) - Page URL
- `payload.*.data` (optional, array) - Additional event data
- `payload.*.timestamp` (optional, date) - Event timestamp (defaults to current time)

**Response:**
```
HTTP 204 No Content
```

**Example - Track Button Click:**
```bash
curl -X POST /stickle/api/track \
  -H "Content-Type: application/json" \
  -d '{
    "payload": [
      {
        "type": "track",
        "name": "clicked:purchase_button",
        "data": {
          "product_id": "123",
          "price": 29.99
        }
      }
    ]
  }'
```

**Example - Track Page View:**
```bash
curl -X POST /stickle/api/track \
  -H "Content-Type: application/json" \
  -d '{
    "payload": [
      {
        "type": "page",
        "url": "https://example.com/products/123"
      }
    ]
  }'
```

---

## Models

### GET `/models`

Lists models that use the StickleEntity trait with optional search and filtering.

**Query Parameters:**
- `model_class` (required, string) - Model class name to query
- `uid` (optional, string) - Specific model ID to retrieve
- `search` (optional, string) - Search term to filter by name
- `per_page` (optional, integer) - Results per page (default: 25)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "current_page": 1,
  "per_page": 25,
  "total": 100
}
```

**Example - List Users:**
```bash
curl "/stickle/api/models?model_class=user&search=john&per_page=10"
```

**Example - Get Specific User:**
```bash
curl "/stickle/api/models?model_class=user&uid=123"
```

### GET `/models-statistics`

Returns aggregate statistics (avg, min, max, sum, count) for a specific model attribute across all instances.

**Query Parameters:**
- `model_class` (required, string) - Model class name
- `attribute` (required, string) - Attribute name to aggregate

**Response:**
```json
[
  {
    "value_avg": 85.5,
    "value_min": 10.0,
    "value_max": 100.0,
    "value_sum": 1710.0,
    "value_count": 20
  }
]
```

**Example - User Score Statistics:**
```bash
curl "/stickle/api/models-statistics?model_class=user&attribute=score"
```

---

## Segments

### GET `/segments`

Lists all available segments with metadata and optional filtering by model class.

**Query Parameters:**
- `model_class` (optional, string) - Filter segments by model class
- `per_page` (optional, integer) - Results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "as_class": "HighValueCustomers",
      "model_class": "User",
      "name": "High Value Customers",
      "description": "Users with total purchases over $1000",
      "export_interval": "daily",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 5
}
```

**Example - List All Segments:**
```bash
curl "/stickle/api/segments"
```

**Example - List User Segments:**
```bash
curl "/stickle/api/segments?model_class=User"
```

### GET `/segment-models`

Lists all models that belong to a specific segment.

**Query Parameters:**
- `segment_id` (required, integer) - Segment ID
- `per_page` (optional, integer) - Results per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "total_purchases": 1250.00,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 150
}
```

**Example - High Value Customers:**
```bash
curl "/stickle/api/segment-models?segment_id=1&per_page=20"
```

### GET `/segment-statistics`

Returns time-series statistics for a segment attribute with delta calculations over a specified period.

**Query Parameters:**
- `segment_id` (required, integer) - Segment ID
- `attribute` (required, string) - Attribute to analyze
- `date_from` (optional, date) - Start date (default: 30 days ago)
- `date_to` (optional, date) - End date (default: now)

**Response:**
```json
{
  "time_series": [
    {
      "timestamp": "2024-01-15T00:00:00Z",
      "value": 85.5
    },
    {
      "timestamp": "2024-01-16T00:00:00Z", 
      "value": 87.2
    }
  ],
  "delta": {
    "start_value": 85.5,
    "end_value": 92.1,
    "absolute_change": 6.6,
    "percentage_change": 7.72,
    "start_date": "2024-01-15T00:00:00Z",
    "end_date": "2024-02-15T00:00:00Z"
  },
  "period": {
    "start": "2024-01-15T00:00:00Z",
    "end": "2024-02-15T00:00:00Z"
  }
}
```

**Example - Segment Revenue Trends:**
```bash
curl "/stickle/api/segment-statistics?segment_id=1&attribute=revenue&date_from=2024-01-01&date_to=2024-01-31"
```

---

## Model Relationships

### GET `/model-relationship`

Returns related models via a specified Eloquent relationship.

**Query Parameters:**
- `model_class` (required, string) - Model class name
- `object_uid` (required, string) - Model instance ID
- `relationship` (required, string) - Relationship method name
- `per_page` (optional, integer) - Results per page (default: 25)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "product_name": "Premium Widget",
      "price": 99.99,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "current_page": 1,
  "per_page": 25,
  "total": 15
}
```

**Example - User's Orders:**
```bash
curl "/stickle/api/model-relationship?model_class=user&object_uid=123&relationship=orders"
```

### GET `/model-relationship-statistics`

Returns time-series statistics for a specific model's relationship attribute with delta calculations.

**Query Parameters:**
- `model_class` (required, string) - Model class name
- `uid` (required, string) - Model instance ID
- `relationship` (required, string) - Relationship method name
- `attribute` (required, string) - Attribute to analyze

**Response:**
```json
{
  "value": 250.00,
  "time_series": [
    {
      "timestamp": "2024-01-15T00:00:00Z",
      "value": 200.00
    },
    {
      "timestamp": "2024-01-16T00:00:00Z",
      "value": 250.00
    }
  ],
  "delta": {
    "start_value": 200.00,
    "end_value": 250.00,
    "absolute_change": 50.00,
    "percentage_change": 25.00,
    "start_date": "2024-01-15T00:00:00Z",
    "end_date": "2024-02-15T00:00:00Z"
  },
  "period": {
    "start": "2024-01-16T00:00:00Z",
    "end": "2024-02-16T00:00:00Z"
  }
}
```

**Example - User's Order Value Trends:**
```bash
curl "/stickle/api/model-relationship-statistics?model_class=user&uid=123&relationship=orders&attribute=total_amount"
```

---

## Model Attribute Auditing

### GET `/model-attribute-audit`

Returns audit history for a specific model attribute showing how values changed over time.

**Query Parameters:**
- `model_class` (required, string) - Model class name
- `uid` (required, string) - Model instance ID
- `attribute` (required, string) - Attribute name to audit

**Response:**
```json
{
  "time_series": [
    {
      "timestamp": "2024-01-15T10:30:00Z",
      "value": 85.5
    },
    {
      "timestamp": "2024-01-20T14:20:00Z",
      "value": 92.1
    }
  ],
  "delta": {
    "start_value": 85.5,
    "end_value": 92.1,
    "absolute_change": 6.6,
    "percentage_change": 7.72,
    "start_date": "2024-01-15T10:30:00Z",
    "end_date": "2024-01-20T14:20:00Z"
  },
  "period": {
    "start": "2024-01-16T00:00:00Z",
    "end": "2024-02-16T00:00:00Z"
  }
}
```

**Example - User Score History:**
```bash
curl "/stickle/api/model-attribute-audit?model_class=user&uid=123&attribute=score"
```

---

## Error Responses

All endpoints return consistent error responses:

**404 Not Found:**
```json
{
  "error": "Model not found"
}
```

**400 Bad Request:**
```json
{
  "error": "Model does not use StickleEntity trait"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "model_class": ["The model class field is required."]
  }
}
```

## Configuration

Endpoints can be customized via configuration:

```php
// config/stickle.php
'routes' => [
    'api' => [
        'prefix' => 'stickle/api',      // URL prefix
        'middleware' => ['api', 'auth'], // Middleware stack
    ],
],
```
