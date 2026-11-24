# Restaurant Food Items API v2 — README

This document describes the current v2 API for the food-item create/edit screen: item details, dietary labels, certificate eligibility, images, status, and multiple serving options.

## Base URL and headers

Set these variables in Postman or your API client:

| Variable | Value |
| --- | --- |
| `base_url` | Application origin, including `https://`, without a trailing slash |
| `token` | Restaurant owner's bearer token |
| `food_item_id` | Item ID returned by create or list |
| `serving_option_id` | Serving-option ID returned inside an item's `serving_options` |
| `image_id` | Image-link ID returned in `data.images` |

```text
{{base_url}}/api/v2/restaurant/food-items
```

For all requests:

```http
Authorization: Bearer {{token}}
Accept: application/json
```

For JSON POST/PATCH requests, also send:

```http
Content-Type: application/json
```

The authenticated account must be an active, email-verified restaurant owner. The API selects that owner's first restaurant by ID with status `draft` or `active`. Do not send `restaurant_id` or `admin_id`; ownership is determined on the server. Item, category, and serving-option IDs must belong to the selected restaurant/item.

All IDs and URLs below are examples. Obtain real category and dietary-label IDs from `/form-options`.

## Endpoint list

All paths below start with `{{base_url}}/api/v2/restaurant/food-items`.

| # | Method | Path | Request body | Success |
| --- | --- | --- | --- | --- |
| 1 | GET | `/form-options` | None | 200 |
| 2 | GET | `/serving-options` | None | 200 |
| 3 | GET | `/` | None; query filters supported | 200 |
| 4 | POST | `/` | Complete item and serving options | 201 |
| 5 | GET | `/{id}` | None | 200 |
| 6 | PATCH | `/{id}` | Changed item fields | 200 |
| 7 | PATCH | `/{id}/status` | `is_active` and/or `is_available` | 200 |
| 8 | DELETE | `/{id}` | None | 200 |
| 9 | POST | `/{id}/serving-options` | One complete serving-option object | 201 |
| 10 | PATCH | `/{id}/serving-options/{optionId}` | Changed serving-option fields | 200 |
| 11 | DELETE | `/{id}/serving-options/{optionId}` | None | 200 |
| 12 | GET | `/{id}/images` | None | 200 |
| 13 | POST | `/{id}/images` | Multipart `images[]`; append multiple images | 201 |
| 14 | PATCH | `/{id}/images` | Multipart `images[]`; replace the entire gallery | 200 |
| 15 | PATCH | `/{id}/images/{imageId}` | Multipart `image`; replace one image | 200 |
| 16 | DELETE | `/{id}/images/{imageId}` | None | 200 |
| 17 | DELETE | `/{id}/images` | JSON `image_ids` array | 200 |

## 1. Get form options

```http
GET {{base_url}}/api/v2/restaurant/food-items/form-options
```

**Body:** none.

The response has `success`, `message`, and `data`. Use these paths to build the screen:

| Response path | Purpose |
| --- | --- |
| `data.restaurant_id` | Restaurant selected for the authenticated owner |
| `data.categories` | Categories, including `id`, `name`, and `status` |
| `data.dietary_labels` | Active dietary labels, certificate type ID, `allowed`, `required`, `status`, and `message` |
| `data.serving_options` | Full serving-option configuration |
| `data.image_upload` | Upload field name, allowed extensions, maximum file size and count |

For each dietary label, display `message` and enable selection only when `allowed` is true. Halal is selectable only after approval when its label requires certification.

## 2. Get serving types, units, and dynamic validation

```http
GET {{base_url}}/api/v2/restaurant/food-items/serving-options
```

**Body:** none.

The full configuration is returned under `data`:

- `default_serve_type`: initial type.
- `serve_types[type].enabled_fields`: fields to show and enable.
- `serve_types[type].required_fields`: mandatory fields for that type.
- `serve_types[type].disabled_fields`: fields to hide and omit from submission.
- `serve_types[type].allowed_units`: valid units, with exact case-sensitive values.
- `serve_types[type].defaults.unit`: initial unit.
- `serve_types[type].field_labels`, `field_help`, and `help`: labels and screen messages.
- `fields[field].validation`: field validation rules.
- `serve_types[type].validation_overrides`: type-specific rule overrides.
- `unit_fields[unit]`: quantity label, step, and validation for the selected unit.
- `collection`: minimum/maximum option counts.

