# Restaurant food feature create/update

```http
Authorization: Bearer <restaurant_owner_token>
Accept: application/json
Content-Type: application/json
```

| Method | Endpoint |
|---|---|
| POST | `{{base_url}}/api/restaurant/food-features` |
| PATCH / PUT | `{{base_url}}/api/restaurant/food-features/{id}` |

The `/api/restaurant/features` aliases use the same behavior. Ownership remains
scoped to the authenticated restaurant. POST requires `name` and `feature_type`.
PATCH may omit them to keep existing values. Send JSON without comments.

## Minimal create bodies

Single choice:

```json
{
  "name": "Spice Level",
  "feature_type": "S",
  "variants": [{"description": "Mild"}, {"description": "Hot"}]
}
```

Multiple choice:

```json
{
  "name": "Allergens",
  "feature_type": "M",
  "variants": [{"description": "Nuts"}, {"description": "Dairy"}, {"description": "Eggs"}]
}
```

Yes/No:

```json
{"name": "Chef's Special", "feature_type": "C"}
```

Text:

```json
{"name": "Origin", "feature_type": "T"}
```

Number:

```json
{"name": "Calories", "feature_type": "O"}
```

The backend assigns the fixed type configuration on every create/update. Previously
submitted purpose/style values cannot override it:

| Type | Purpose | Feature style | Filter style |
|---|---|---|---|
| S | find_products | text | checkbox |
| M | find_products | multiple_checkbox | checkbox |
| C | find_products | checkbox | checkbox |
| T | describe_product | text | empty string |
| O | find_products | number | slider |

Status defaults to `A`; updates preserve existing status when omitted. Existing
optional status/other metadata fields remain supported. Submitted options default
to `A`, and positions are assigned 1, 2, 3… in request order. S/M need at least two
non-empty, case-insensitively distinct descriptions and allow at most 50 options.
C/T/O have no variants; changing to those types clears old options. Types outside
S/M/C/T/O return 422; groups use the separate feature-group endpoints.

## Update examples

Rename only:

```http
PATCH {{base_url}}/api/restaurant/food-features/7
```

```json
{"name": "Spice Preference"}
```

Update type/options with the same minimal structure as POST:

```json
{
  "name": "Spice Preference",
  "feature_type": "M",
  "variants": [{"description": "Mild"}, {"description": "Hot"}, {"description": "Medium"}]
}
```

When `variants` is omitted, existing options are retained for S/M. When supplied,
the array represents the complete desired option list. Matching descriptions
retain their existing IDs (case-insensitive, ignoring surrounding whitespace).
New descriptions get new IDs; omitted old options are removed. To rename an
existing option while preserving its ID/food-item assignments, optionally include
its `variant_id`:

```json
{
  "variants": [
    {"variant_id": 11, "description": "Low"},
    {"variant_id": 12, "description": "Hot"}
  ]
}
```

IDs must belong to this feature and cannot be repeated. Changing from a non-choice
type to S/M requires at least two options. Status can also be toggled through the
existing `PATCH /api/restaurant/food-features/{id}/status` boolean endpoint.

## Responses

POST returns **201**; PATCH/PUT returns **200**. Both return the full saved model,
including generated settings and option IDs. Example POST response (selected
fields; timestamps and other saved metadata are also returned):

```json
{
  "success": true,
  "message": "Food feature created successfully.",
  "data": {
    "feature_id": 7,
    "restaurant_id": 397,
    "feature_code": "",
    "feature_type": "S",
    "purpose": "find_products",
    "feature_style": "text",
    "filter_style": "checkbox",
    "status": "A",
    "position": 0,
    "parent_id": 0,
    "display_on_product": "Y",
    "display_on_catalog": "Y",
    "display_on_header": "N",
    "comparison": "N",
    "description": {"feature_id": 7, "lang_code": "en", "name": "Spice Level", "description": null},
    "parent": null,
    "categories": [],
    "variants": [
      {"variant_id": 11, "feature_id": 7, "position": 1, "status": "A", "description": {"variant_id": 11, "lang_code": "en", "description": "Mild"}},
      {"variant_id": 12, "feature_id": 7, "position": 2, "status": "A", "description": {"variant_id": 12, "lang_code": "en", "description": "Hot"}}
    ]
  }
}
```

Update message: `Food feature updated successfully.`. For C/T/O, `variants` is `[]`.
Validation failures return 422 with an `errors` object, for example
`{"errors":{"variants":["Add at least two options."]},"message":"Add at least two options."}`.
Missing or foreign features return 404 through the existing controller.
