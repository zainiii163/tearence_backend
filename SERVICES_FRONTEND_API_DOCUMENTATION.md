# Services Frontend Form — Backend Field Reference

This document maps the **frontend service create/edit form** to the **Filament admin panel** and **API** so both use the same fields.

## Form schema endpoint

```
GET /api/v1/services/form-schema
```

Returns machine-readable field definitions (sections, types, options, validation hints).

## Categories

```
GET /api/v1/services/categories
```

Response item shape:

| Field | Type | Notes |
|-------|------|-------|
| `id` | number | Use as `category_id` |
| `name` | string | Display label |
| `label` | string | Same as `name` — use for dropdown text |
| `icon` | string | Font Awesome class (display separately, do not prepend to label) |
| `slug` | string | URL slug |

## Main form fields (aligned with admin panel)

### Service Information

| Field | API key | Type | Required | Notes |
|-------|---------|------|----------|-------|
| Category | `category_id` | select | Yes | From `/services/categories` |
| Title | `title` | text | Yes | max 255 |
| Tagline | `tagline` | text | No | max 80 |
| Description | `description` | textarea | Yes | min 50 chars |
| What's Included | `whats_included` | string[] | No | Send as JSON array |
| What's Not Included | `whats_not_included` | string[] | No | Send as JSON array |
| Requirements from Buyer | `requirements` | textarea | No | max 1000 |

### Service Details

| Field | API key | Type | Required | Notes |
|-------|---------|------|----------|-------|
| Service Type | `service_type` | select | Yes* | `freelance`, `local`, `business` — defaults to `freelance` |
| Starting Price | `starting_price` | number | Yes | min 0 |
| Currency | `currency` | select | Yes | `USD`, `EUR`, `GBP`, `AUD`, `CAD`, `JPY` |
| Delivery Time (days) | `delivery_time` | number | No | 1–365 |
| Country | `country` | text | Yes | max 100 |
| City | `city` | text | No | max 100 |
| Latitude | `latitude` | number | No | For local services |
| Longitude | `longitude` | number | No | For local services |
| Service Area Radius (km) | `service_area_radius` | number | No | Show when `service_type` is `local` |
| Languages Spoken | `languages` | string[] | No | Send as JSON array |

### Status

| Field | API key | Type | Required | Notes |
|-------|---------|------|----------|-------|
| Status | `status` | select | No | `draft`, `active`, `paused`, `suspended` — default `draft` on create |

Use `"status": "active"` when the user clicks **Post Service** to publish.

## Array fields — request format

Send list fields as **JSON arrays of strings**:

```json
{
  "whats_included": ["Initial consultation", "2 revisions"],
  "whats_not_included": ["Rush delivery"],
  "languages": ["English", "Spanish"]
}
```

The API also accepts newline-separated strings and normalizes them automatically.

## Create service

```
POST /api/v1/services
Authorization: Bearer {token}
Content-Type: application/json
```

Example payload:

```json
{
  "category_id": 1,
  "title": "Professional Logo Design",
  "tagline": "Modern logos for your brand",
  "description": "I create professional logo designs tailored to your business needs with unlimited concepts.",
  "whats_included": ["Source files", "3 revisions"],
  "whats_not_included": ["Printing"],
  "requirements": "Brand guidelines if available",
  "service_type": "freelance",
  "starting_price": 98.00,
  "currency": "AUD",
  "delivery_time": 7,
  "country": "Australia",
  "city": "Sydney",
  "languages": ["English"],
  "status": "active"
}
```

## Update service

```
PUT /api/v1/services/{id}
Authorization: Bearer {token}
```

All main form fields can be updated, including:

- `category_id`
- `currency`
- `service_type`
- `status`
- `whats_included`, `whats_not_included`, `languages` (arrays)

Partial updates are supported (`sometimes` validation).

## Packages (optional)

Managed separately in admin via Packages tab. Can be included in create/update:

```json
{
  "packages": [
    {
      "name": "Basic",
      "description": "1 logo concept",
      "price": 98.00,
      "delivery_time": 7,
      "features": ["1 concept", "PNG export"],
      "revisions": 2
    }
  ]
}
```

Max 5 packages. Updating with `packages` replaces all existing packages.

## Media / images (required on create)

Upload images **after** the service is created (or during edit):

```
POST /api/v1/services/{id}/media
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

| Field | Type | Required |
|-------|------|----------|
| `file` | file | Yes (or use `files[]` for batch) |
| `type` | `image` \| `video` \| `document` | No (defaults to `image`) |
| `is_thumbnail` | boolean | No (first image auto-becomes thumbnail if none exists) |

Delete an image:

```
DELETE /api/v1/services/{id}/media/{mediaId}
```

The frontend form uploads images automatically after create/update. At least **one image** is required when posting a new service.

## List user's services

```
GET /api/v1/services/my-services
```

## Toggle active/paused

```
POST /api/v1/services/{id}/toggle-status
```

Toggles between `active` and `paused`.

## Frontend form checklist

Fields shown in the current frontend modal that match the backend:

- [x] Category → `category_id`
- [x] Currency → `currency`
- [x] Starting Price → `starting_price`
- [x] Delivery Time → `delivery_time`
- [x] Description → `description`
- [x] Requirements from Buyer → `requirements`
- [x] What's Included → `whats_included[]`
- [x] What's Not Included → `whats_not_included[]`
- [x] Country → `country`
- [x] City → `city`
- [x] Languages Spoken → `languages[]`
- [x] Service Images → upload via `/services/{id}/media` (form handles this automatically)

## Category dropdown fix

Do **not** render category label as `{icon} {name}`. Use `name` or `label` for text and show `icon` as a separate CSS class if needed.