Apply these settings independently to each row. On a type change, update visible/required fields and unit options. Keep the current unit if allowed; otherwise use the configured default. On a unit change, update quantity settings. Disabled fields are excluded from server validation and cleared in storage.

This endpoint returns configuration, not an item's saved options. Get saved options from `GET /{id}` under `data.serving_options`.

## 3. Get food-item list

```http
GET {{base_url}}/api/v2/restaurant/food-items
```

**Body:** none.

Example with filters:

```http
GET {{base_url}}/api/v2/restaurant/food-items?search=rice&category_id=12&is_active=1&is_available=1&page=1&per_page=15
```

| Query parameter | Validation / behavior |
| --- | --- |
| `search` | Optional string, maximum 255 characters; matches item name |
| `category_id` | Optional integer; must belong to this restaurant |
| `is_active` | Optional `0` or `1` |
| `is_available` | Optional `0` or `1` |
| `page` | Optional integer, minimum 1 |
| `per_page` | Optional integer, 1–100; default 15 |

Results are ordered by item ID descending. The response contains an item array in `data` and pagination in `meta`.

Empty-list response:

```json
{
  "success": true,
  "message": "Food items retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 0,
    "last_page": 1,
    "has_more_pages": false
  }
}
```

## 4. Create a food item

```http
POST {{base_url}}/api/v2/restaurant/food-items
```

**Body — raw JSON:**

```json
{
  "name": "Vegetable rice",
  "description": "Rice with seasonal vegetables",
  "category_id": 12,
  "tax_percentage": "10.00",
  "dietary_label_ids": ["3"],
  "is_active": true,
  "is_available": true,
  "serving_options": [
    {
      "serve_type": "tray",
      "serving_name": "Half tray",
      "quantity": "1.00",
      "unit": "Trays",
      "serves": 5,
      "price": "20.00",
      "discount_price": "18.00",
      "is_available": true
    },
    {
      "serve_type": "per_person",
      "serving_name": "Buffet",
      "min_guests": 10,
      "max_guests": 40,
      "price": "12.00",
      "discount_price": null,
      "is_available": true
    }
  ]
}
```

HTTP **201**. Message: `Food item created successfully.` The saved item is returned under `data`, including generated item/serving-option IDs. Omitted `is_active` and `is_available` default to true on creation.

Do not supply serving-option IDs when creating an item. `sale_price` and `list_price` are calculated from the first available serving option, falling back to the first option if none are available; do not send them as independent input prices.

## 5. Get one item and its uploaded images/serving options

```http
GET {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}
```

**Body:** none.

HTTP **200**. Message: `Food item retrieved successfully.`

The same item structure is returned by create, item PATCH, status PATCH, and successful serving-option mutations:

| `data` field | Content |
| --- | --- |
| `id`, `restaurant_id` | Item and owner restaurant IDs |
| `name`, `description` | Item details |
| `category_id`, `categories` | Selected category ID and category summaries |
| `tax_percentage` | Tax percentage as a decimal string |
| `dietary_label_ids` | Selected label IDs as strings |
| `dietary_labels` | Current eligibility/status/message for each selected label |
| `is_active`, `is_available` | Item status flags |
| `sale_price`, `list_price` | Effective and regular primary serving-option prices |
| `image`, `images` | First image or null, and image list; each image has `id`, `url`, `original_name` |
| `serving_options` | Saved rows, including dynamic guidance and price/tax previews |
| `created_at`, `updated_at` | ISO 8601 timestamps |

Each saved serving option contains:

```json
{
  "id": 501,
  "serve_type": "tray",
  "serving_name": "Half tray",
  "quantity": "1.00",
  "unit": "Trays",
  "serves": 5,
  "min_guests": null,
  "max_guests": null,
  "price": "20.00",
  "discount_price": "18.00",
  "is_available": true,
  "serve_type_label": "Tray",
  "message": "Tray: set the amount included in one option and its total price before tax. Add another option for a different size.",
  "enabled_fields": ["serve_type", "serving_name", "price", "discount_price", "is_available", "quantity", "unit", "serves"],
  "required_fields": ["serve_type", "serving_name", "price", "is_available", "quantity", "unit", "serves"],
  "allowed_units": ["Trays", "Pieces"],
  "pricing_basis": "per_option",
  "effective_price": "18.00",
  "tax_amount": "1.80",
  "price_including_tax": "19.80"
}
```

