<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;

class OrderItemOptionsService
{
    public function forItems(array $items): array
    {
        $selections = [];
        $optionIds = $groupIds = $foodIds = [];

        foreach ($items as $index => $item) {
            $options = $item['selected_options'] ?? [];
            if (is_string($options)) {
                $options = json_decode($options, true) ?: [];
            }
            $selections[$index] = $this->selections(is_array($options) ? $options : []);
            $foodIds[] = $item['item_id'] ?? $item['food_item_id'] ?? null;
            foreach ($selections[$index] as $selection) {
                $optionIds[] = $selection['option_id'];
                $groupIds[] = $selection['group_id'];
            }
        }

        $catalog = $this->loadCatalog(
            array_values(array_unique(array_filter($optionIds))),
            array_values(array_unique(array_filter($groupIds))),
            array_values(array_unique(array_filter($foodIds)))
        );

        $result = [];
        foreach ($selections as $index => $options) {
            $result[$index] = [];
            $foodId = $items[$index]['item_id'] ?? $items[$index]['food_item_id'] ?? null;
            foreach ($options as $selection) {
                $option = $catalog['options'][$selection['option_id']] ?? [];
                $name = $selection['name'] ?: ($option['name'] ?? null);
                $group = $selection['group_name'] ?: ($catalog['groups'][$selection['group_id']] ?? $option['group_name'] ?? null);
                $catalogPrice = $catalog['prices'][$foodId.':'.$selection['option_id']] ?? null;
                $result[$index][] = [
                    'name' => $name ? html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Selected option no longer available',
                    'group_name' => $group ? html_entity_decode($group, ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Options',
                    'quantity' => $selection['quantity'],
                    'price' => $selection['price'] ?? $catalogPrice,
                    'price_recorded' => $selection['price'] !== null,
                ];
            }
        }

        return $result;
    }

    private function selections(array $node, ?int $groupId = null, ?string $groupName = null, bool $optionNode = false): array
    {
        $groupId = $node['option_group_id'] ?? $node['food_option_group_id'] ?? $node['group_id'] ?? $groupId;
        $groupName = $node['group_name'] ?? $node['option_group_name'] ?? $groupName;

        if (isset($node['options'])) {
            $groupName = $node['name'] ?? $groupName;
        }

        $optionId = $node['option_id'] ?? ($optionNode ? ($node['id'] ?? null) : null);
        $name = $node['option_name'] ?? (!isset($node['options'], $node['groups']) ? ($node['name'] ?? null) : null);
        if ($optionId || ($name && !isset($node['options']) && !isset($node['groups']))) {
            return [[
                'option_id' => $optionId,
                'group_id' => $groupId,
                'group_name' => $groupName,
                'name' => $name,
                'price' => isset($node['price']) && is_numeric($node['price']) ? (float) $node['price'] : null,
                'quantity' => isset($node['quantity']) && is_numeric($node['quantity']) ? max(1, (int) $node['quantity']) : 1,
            ]];
        }

        $result = [];
        foreach ($node as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            foreach ($this->selections($value, $groupId, $groupName, $key === 'options' || ($optionNode && is_int($key))) as $selection) {
                $result[] = $selection;
            }
        }

        return $result;
    }

    protected function loadCatalog(array $optionIds, array $groupIds, array $foodIds): array
    {
        $catalog = ['options' => [], 'groups' => [], 'prices' => []];
        if ($optionIds) {
            $options = DB::table('food_options')
                ->leftJoin('food_option_groups', 'food_option_groups.id', '=', 'food_options.food_option_group_id')
                ->whereIn('food_options.id', $optionIds)
                ->get(['food_options.id', 'food_options.name', 'food_option_groups.name as group_name']);
            foreach ($options as $option) {
                $catalog['options'][$option->id] = ['name' => $option->name, 'group_name' => $option->group_name];
            }
            if ($foodIds) {
                foreach (DB::table('food_item_options')->whereIn('food_item_id', $foodIds)->whereIn('food_option_id', $optionIds)->get() as $price) {
                    $catalog['prices'][$price->food_item_id.':'.$price->food_option_id] = $price->price === null ? null : (float) $price->price;
                }
            }
        }
        if ($groupIds) {
            $catalog['groups'] = DB::table('food_option_groups')->whereIn('id', $groupIds)->pluck('name', 'id')->all();
        }

        return $catalog;
    }
}
