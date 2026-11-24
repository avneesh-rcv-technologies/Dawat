<?php

return [
    'name' => 'Seo',
    'version' => '1.0.0',

    'settings' => [
        'enabled' => true,
        'cache_sitemap' => true,
        'cache_ttl' => 3600,
    ],

    'pages' => [
        'home' => [
            'route' => 'website.home',
            'title' => 'Dawat — Where Every Celebration Becomes a Feast',
            'description' => 'Discover restaurants, explore delicious cuisines, and order your favourite meals with Dawat.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'WebSite'],
        ],
        'about' => [
            'route' => 'website.about',
            'title' => 'About Us — Dawat',
            'description' => 'Discover the story behind Dawat and a simpler way to find, order, and enjoy the food you love.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'AboutPage'],
        ],
        'how_it_works' => [
            'route' => 'website.how-it-works',
            'title' => 'How It Works — Dawat',
            'description' => 'At Dawat, we believe every gathering deserves great food. Here is how we bring restaurants
and bulk orders together, from the first menu you browse to the final delivery.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'WebPage'],
        ],
        'blogs_index' => [
            'route' => 'website.blogs',
            'title' => 'Blogs — Dawat',
            'description' => 'Explore food stories, restaurant inspiration, and the latest updates from Dawat.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'Blog'],
        ],
        'contact' => [
            'route' => 'website.contact',
            'title' => 'Contact Us — Dawat',
            'description' => 'Get in touch with the Dawat team for questions, support, and partnership enquiries.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'ContactPage'],
        ],
        'partner' => [
            'route' => 'website.partner',
            'title' => 'Partner With Us — Dawat',
            'description' => 'Grow your restaurant with Dawat and connect with customers who love great food.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'WebPage'],
        ],
        'terms' => [
            'route' => 'website.terms-and-conditions',
            'title' => 'Terms and Conditions — Dawat',
            'description' => 'Review the terms and conditions for using Dawat.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'WebPage'],
        ],
        'privacy' => [
            'route' => 'website.privacy-policy',
            'title' => 'Privacy Policy — Dawat',
            'description' => 'Learn how Dawat collects, uses, and protects your personal information.',
            'robots' => 'index,follow',
            'schema' => ['@type' => 'WebPage'],
        ],
        'partner_login' => [
            'route' => 'website.partner-login',
            'title' => 'Partner Login — Dawat',
            'description' => 'Log in to your Dawat restaurant partner account.',
            'robots' => 'noindex,follow',
            'schema' => ['@type' => 'WebPage'],
            'sitemap' => false,
        ],
        'partner_signup' => [
            'route' => 'website.partner-signup',
            'title' => 'Partner Sign Up — Dawat',
            'description' => 'Create a Dawat restaurant partner account and grow your business.',
            'robots' => 'noindex,follow',
            'schema' => ['@type' => 'WebPage'],
            'sitemap' => false,
        ],
    ],

    // Individual blog articles use their saved metadata instead of fixed page entries.
    // Signed draft previews stay outside the sitemap and use noindex response headers.
    'sources' => [
        'blog' => [
            'model' => \Modules\Blogs\Models\Blog::class,
            'route' => 'website.blog-detail',
            'route_param' => 'slug',
            'slug_column' => 'post_url',
            'title_column' => 'meta_title',
            'title_fallback' => 'title',
            'description_column' => 'meta_description',
            'description_fallback' => ['short_description', 'long_description'],
            'keywords_column' => 'meta_keywords',
            'image_column' => 'outer_image_url',
            'updated_column' => 'updated_at',
            'scope' => 'published',
            'og_type' => 'article',
            'schema_attribute' => 'decoded_schema',
            'schema' => [
                '@type' => 'BlogPosting',
                'headline' => '{title}',
                'datePublished' => '{published_at}',
                'dateModified' => '{updated_at}',
                'author' => [
                    '@type' => 'Person',
                    'name' => '{author_name}',
                ],
            ],
            'sitemap' => ['changefreq' => 'weekly', 'priority' => 0.7],
        ],
        'blog_author' => [
            'model' => \Modules\Blogs\Models\BlogAuthor::class,
            'route' => 'website.author-detail',
            'route_param' => 'slug',
            'slug_column' => 'slug',
            'title_column' => 'name',
            'title_fallback' => 'name',
            'description_fallback' => ['bio', 'designation'],
            'image_column' => 'avatar_url',
            'updated_column' => 'updated_at',
            'scope' => 'active',
            'og_type' => 'profile',
            'schema' => [
                '@type' => 'Person',
                'name' => '{title}',
                'description' => '{description}',
                'image' => '{image_url}',
            ],
            'sitemap' => ['changefreq' => 'weekly', 'priority' => 0.5],
        ],
    ],

    'sitemap' => [
        'path' => '/sitemap.xml',
        'enabled' => true,
        'include_static_pages' => true,
        'include_sources' => ['blog', 'blog_author'],
        'max_urls' => 50000,
    ],

    'robots' => [
        'path' => '/robots.txt',
        'enabled' => true,
        'user_agents' => [
            '*' => [
                'allow' => ['/'],
                'disallow' => [
                    '/admin',
                    '/admin/*',
                    '/api',
                    '/api/*',
                    '/settings',
                    '/seo',
                    '/faqs',
                    '/blogs/blogs',
                    '/blogs/blog_categories',
                    '/blogs/blog_authors',
                    '/blogs/blog_ctas',
                    '/blogs/preview/',
                    '/partner/login',
                    '/partner/sign-up',
                ],
            ],
        ],
        'sitemap' => '{app_url}/sitemap.xml',
    ],

    'redirects' => [
        'enabled' => true,
        'preserve_query' => true,
        'case_sensitive' => false,
        'trailing_slash' => 'ignore',
        'cache' => ['enabled' => true, 'ttl' => 3600],
        'static' => [],
    ],

    'analytics' => [
        'google_analytics' => [
            'enabled' => false,
            'measurement_id' => null,
            'anonymize_ip' => true,
        ],
        'google_tag_manager' => [
            'enabled' => false,
            'container_id' => null,
            'enabled_on_portal' => false,
        ],
        'facebook_pixel' => [
            'enabled' => false,
            'pixel_id' => null,
        ],
        'google_search_console' => [
            'enabled' => false,
            'verification_code' => null,
        ],
        'custom_head_scripts' => '',
        'custom_body_scripts' => '',
    ],

    'defaults' => [
        'site_name' => 'Dawat',
        'title_suffix' => '',
        'description' => 'Discover restaurants, explore delicious cuisines, and order your favourite meals with Dawat.',
        'robots' => 'index,follow',
        'og' => [
            'type' => 'website',
            'locale' => 'en_US',
            'image' => env('SEO_DEFAULT_SOCIAL_IMAGE'),
        ],
        'twitter' => [
            'card' => 'summary_large_image',
            'site' => null,
        ],
        'organization_schema' => [
            '@type' => 'Organization',
            'name' => '{site_name}',
            'url' => '{app_url}',
            'description' => '{description}',
        ],
    ],
];