This example uses 10% tax. Decimal prices/quantities are returned as strings; counts such as `serves` and guest limits are integers.

## 6. Partially update a food item

```http
PATCH {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}
```

**Body — change ordinary item fields:**

```json
{
  "name": "Updated vegetable rice",
  "description": "Rice with fresh seasonal vegetables",
  "category_id": 12,
  "tax_percentage": "8.25",
  "dietary_label_ids": ["3", "4"]
}
```

Send only fields you want to change. At least one supported field is required. Omitted item fields are retained. The merged item must satisfy the screen's required fields and certificate rules. Use real, permitted label IDs; the example IDs do not imply particular labels.

**Body — replace the complete serving-option list:**

```json
{
  "serving_options": [
    {
      "id": 501,
      "serve_type": "tray",
      "serving_name": "Large tray",
      "quantity": "2.00",
      "unit": "Trays",
      "serves": 10,
      "price": "40.00",
      "discount_price": null,
      "is_available": true
    },
    {
      "serve_type": "package",
      "serving_name": "Family combo",
      "serves": 4,
      "price": "30.00",
      "discount_price": null,
      "is_available": true
    }
  ]
}
```

Include an existing row's `id` to keep/update that row; omit `id` to create a row. Existing rows omitted from this submitted array are deleted. Submit complete rows for the selected types. At least one option must remain, with a maximum of 50.

HTTP **200**. Message: `Food item updated successfully.` Use the nested endpoints below to change a single serving option without replacing the list.

## 7. Update item status

```http
PATCH {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/status
```

**Body — activate and make available:**

```json
{
  "is_active": true,
  "is_available": true
}
```

**Alternative body — temporarily unavailable:**

```json
{"is_available": false}
```

**Alternative body — inactive:**

```json
{"is_active": false}
```

Send at least one flag. Setting either submitted flag to true rechecks the item's selected dietary certificates. Deactivation/unavailability remains possible after certificate rejection. Flags are explicit values, not toggle commands.

HTTP **200**. Message: `Food item status updated successfully.`

## 8. Delete a food item

```http
DELETE {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}
```

**Body:** none.

HTTP **200**:

```json
{
  "success": true,
  "message": "Food item deleted successfully.",
  "data": null
}
```

An item can be deleted even when one of its selected certificates is no longer approved.

## 9. Add another serving option

```http
POST {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/serving-options
```

**Body — one option object, without a `serving_options` wrapper:**

```json
{
  "serve_type": "bowl",
  "serving_name": "Large bowl",
  "quantity": "1.00",
  "unit": "Bowls",
  "serves": 1,
  "price": "12.00",
  "discount_price": "10.00",
  "is_available": true
}
```

Do not submit `id`, `food_item_id`, `restaurant_id`, or `admin_id`. Existing options are retained. The combined list may contain at most 50 options. See the type examples below for other valid bodies.

HTTP **201**. Message: `Serving option added successfully.` `data` is the updated **whole item**, with all saved options and their IDs.

## 10. Update one serving option

```http
PATCH {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/serving-options/{{serving_option_id}}
```

**Body — change price and remove discount:**

```json
{
  "price": "24.00",
  "discount_price": null
}
```

**Alternative body — change availability:**

```json
{"is_available": false}
```

**Alternative body — switch to per-person pricing:**

```json
{
  "serve_type": "per_person",
  "serving_name": "Buffet per guest",
  "min_guests": 10,
  "max_guests": 100,
  "price": "15.00",
  "discount_price": null,
  "is_available": true
}
```

Newly required fields must be provided when changing type. Disabled fields such as quantity/unit/serves in `per_person` are cleared. Unspecified fields that remain relevant are retained. Send `discount_price: null` to clear a previous discount; otherwise it must still be lower than the updated price.

HTTP **200**. Message: `Serving option updated successfully.` `data` contains the updated whole item.

## 11. Delete one serving option

```http
DELETE {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/serving-options/{{serving_option_id}}
```

**Body:** none.

HTTP **200**. Message: `Serving option deleted successfully.` `data` contains the updated whole item.

Deleting the last option returns HTTP **422** with `message: "At least one serving option is required."`. An option belonging to another item cannot be deleted or edited.

## 12. Get uploaded food-item images

```http
GET {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images
```

