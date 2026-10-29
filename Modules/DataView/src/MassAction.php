<?php

namespace Modules\DataView;

/**
 * Initial implementation of the mass action class. Stay tuned for more features coming soon.
 */
class MassAction
{
    /**
     * Create a column instance.
     */
    public function __construct(
        public string $icon,
        public string $title,
        public string $method,
        public mixed $url,
        public string $action,
        public bool $is_popup,
        public array $options = [],
    ) {}

    /**
     * Convert to an array.
     */
    public function toArray()
    {
        return [
            'icon'    => $this->icon,
            'title'   => $this->title,
            'method'  => $this->method,
            'url'     => $this->url,
            'action'     => $this->action,
            'is_popup'     => $this->is_popup,
            'options' => $this->options,
        ];
    }
}
