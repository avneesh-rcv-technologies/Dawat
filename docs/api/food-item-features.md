# Food item features in listing responses

These GET endpoints include a `features` array inside each food item in `data`:

- `/api/v2/restaurant/food-items`
- `/api/menu/categories/{categoryId}/items`
- `/api/menu/dietary-labels/items`

Existing query parameters and pagination remain supported. The restaurant endpoint
requires its existing owner authentication. No new request fields are needed.

Example feature entry:

```json
{
  "feature_id": 7,
  "name": "Spice Level",
  "feature_type": "S",
  "feature_style": "text",
  "filter_style": "checkbox",
  "purpose": "find_products",
  "status": "A",
  "position": 1,
  "values": [
    {
      "variant_id": 11,
      "variant_name": "Mild",
      "value": "",
      "value_int": null
    }
  ]
}
```

Each feature appears once. Multiple-choice features contain multiple selected
entries in `values`. Text and Yes/No use the stored `value` string; numeric features
also include the stored decimal string in `value_int`. Values without variants have
`variant_id` and `variant_name` set to null. No assigned features returns `[]`.

Feature types: `S` single choice, `M` multiple choice, `C` Yes/No, `T` text,
`O` number. Only assigned values are returned, rather than all available options.

Public menus include active features marked `display_on_product = Y`, and active
selected variants. Owners can also see hidden/disabled assignments. Features from
other restaurants, missing features, and missing/mismatched variants are excluded.

The shared serializers also expose this field in V2 item detail/write responses
and public menu tag listings.