**Body:** none. Use the same bearer token and `Accept: application/json` headers as the item endpoints.

HTTP **200**:

```json
{
  "success": true,
  "message": "Food item images retrieved successfully.",
  "data": {
    "food_item_id": 123,
    "images": [
      {"id": 901, "url": "https://your-domain.example/storage/uploads/food_image/123/example.jpg", "original_name": "rice.jpg", "position": 0},
      {"id": 902, "url": "https://your-domain.example/storage/uploads/food_image/123/example2.png", "original_name": "platter.png", "position": 1}
    ],
    "count": 2,
    "max_images": 10
  }
}
```

`images[].id` is the image link ID. Use this value as `imageId` in image PATCH/DELETE URLs or in `image_ids` for bulk deletion. It is the same ID exposed by the item GET response's `data.images` array. `position` describes gallery order; the first image is used by the item response as its preview. An empty gallery returns `images: []` and `count: 0`.

## 13. Upload multiple images — append to gallery

```http
POST {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images
```

**Body — multipart form-data:**

| Key | Type | Value |
| --- | --- | --- |
| `images[0]` | File | Select the first JPG/JPEG/PNG/WebP |
| `images[1]` | File | Select the second JPG/JPEG/PNG/WebP |
| `images[2]` | File | Optional additional image |

Use the field name `images` for these dedicated image endpoints. The item-create endpoint continues to use `food_image`.

HTTP **201**. Message: `Food item images uploaded successfully.` Response `data` is the **gallery object shown in section 12**, containing all existing and newly uploaded images. Existing images remain in place, and new images are appended in submitted order.

Limits: 1–10 files per request, **2 MB per file**, and **10 images in the complete gallery**. For example, a gallery with 8 images accepts up to 2 more. Actual content and filename extension must be JPG/JPEG/PNG/WebP. Supply bearer authorization and `Accept: application/json`; let your client generate the multipart Content-Type boundary.

Example cURL:

```bash
curl --request POST 'https://your-domain.example/api/v2/restaurant/food-items/123/images' \
  --header 'Authorization: Bearer YOUR_TOKEN' \
  --header 'Accept: application/json' \
  --form 'images[0]=@/absolute/path/rice.jpg' \
  --form 'images[1]=@/absolute/path/platter.png'
```

## 14. Replace the whole gallery with multiple images

```http
PATCH {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images
```

For PHP multipart parsing, use **POST with `_method=PATCH`** at that URL:

| Key | Type | Value |
| --- | --- | --- |
| `_method` | Text | PATCH |
| `images[0]` | File | First replacement image |
| `images[1]` | File | Optional second replacement image |

HTTP **200**. Message: `Food item images updated successfully.` Returns the updated gallery object. All old gallery image links are replaced; the submitted file order becomes the gallery order, and the new images receive new IDs. At least one replacement file is required. Use the DELETE endpoints to remove all images without replacements.

```bash
curl --request POST 'https://your-domain.example/api/v2/restaurant/food-items/123/images' \
  --header 'Authorization: Bearer YOUR_TOKEN' \
  --header 'Accept: application/json' \
  --form '_method=PATCH' \
  --form 'images[0]=@/absolute/path/new-rice.jpg' \
  --form 'images[1]=@/absolute/path/new-platter.png'
```

## 15. Replace one uploaded image

```http
PATCH {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images/{{image_id}}
```

**Body — multipart, sent with POST method override:**

| Key | Type | Value |
| --- | --- | --- |
| `_method` | Text | PATCH |
| `image` | File | One replacement JPG/JPEG/PNG/WebP, up to 2 MB |

Use singular `image`, not `images[]`, for this endpoint. The selected image ID and gallery position are preserved, while its file and URL are replaced. Other images remain unchanged. Replacing one image is allowed when the gallery already contains 10 images.

HTTP **200**. Message: `Food item image updated successfully.` Returns the complete updated gallery.

```bash
curl --request POST 'https://your-domain.example/api/v2/restaurant/food-items/123/images/901' \
  --header 'Authorization: Bearer YOUR_TOKEN' \
  --header 'Accept: application/json' \
  --form '_method=PATCH' \
  --form 'image=@/absolute/path/replacement.jpg'
```

## 16. Delete one image

```http
DELETE {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images/{{image_id}}
```

**Body:** none.

