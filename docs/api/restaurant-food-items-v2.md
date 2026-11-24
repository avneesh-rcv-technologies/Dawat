# Restaurant food items API v2

For full endpoint URLs and copyable request bodies, see [Food Items v2 API README](FOOD_ITEMS_V2_README.md).

Base URL: `{{base_url}}/api/v2/restaurant/food-items`

Use `Authorization: Bearer {{token}}` and `Accept: application/json`. The token must belong to an active, email-verified restaurant owner. The restaurant is selected from the authenticated owner; never submit `restaurant_id` or `admin_id`. Active and draft restaurants can manage their menu, matching the food-item form.

## Endpoints

| Method | Path relative to base URL | Purpose |
| --- | --- | --- |
| GET | `/form-options` | Categories, dietary-label certificate eligibility/messages, serving configuration, image requirements |
| GET | `/serving-options` | The serving-options JSON: types, units, enabled/required fields, validation rules, help text |
| GET | `/` | Paginated food-item list |
| POST | `/` | Create an item with one or more serving options |
| GET | `/{id}` | Item details, images, selected dietary labels and certificate status, serving options and tax previews |
| PATCH | `/{id}` | Partially update the item; omitted item fields remain unchanged |
| PATCH | `/{id}/status` | Update `is_active` and/or `is_available` |
| DELETE | `/{id}` | Delete the food item |
| POST | `/{id}/serving-options` | Add a serving option |
| PATCH | `/{id}/serving-options/{optionId}` | Partially update a serving option, including its availability or serve type |
| DELETE | `/{id}/serving-options/{optionId}` | Remove an option; at least one option must remain |

The list accepts `search`, `category_id`, `is_active`, `is_available`, `page`, and `per_page` (1–100). Boolean query values are `0` or `1`. The response includes `meta.current_page`, `per_page`, `total`, `last_page`, and `has_more_pages`.

## Screen flow

1. Call `/form-options` to populate categories and dietary labels. Display each label's `message` and enable selection only when `allowed` is true.
2. Read `serving_options.serve_types[selected_type]` to show `enabled_fields`, mark `required_fields`, and populate `allowed_units`. Apply field definitions and `validation_overrides` from the same JSON. Changing a unit applies its `unit_fields` quantity settings.
3. Submit the item fields and a `serving_options` array. Each row is validated independently against its selected type and unit.
4. GET an existing item to populate the edit screen. Use PATCH for item changes or the nested serving-option endpoints to add/edit/delete individual options.

The shared configuration lives in `Modules/FoodManagements/src/Config/serving-options.json`. Server validation and the web form use the same configuration.

## Create example

Use real category and dietary-label IDs returned by `/form-options`.

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

Success: HTTP 201 with `success: true`, `message`, and the saved item in `data`. Prices are before tax. Each returned serving option includes its JSON guidance, required fields, allowed units, effective price, tax amount, and price including tax. Per-person prices apply to one guest.

## Update semantics

To update only the name:

```json
{"name": "Updated vegetable rice"}
```

When PATCH includes a `serving_options` array, that array replaces the item's complete serving-option list. Include an existing row's `id` to retain and update it. Rows without `id` are created. Omitted existing rows are deleted. Submit complete rows for the selected serve type. An ID belonging to another item is rejected.

Use the nested endpoints when changing one option without replacing the list. POST a single option object directly (no `serving_options` wrapper). PATCH an option with just the changed fields:

```json
{"price": "24.00", "discount_price": null}
```

A type change must include newly required fields. Fields disabled for the new type are excluded from validation and cleared in storage. Other unspecified fields, including the discount price, are retained; send `discount_price: null` to remove a discount that no longer applies.

Availability for one option:

```json
{"is_available": false}
```

Item status, using `PATCH /{id}/status`:

```json
{"is_active": true, "is_available": true}
```

Supply at least one status flag. The response contains the updated item. Activating or making an item available checks all selected dietary certificates again. Owners can deactivate, mark unavailable, or delete an item after a certificate is rejected.

## Feature mapping, matching the web item form

Use `GET /api/v2/restaurant/food-items/form-options` to obtain `data.features`:
active, supported features belonging to the authenticated restaurant, with their
active variants. The existing `GET /api/restaurant/food-features/active-lists`
endpoint also lists available features. Use the real `feature_id` and `variant_id`
values from those responses.

POST `/api/v2/restaurant/food-items` and PATCH
`/api/v2/restaurant/food-items/{id}` accept a `features` object, with feature IDs as
keys. This uses the same `features[feature_id]` shape as the web UI:

```json
{
  "features": {
    "7": 11,
    "8": [21, 22],
    "9": true,
    "10": "Punjab",
    "11": 450
  }
}
```

This is a complete PATCH body. For POST, add it to the required food-item fields
(name, description, category, dietary labels, tax and serving options).

