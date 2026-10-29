<?php

namespace Modules\Acl\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard' => $this->guard,
            'description' => $this->description,
            'permission_type' => $this->permission_type,
            'permissions' => $this->when($this->permission_type === 'custom', $this->permissions),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