HTTP **200**. Message: `Food item image deleted successfully.` Returns the remaining gallery. Deleting the last image is allowed and returns `images: []` with `count: 0`.

## 17. Delete multiple images

```http
DELETE {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}/images
Content-Type: application/json
```

**Body — raw JSON:**

```json
{
  "image_ids": [901, 902]
}
```

Submit 1–10 distinct positive image-link IDs from this item's gallery. HTTP **200**, with message `Food item images deleted successfully.` and the remaining gallery in `data`.

If any submitted image does not belong to this item, the request returns **404** and no submitted images are deleted. A food item or image belonging to another restaurant cannot be read, changed, or deleted through these endpoints.

### Image mutation responses and failures

All dedicated image endpoints return the gallery structure from section 12, rather than the full food item. Refresh the UI from `data.images` after a successful mutation; item GET also returns the updated gallery.

- Invalid/missing images, unsupported file type, files over 2 MB, duplicate deletion IDs, or exceeding 10 gallery images: **422**.
- Item or image outside the authenticated owner's item: **404**.
- Unexpected upload/storage failure: **500**. Failed batches roll back their metadata and clean up new files; gallery replacement retains existing images when the upload fails. Old physical files are removed after the database change commits. Files still referenced by another image link are retained.

Do not send `restaurant_id`, `admin_id`, or `food_item_id` in image request bodies. The item ID comes from the URL, and ownership comes from the token.

## Serving-type field and unit reference

These are the current values from the JSON configuration. Always fetch `/serving-options` so clients follow future configuration changes.

Every type requires `serve_type`, `serving_name`, `price`, and `is_available`. `discount_price` is optional and may be null.

| Serve type | Additional required fields | Optional type fields | Allowed units |
| --- | --- | --- | --- |
| `tray` | `quantity`, `unit`, `serves` | None | `Trays`, `Pieces` |
| `plate` | `quantity`, `unit`, `serves` | None | `Plates` |
| `bowl` | `quantity`, `unit`, `serves` | None | `Bowls` |
| `box` | `quantity`, `unit`, `serves` | None | `Boxes`, `Pieces` |
| `portion` | `quantity`, `unit`, `serves` | None | `Portions`, `Pieces` |
| `piece` | `quantity`, `unit`, `serves` | None | `Pieces` |
| `by_weight` | `quantity`, `unit`, `serves` | None | `Kg`, `Grams`, `Oz`, `Lb` |
| `by_volume` | `quantity`, `unit`, `serves` | None | `Litres`, `Ml`, `Fl oz`, `Cups` |
| `per_person` | `min_guests` | `max_guests` | No unit field |
| `package` | `serves` | None | No unit field |

### Copyable option bodies for every type

Each object below can be sent directly to `POST /{id}/serving-options`, or placed inside the `serving_options` array when creating/replacing an item's options. Do not send all examples unless you want all those options saved.

#### Tray (`tray`)

