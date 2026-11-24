<?php

namespace Modules\DietaryManagment\Services;

use Modules\DietaryManagment\Models\CertificationType;
use Modules\DietaryManagment\Models\DietaryLabel;

class CertificationTypeSynchronizer
{
    public function sync(DietaryLabel $label): ?CertificationType
    {
        if (! $label->is_certification_required) {
            $label->certificationType()->update(['is_required_for_orders' => false, 'is_active' => false]);
            return null;
        }

        $type = $label->certificationType()->firstOrNew([]);
        $type->fill([
            'dietary_label_id' => $label->getKey(),
            'name' => $this->uniqueName($label->name.' certification', $type),
            'is_required_for_orders' => true,
            'is_active' => (bool) $label->is_active,
        ]);
        $type->save();

        return $type;
    }

    protected function uniqueName(string $base, CertificationType $type): string
    {
        $name = $base;
        for ($suffix = 2; CertificationType::where('name', $name)
            ->when($type->exists, fn ($query) => $query->where('id', '<>', $type->getKey()))->exists(); $suffix++) {
            $name = $base.' ('.$suffix.')';
        }

        return $name;
    }
}
