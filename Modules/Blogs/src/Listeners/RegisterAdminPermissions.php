<?php

namespace Modules\Blogs\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Blogs - Blogs
         */
        $event->registerArray([
            'name' => 'Blogs',
            'code' => 'blogs_blogs',
            'key' => [
                'admin.blogs.blogs.index' => 'View Blogs',
                'admin.blogs.blogs.create' => 'Create Blogs',
                'admin.blogs.blogs.store' => 'Store Blogs',
                'admin.blogs.blogs.show' => 'Show Blogs',
                'admin.blogs.blogs.edit' => 'Edit Blogs',
                'admin.blogs.blogs.update' => 'Update Blogs',
                'admin.blogs.blogs.destroy' => 'Delete Blogs',
                'admin.blogs.blogs.bulk-delete' => 'Bulk Delete Blogs',
                'admin.blogs.blogs.toggle-status' => 'Toggle Blog Status',
                'admin.blogs.blogs.preview' => 'Preview Blogs',
            ]
        ]);

        /**
         * Blogs - Blog Authors
         */
        $event->registerArray([
            'name' => 'Blogs',
            'code' => 'blogs_blog_authors',
            'key' => [
                'admin.blogs.blog_authors.index' => 'View Blog Authors',
                'admin.blogs.blog_authors.create' => 'Create Blog Authors',
                'admin.blogs.blog_authors.store' => 'Store Blog Authors',
                'admin.blogs.blog_authors.show' => 'Show Blog Authors',
                'admin.blogs.blog_authors.edit' => 'Edit Blog Authors',
                'admin.blogs.blog_authors.update' => 'Update Blog Authors',
                'admin.blogs.blog_authors.destroy' => 'Delete Blog Authors',
                'admin.blogs.blog_authors.bulk-delete' => 'Bulk Delete Blog Authors',
                'admin.blogs.blog_authors.toggle-status' => 'Toggle Blog Author Status',
            ]
        ]);

        /**
         * Blogs - Blog CTAs
         */
        $event->registerArray([
            'name' => 'Blogs',
            'code' => 'blogs_blog_ctas',
            'key' => [
                'admin.blogs.blog_ctas.index' => 'View Blog CTAs',
                'admin.blogs.blog_ctas.create' => 'Create Blog CTAs',
                'admin.blogs.blog_ctas.store' => 'Store Blog CTAs',
                'admin.blogs.blog_ctas.show' => 'Show Blog CTAs',
                'admin.blogs.blog_ctas.edit' => 'Edit Blog CTAs',
                'admin.blogs.blog_ctas.update' => 'Update Blog CTAs',
                'admin.blogs.blog_ctas.destroy' => 'Delete Blog CTAs',
                'admin.blogs.blog_ctas.bulk-delete' => 'Bulk Delete Blog CTAs',
                'admin.blogs.blog_ctas.toggle-status' => 'Toggle Blog CTA Status',
            ]
        ]);

        /**
         * Blogs - Blog Categories
         */
        $event->registerArray([
            'name' => 'Blogs',
            'code' => 'blogs_blog_categories',
            'key' => [
                'admin.blogs.blog_categories.index' => 'View Blog Categories',
                'admin.blogs.blog_categories.create' => 'Create Blog Categories',
                'admin.blogs.blog_categories.store' => 'Store Blog Categories',
                'admin.blogs.blog_categories.show' => 'Show Blog Categories',
                'admin.blogs.blog_categories.edit' => 'Edit Blog Categories',
                'admin.blogs.blog_categories.update' => 'Update Blog Categories',
                'admin.blogs.blog_categories.destroy' => 'Delete Blog Categories',
                'admin.blogs.blog_categories.bulk-delete' => 'Bulk Delete Blog Categories',
                'admin.blogs.blog_categories.toggle-status' => 'Toggle Blog Category Status',
            ]
        ]);

    }
}