| Feature type | Example value | Rules |
|---|---|---|
| S: Single choice | `"7": 11` | One active variant ID belonging to feature 7 |
| M: Multiple choice | `"8": [21,22]` | List of distinct active variant IDs belonging to feature 8 |
| C: Yes/No | `"9": true` or `false` | Also accepts `Y/N`, `yes/no`, `true/false`, `1/0`; stored as Y/N |
| T: Text | `"10": "Punjab"` | String up to 255 characters; numeric-looking strings remain text |
| O: Number | `"11": 450` | Number with at most two decimals, within decimal(12,2) bounds |

The feature type comes from the backend. Do not send a type or description in the
mapping value. False and numeric zero are valid assignments. Null, empty string or
an empty list clears that optional control's assignment. Maximum 50 feature keys
and 50 selections per multiple-choice feature.

For multipart requests with images, use these text fields alongside `images[]`:

```text
features[7]       = 11
features[8][0]    = 21
features[8][1]    = 22
features[9]       = 1
features[10]      = Punjab
features[11]      = 450
```

Use POST with `_method=PATCH` for multipart updates. JSON PATCH works directly.

- Omit `features` during update to retain current mappings.
- Supply `features` to replace the complete editable selection; include every
  active feature assignment you want to retain.
- Send `{"features": {}}` or `{"features": []}` in JSON to clear editable mappings.
- Hidden/disabled feature assignments are preserved, matching the web UI. They
  cannot be newly selected through this API.
- Foreign, hidden, unsupported or missing features and foreign/inactive variant
  IDs return 422 with an error such as `errors["features.7"]`.

Validation runs before item/image mutations. Mappings are saved to the existing
`food_feature_values` table in the same item transaction. Text/numeric values never
get guessed as variant IDs; each value is stored according to its feature type.

POST/PATCH and GET list/detail retain the detailed `features` array and also return
`feature_mapping`, ready to send back as the next write request's `features`:

```json
{
  "features": [
    {
      "feature_id": 9,
      "name": "Chef's Special",
      "feature_type": "C",
      "feature_style": "checkbox",
      "filter_style": "checkbox",
      "purpose": "find_products",
      "status": "A",
      "position": 0,
      "values": [{"variant_id": null, "variant_name": null, "value": "Y", "value_int": null}]
    }
  ],
  "feature_mapping": {"9": true}
}
```

The example shows fields inside one item's `data` object. Numeric mapping values
are returned as decimal strings (e.g. `"450.00"`) to preserve stored precision.
`feature_mapping` contains active supported feature assignments; `features` also
exposes historical hidden assignments. An old inactive choice may still be visible
in a saved item's details; selecting it again is rejected, so choose an active
option from form-options. No mapped features returns `features: []` and
`feature_mapping: {}`.

## Dietary certificate checks

Checks apply to every selected label with `is_certification_required=true`, including Halal. Its certification type must be active. A certificate in `restaurant_certifications` must belong to this restaurant and certification type, have status `approved`, and reference a document that still exists on the private local disk. Missing records/documents, submitted, under-review, rejected, and changes-requested certificates block using that label. Another restaurant's approval never unlocks the label. Labels that do not require a certificate remain allowed without an upload.

Creation, item updates, serving-option changes, and status activation enforce these checks on the server. The restaurant is locked during writes so certificate reviews and menu updates use the same restaurant context.

The V2 write service checks the final label selection before item changes, image
removals or uploads. Updates recheck saved labels even if `dietary_label_ids` is
omitted; send a new allowed selection to remove a blocked label. An upload alone
does not unlock a label: wait for admin approval. Multiple blocked labels return
multiple explanations under `errors.dietary_label_ids`.

`GET /api/v2/restaurant/food-items` and `GET /api/v2/restaurant/food-items/{id}`
remain readable when approval is missing/revoked. Each item's `dietary_labels`
reports current `required`, `is_certification_required`, `is_uploaded`,
`is_approved`, `allowed`, `status`, `message`, `certification_type_id`, and
`certificate_id`. Each item also includes:

```json
{
  "dietary_certificate_check": {
    "can_save": false,
    "blocked_dietary_label_ids": ["3"],
    "errors": ["Halal: Upload certificate for admin approval."]
  }
}
```

This is an additional field inside each item's `data` object. GET does not reject
the whole list because an existing item has a blocked label. See the
[restaurant certificate status API](restaurant-dietary-certificates.md) to check
all active labels that require certificates before building the form.

Example HTTP 422 response:

```json
{
  "success": false,
  "message": "Halal: Certificate awaiting admin approval.",
  "errors": {
    "dietary_label_ids": [
      "Halal: Certificate awaiting admin approval."
    ]
  }
}
```

Field validation errors use paths such as `serving_options.0.unit`. Messages identify the selected serving type and field; invalid units list the allowed units. Invalid requests do not save partial item or serving-option changes.

## Images

