<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteCacheTest extends TestCase
{
    public function test_all_routes_have_unique_names_and_can_be_cached(): void
    {
        $routes = $this->app['router']->getRoutes();
        $names = [];

        foreach ($routes as $route) {
            if ($name = $route->getName()) {
                $this->assertArrayNotHasKey($name, $names, "Duplicate route name: {$name}");
                $names[$name] = $route->uri();
            }
        }

        $this->assertSame('admin/cms/pages', $names['cms.pages.index']);
        $this->assertSame('admin/mobile-configuration/cms', $names['mobileconfiguration.cms.index']);
        $this->assertSame('admin/reports', $names['reports.index']);
        $this->assertSame('cms/pages/{slug}', $names['api.cms.pages.show']);
        $this->assertSame('api/firebase/subscribe', $names['api.firebase.subscribe']);
        $this->assertSame('api/app/firebase/subscribe', $names['api.app.firebase.subscribe']);

        foreach ($routes as $route) {
            $route->prepareForSerialization();
        }

        $compiled = $routes->compile();
        $this->assertArrayHasKey('cms.pages.index', $compiled['attributes']);
        $this->assertIsString(serialize($compiled));
    }
}
