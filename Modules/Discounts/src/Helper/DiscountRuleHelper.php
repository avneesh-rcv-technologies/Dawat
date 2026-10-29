<?php

use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Category;

if(!function_exists('fn_target_name')) {
function fn_target_name($rule): string
{
    switch ($rule->rule_type) {
        case 'product':
            $product = Product::find($rule->rule_id);
            return $product ? $product->name : 'Unknown Product';

        case 'category':
            $category = Category::find($rule->rule_id);
            return $category ? $category->name : 'Unknown Category';

        case 'subtotal':
            return 'Cart Subtotal';

        case 'quantity':
            return 'Cart Quantity';

        default:
            return 'Unknown Target';
    }
}

}