```json
{
  "serve_type": "tray",
  "serving_name": "Tray option",
  "quantity": "1.00",
  "unit": "Trays",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Plate (`plate`)

```json
{
  "serve_type": "plate",
  "serving_name": "Plate option",
  "quantity": "1.00",
  "unit": "Plates",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Bowl (`bowl`)

```json
{
  "serve_type": "bowl",
  "serving_name": "Bowl option",
  "quantity": "1.00",
  "unit": "Bowls",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Box / container (`box`)

```json
{
  "serve_type": "box",
  "serving_name": "Box / container option",
  "quantity": "1.00",
  "unit": "Boxes",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Portion (`portion`)

```json
{
  "serve_type": "portion",
  "serving_name": "Portion option",
  "quantity": "1.00",
  "unit": "Portions",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Piece / item (`piece`)

```json
{
  "serve_type": "piece",
  "serving_name": "Piece / item option",
  "quantity": "1.00",
  "unit": "Pieces",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### By weight (`by_weight`)

```json
{
  "serve_type": "by_weight",
  "serving_name": "By weight option",
  "quantity": "0.50",
  "unit": "Kg",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### By volume (`by_volume`)

```json
{
  "serve_type": "by_volume",
  "serving_name": "By volume option",
  "quantity": "1.00",
  "unit": "Litres",
  "serves": 1,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Per person (`per_person`)

```json
{
  "serve_type": "per_person",
  "serving_name": "Per person option",
  "min_guests": 10,
  "max_guests": 100,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

#### Package / combo (`package`)

```json
{
  "serve_type": "package",
  "serving_name": "Package / combo option",
  "serves": 4,
  "price": "20.00",
  "discount_price": null,
  "is_available": true
}
```

## Validation reference

| Field | Rule |
| --- | --- |
| `name` | Required for create; string, maximum 255 characters |
| `description` | Required for create; string, maximum 500 characters |
| `category_id` | Required for create; integer ID belonging to the authenticated restaurant |
| `tax_percentage` | Required for create; numeric, 0–100, at most 2 decimal places |
| `dietary_label_ids` | Required for create; list of 1–50 distinct active label IDs, each string up to 36 characters; numeric legacy IDs are normalized to strings |
| `is_active`, `is_available` | Optional item flags on create; booleans; default true |
| `serving_options` | Required on create; 1–50 rows; supplied arrays on item PATCH replace the complete list |
| `serving_name` | Required per option; string, maximum 100 characters |
| `serve_type` | Required per option; key from the serving configuration |
| `quantity` | Required when enabled; greater than 0, at most 2 decimal places, maximum 99,999,999.99 |
| `unit` | Required when enabled; exact unit allowed for the selected type |
| `serves`, `min_guests` | Required when enabled; integer, 1–4,294,967,295 |
| `max_guests` | Optional for per-person; nullable integer, at least `min_guests`, maximum 4,294,967,295 |
| `price` | Required per option; numeric, 0–99,999,999.99, at most 2 decimal places |
| `discount_price` | Optional, nullable; numeric, nonnegative, at most 2 decimal places, strictly less than `price` |
| Option `is_available` | Required boolean for each new option |
| `food_image` | Optional array, maximum 10 files |
| Each food image | Actual image content, JPG/JPEG/PNG/WebP, maximum 2 MB per file |

Use JSON booleans `true`/`false`; multipart booleans use `1`/`0`. Empty optional discount and maximum-guests values may be sent as null. Do not send response-only fields such as `message`, `effective_price`, `tax_amount`, `required_fields`, or `allowed_units` back inside serving-option objects.

## Halal and other dietary certificate checks

Checks apply to any selected label with `required: true`, including Halal. It must have a certificate belonging to this restaurant with status `approved` and a nonempty uploaded-document reference.

| Certificate status | Selectable when certification is required? | UI behavior |
| --- | --- | --- |
| `not_submitted` | No | Ask owner to upload a certificate |
| `submitted` | No | Show awaiting approval |
| `under_review` | No | Show awaiting approval |
| `rejected` | No | Ask owner to upload a corrected certificate |
| `changes_requested` | No | Ask owner to update and resubmit |
| `approved` | Yes, with an uploaded-document reference | Show approved |

For labels that do not require certification, `allowed` is true regardless of certificate status. Display the server-provided `message`, rather than guessing from the status alone.

Creation, normal item updates, serving-option mutations, and status activation enforce eligibility. Item deactivation, unavailability, and deletion remain available after rejection. These food-item endpoints do not approve certificates; certificate review remains an admin action.

Example HTTP **422**:

```json
{
  "success": false,
  "message": "Halal: Certificate required · Awaiting admin approval. You can select this label after approval.",
  "errors": {
    "dietary_label_ids": [
      "Halal: Certificate required · Awaiting admin approval. You can select this label after approval."
    ]
  }
}
```

## Images during item create/update and multipart PATCH

For separate gallery management after item creation, use sections 12–17. POST to `/{id}/images` appends; the older `food_image` field on the item itself replaces its gallery.

For files, select **form-data** in Postman. Let Postman/cURL set `Content-Type` with the multipart boundary; do not manually send `Content-Type: application/json`.

Example create form-data fields:

| Key | Type | Example |
| --- | --- | --- |
| `name` | Text | Vegetable rice |
| `description` | Text | Rice with seasonal vegetables |
| `category_id` | Text | 12 |
| `tax_percentage` | Text | 10.00 |
| `dietary_label_ids[0]` | Text | 3 |
| `is_active` | Text | 1 |
| `is_available` | Text | 1 |
| `serving_options[0][serve_type]` | Text | tray |
| `serving_options[0][serving_name]` | Text | Half tray |
| `serving_options[0][quantity]` | Text | 1.00 |
| `serving_options[0][unit]` | Text | Trays |
| `serving_options[0][serves]` | Text | 5 |
| `serving_options[0][price]` | Text | 20.00 |
| `serving_options[0][is_available]` | Text | 1 |
| `food_image[0]` | File | Select a JPG/PNG file |
| `food_image[1]` | File | Optional second JPG/PNG file |

For an image update, send POST with method override:

```http
POST {{base_url}}/api/v2/restaurant/food-items/{{food_item_id}}
```

| Key | Type | Example |
| --- | --- | --- |
| `_method` | Text | PATCH |
| `food_image[0]` | File | Select an additional image |

Omit `food_image` to keep existing images. Sending new images appends them to the gallery and preserves existing images, matching the web form. Existing plus new images cannot exceed 10. Use the dedicated gallery-replacement endpoint only when replacing the entire gallery is intended.

Example cURL image update; replace the URL, token, item ID, and file path:

```bash
curl --request POST 'https://your-domain.example/api/v2/restaurant/food-items/123' \
  --header 'Authorization: Bearer YOUR_TOKEN' \
  --header 'Accept: application/json' \
  --form '_method=PATCH' \
  --form 'food_image[0]=@/absolute/path/rice.jpg'
```

## Troubleshooting: every field says required

If `name`, `description`, and all other submitted fields report “required”, the request body may not have been decoded as JSON.

In Postman:

1. Use **POST** `{{base_url}}/api/v2/restaurant/food-items` to create the item.
2. Select **Body → raw → JSON**.
3. Send `Content-Type: application/json`, `Accept: application/json`, and your bearer authorization header.
4. Paste one complete JSON object. After the closing `]` of `serving_options`, include the final `}` of the item object. Do not include Markdown fences or literal backslashes at the end of lines.
5. Use category and dietary-label IDs returned by `/form-options` for this authenticated restaurant.

The v2 middleware also recovers valid JSON sent as raw text or with a missing/mistaken content-type header. Malformed JSON returns **400**, with an error under `errors.body`, rather than misleading missing-field errors. Normal form-data/image uploads remain supported.

Example malformed-body response:

```json
{
  "success": false,
  "message": "Invalid JSON request body. Send a complete JSON object, including the final closing brace, with Content-Type: application/json.",
  "errors": {
    "body": [
      "Invalid JSON request body. Send a complete JSON object, including the final closing brace, with Content-Type: application/json."
    ]
  }
}
```

## Error responses

| HTTP status | Meaning |
| --- | --- |
| 200 | GET, PATCH, or DELETE succeeded |
| 201 | Item or serving option created |
| 400 | Malformed JSON or unsupported JSON root value |
| 401 | Authentication required/invalid token |
| 403 | Account is not permitted, including inactive or unverified owner |
| 404 | Selected restaurant, food item, or serving option was not found for this owner |
| 422 | Validation, dietary certificate, or serving-option count error |
| 500 | Unexpected server error |

Field-validation example, HTTP **422**:

```json
{
  "success": false,
  "message": "Bowl requires one of these units: Bowls.",
  "errors": {
    "serving_options.0.unit": [
      "Bowl requires one of these units: Bowls."
    ]
  }
}
```

Error field indices match the option's position in the complete validated list. For a nested option PATCH/POST, this can be its position within the item's list rather than index zero. Show `message` as a summary and bind individual `errors` to their fields.

The API uses `success: false` for errors and `Cache-Control: private, no-store` for its responses. Successful writes return the updated record, so clients can refresh generated IDs, option order, guidance, and computed prices.

## Source and verification

- Routes: `Modules/Restaurant/src/Routes/api-v2.php`.
- Controller: `Modules/Restaurant/src/Http/Controllers/Api/V2/FoodItemController.php`.
- Serving configuration: `Modules/FoodManagements/src/Config/serving-options.json`.
- The older public configuration-only endpoint is `GET {{base_url}}/api/foodmanagements/serving-options`; it does not provide owner-specific item data or certificate eligibility.

Check route registration and PHP syntax after deployment:

```bash
php artisan route:list --path=api/v2/restaurant/food-items
php -l Modules/Restaurant/src/Http/Controllers/Api/V2/FoodItemImageController.php
php -l Modules/Restaurant/src/Services/RestaurantFoodItemImageService.php
```

The route list contains 17 endpoints, including the six image endpoints. If deployment uses cached routes, rebuild the route cache as part of the normal deployment process.
