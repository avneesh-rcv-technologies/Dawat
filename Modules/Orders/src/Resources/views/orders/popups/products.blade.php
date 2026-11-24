<x-advanced-selector
    label=""
    name="items"
    table="products"
    value-column="id"
    search-column="name"
    display-column="name"
    :multiple="true"
    :selected="!empty($product->categories) ? $product->categories->pluck('id')->all() : []"
/>
