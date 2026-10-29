<?php

namespace Modules\Tax\View\Components;

use Illuminate\View\Component;
use Modules\FoodManagements\Models\FoodItem;

class FoodItemPrice extends Component
{
    public FoodItem $foodItem;
    public bool $showTaxInfo;
    public ?string $currency;
    public string $layout;
    public ?int $taxCategoryId;

    public function __construct(
        FoodItem $foodItem,
        bool $showTaxInfo = true,
        ?string $currency = null,
        string $layout = 'default',
        ?int $taxCategoryId = null
    ) {
        $this->foodItem = $foodItem;
        $this->showTaxInfo = $showTaxInfo;
        $this->currency = $currency;
        $this->layout = $layout;
        $this->taxCategoryId = $taxCategoryId;
    }

    public function render()
    {
        return view('tax::components.food-item-price');
    }
}