Dedicated image APIs now support list, append multiple images, replace one image, replace the gallery, and single/bulk deletion. See [sections 12–17 of the API README](FOOD_ITEMS_V2_README.md#12-get-uploaded-food-item-images) for endpoints and multipart bodies.

Create and update both accept **`images[]` or `food_image[]`** (send one field name,
not both). Upload JPG/JPEG/PNG/WebP files, up to 2 MB each and 10 total gallery
images. All inline uploads use Filemanager's validation, security scanning, storage
driver, file records and links with object type `food_image`.

Use `multipart/form-data` with nested names such as
`serving_options[0][serve_type]` and `dietary_label_ids[0]`. Omit image fields to
retain the gallery. By default new files append; existing plus new cannot exceed
10. Set **`replace_images=1`** to replace the entire gallery with the supplied 1–10
files. Replacement requires files, uploads/validates the new batch first, and
defers removal of old physical files until the database transaction commits.

Create:

```http
POST {{base_url}}/api/v2/restaurant/food-items
Authorization: Bearer <owner_token>
Accept: application/json
```

Example Postman form-data (use a category and dietary labels owned/allowed for the
authenticated restaurant):

| Key | Type | Example |
|---|---|---|
| `name` | Text | Rice |
| `description` | Text | Freshly cooked rice |
| `category_id` | Text | 1 |
| `tax_percentage` | Text | 5 |
| `dietary_label_ids[0]` | Text | 1 |
| `serving_options[0][serve_type]` | Text | tray |
| `serving_options[0][serving_name]` | Text | Full tray |
| `serving_options[0][quantity]` | Text | 1 |
| `serving_options[0][unit]` | Text | Trays |
| `serving_options[0][serves]` | Text | 8 |
| `serving_options[0][price]` | Text | 45 |
| `serving_options[0][is_available]` | Text | 1 |
| `images[]` | File | rice.jpg |
| `images[]` | File | rice-side.webp |

Update an item's images without resending its other fields:

```http
POST {{base_url}}/api/v2/restaurant/food-items/15
```

| Key | Type | Example |
|---|---|---|
| `replace_images` | Text | 1 |
| `images[]` | File | new-rice.jpg |
| `images[]` | File | new-rice-side.png |

Omit `replace_images` to append instead. A direct POST to the item URL and POST
with `_method=PATCH` are both supported. Let the HTTP client generate the multipart
Content-Type/boundary. An unparsed raw multipart PATCH receives a clear 400 error.

To remove selected images and append new ones in the same update, send
`remove_image_ids[0]=51`, `remove_image_ids[1]=52`, plus the new `images[]` files.
Use image **link IDs** from `data.images[].id` in the GET item response. IDs must
belong to the current food item. Remaining old images plus new images cannot exceed
10. Omitted existing IDs are retained. Image removals and item changes roll back
together if validation/upload fails; old physical files are removed only after
commit. Do not combine `remove_image_ids` with `replace_images=1`.

Create returns 201; update returns 200. Both return the full item under `data`,
including the saved Filemanager images:

```json
{
  "image": {"id": 51, "url": "https://example.com/storage/uploads/rice.jpg", "original_name": "rice.jpg"},
  "images": [
    {"id": 51, "url": "https://example.com/storage/uploads/rice.jpg", "original_name": "rice.jpg"},
    {"id": 52, "url": "https://example.com/storage/uploads/rice-side.webp", "original_name": "rice-side.webp"}
  ]
}
```

The snippet shows only the image fields inside `data`. Invalid image types, an
11-file batch, or appending beyond 10 returns 422. Validation errors use the
canonical `food_image` / `food_image.N` field names for either upload alias.

For a multipart update, send POST to `/{id}` with `_method=PATCH`, allowing PHP to parse the uploaded files. Standard JSON updates use PATCH directly.

The dedicated POST `/{id}/images` appends files and checks existing plus uploaded
images against the 10-image gallery limit. PATCH `/{id}/images` replaces the
gallery; PATCH `/{id}/images/{imageId}` replaces one image. Every path checks
actual image content, filename extension, and a maximum of 2048 KB per image.

PHP must allow a complete multipart request containing ten 2 MB files. The
project's `docker/php.ini` and `public/.user.ini` set `post_max_size` to 100M.
Restart/reload the applicable PHP process after configuration changes. PHP's
built-in development server ignores `.user.ini`; start it with the project INI
scan directory to load the same request limit:

```bash
PHP_INI_SCAN_DIR=":$(pwd)/docker" php artisan serve --no-reload
```

## Responses and errors

- 200: successful GET/PATCH/DELETE.
- 201: item or serving option created.
- 401: authentication required.
- 403: an active, verified restaurant owner account is required.
- 404: restaurant, item, or serving option is not available to this owner.
- 422: field validation, dietary certificate, or minimum/maximum option-count error.

Responses use `success`, `message`, and `data` for success; errors use `success: false`, `message`, and field `errors` when applicable. Authenticated responses use `Cache-Control: private, no-store`.

## Verification

```bash
php artisan route:list --path=api/v2/restaurant/food-items
php -l Modules/Restaurant/src/Http/Controllers/Api/V2/FoodItemImageController.php
php -l Modules/Restaurant/src/Services/RestaurantFoodItemImageService.php
```
