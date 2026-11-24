<?php

namespace Modules\Seo\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Seo\Services\SitemapBuilder;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapBuilder $builder,
    ) {}

    public function index(): Response
    {
        return response($this->builder->build(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
