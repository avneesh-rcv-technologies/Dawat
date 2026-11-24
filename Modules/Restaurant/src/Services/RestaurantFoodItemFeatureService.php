<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Models\FoodFeatureValue;
use Modules\FoodManagements\Models\FoodItem;

class RestaurantFoodItemFeatureService
{
    /** Validate the web form's feature_id => value(s) mapping and produce typed rows. */
    public function normalize(int $restaurantId, array $mapping): array
    {
        $ids = [];
        foreach (array_keys($mapping) as $id) {
            if (! ctype_digit((string) $id) || (int) $id < 1 || in_array((int) $id, $ids, true)) {
                throw ValidationException::withMessages(['features' => 'Use distinct feature IDs as keys.']);
            }
            $ids[] = (int) $id;
        }
        if ($ids === []) {
            return [];
        }
        $features = $this->activeFeatures($restaurantId)->whereIn('feature_id', $ids)
            ->with(['variants' => fn ($query) => $query->where('status', 'A')])->get()->keyBy('feature_id');
        $rows = [];
        $errors = [];
        foreach ($mapping as $id => $value) {
            $feature = $features->get((int) $id);
            $field = 'features.'.$id;
            if (! $feature) {
                $errors[$field] = 'Choose an active feature belonging to your restaurant.';
                continue;
            }
            if ($value === null || (is_string($value) && trim($value) === '') || $value === []) {
                $errors[$field] = 'Choose or enter a value for this feature, or remove it from the item.';
                continue;
            }
            $base = ['feature_id' => (int) $id, 'variant_id' => null, 'value' => '', 'value_int' => null, 'lang_code' => 'en'];
            $type = $feature->feature_type;
            if (in_array($type, ['S', 'M'], true)) {
                $values = $type === 'M' ? $value : [$value];
                if (! is_array($values) || ! array_is_list($values) || count($values) > 50) {
                    $errors[$field] = $type === 'M' ? 'Select a list of option IDs.' : 'Select one option ID.';
                    continue;
                }
                $selected = [];
                foreach ($values as $variantId) {
                    if ((! is_int($variantId) && ! is_string($variantId)) || ! ctype_digit((string) $variantId)
                        || ! $feature->variants->contains('variant_id', (int) $variantId)
                        || in_array((int) $variantId, $selected, true)) {
                        $errors[$field] = 'Choose distinct active options belonging to this feature.';
                        break;
                    }
                    $selected[] = (int) $variantId;
                    $rows[] = array_replace($base, ['variant_id' => (int) $variantId]);
                }
            } elseif ($type === 'C') {
                $normalized = is_bool($value) ? ($value ? 'y' : 'n') : (is_scalar($value) ? strtolower((string) $value) : '');
                if (! in_array($normalized, ['y', 'n', 'yes', 'no', '1', '0', 'true', 'false'], true)) {
                    $errors[$field] = 'Choose Yes or No.';
                    continue;
                }
                $rows[] = array_replace($base, ['value' => in_array($normalized, ['y', 'yes', '1', 'true'], true) ? 'Y' : 'N']);
            } elseif ($type === 'T') {
                if (! is_string($value) || mb_strlen($value) > 255) {
                    $errors[$field] = 'Enter text up to 255 characters.';
                    continue;
                }
                // Numeric-looking text is still text, never an option ID or number.
                $rows[] = array_replace($base, ['value' => $value]);
            } elseif ($type === 'O') {
                if (Validator::make(['value' => $value], ['value' => ['numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99']])->fails()) {
                    $errors[$field] = 'Enter a number with up to 2 decimal places.';
                    continue;
                }
                $rows[] = array_replace($base, ['value' => (string) $value, 'value_int' => number_format((float) $value, 2, '.', '')]);
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $rows;
    }

    /** Called inside the V2 item transaction, after its restaurant/item locks. */
    public function save(FoodItem $item, array $rows): void
    {
        // Match the web UI: replace editable active assignments, retaining hidden ones.
        FoodFeatureValue::where('food_item_id', $item->id)
            ->whereIn('feature_id', $this->activeFeatures((int) $item->restaurant_id)->select('feature_id'))->delete();
        foreach ($rows as $row) {
            FoodFeatureValue::create(['food_item_id' => $item->id, ...$row]);
        }
    }

    private function activeFeatures(int $restaurantId)
    {
        return FoodFeature::forRestaurant($restaurantId)->where('status', 'A')->whereIn('feature_type', ['S', 'M', 'C', 'T', 'O']);
    }
}
