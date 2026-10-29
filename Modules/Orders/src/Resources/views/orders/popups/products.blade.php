<x-autocomplete 
    label=""
    field="items"
    table="products"
    value-field="id"
    search-fields="name"
    list-attributes="id,name"
    :multiple="true"
    :actions="[
        ['label' => 'Select', 'callback' => 'selectItem'],
    ]"
    :selected="!empty($product->categories) ?$product->categories->toArray() : []"
/>