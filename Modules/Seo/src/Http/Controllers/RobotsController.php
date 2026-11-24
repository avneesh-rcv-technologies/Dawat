<?php

namespace Modules\Seo\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Seo\Services\RobotsBuilder;
use Symfony\Component\HttpFoundation\Response;

class RobotsController extends Controller
{
    public function __construct(
        protected RobotsBuilder $builder,
    ) {}

    public function index(): Response
    {
        return response($this->builder->build(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
