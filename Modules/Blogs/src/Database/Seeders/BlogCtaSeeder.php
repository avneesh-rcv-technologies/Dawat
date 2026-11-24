<?php

namespace Modules\Blogs\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Blogs\Models\BlogCta;

class BlogCtaSeeder extends Seeder
{
    public function run(): void
    {
        $accent = '#FF6210';
        $accentDark = '#E3550A';

        $urls = [
            'home' => route('website.home'),
            'blogs' => route('website.blogs'),
            'partner' => route('website.partner'),
            'howItWorks' => route('website.how-it-works'),
            'contact' => route('website.contact'),
            'about' => route('website.about'),
        ];

        $ctas = [
            [
                'name' => 'Discover restaurants',
                'slug' => 'discover-restaurants',
                'status' => true,
                'content' => $this->banner(
                    'Hungry for something new?',
                    'Browse nearby restaurants on Dawat and order dishes you will actually remember.',
                    'Explore restaurants',
                    $urls['home'],
                    $accent
                ),
            ],
            [
                'name' => 'Partner with Dawat',
                'slug' => 'partner-with-dawat',
                'status' => true,
                'content' => $this->banner(
                    'Grow your restaurant with Dawat',
                    'Reach more diners, manage orders smoothly, and put your menu in front of hungry customers.',
                    'Partner with us',
                    $urls['partner'],
                    $accentDark
                ),
            ],
            [
                'name' => 'How Dawat works',
                'slug' => 'how-dawat-works',
                'status' => true,
                'content' => $this->banner(
                    'From craving to doorstep',
                    'See how Dawat helps you discover restaurants, build the perfect order, and get food delivered fast.',
                    'See how it works',
                    $urls['howItWorks'],
                    $accent
                ),
            ],
            [
                'name' => 'Read more stories',
                'slug' => 'read-more-stories',
                'status' => true,
                'content' => $this->banner(
                    'More food stories from Dawat',
                    'Explore guides, restaurant tips, and culinary inspiration from our editorial team.',
                    'Browse blogs',
                    $urls['blogs'],
                    $accent
                ),
            ],
            [
                'name' => 'Talk to Dawat',
                'slug' => 'talk-to-dawat',
                'status' => true,
                'content' => $this->banner(
                    'Questions, ideas, or feedback?',
                    'Our team is here for diners and restaurant partners. Reach out and we will help you get started.',
                    'Contact us',
                    $urls['contact'],
                    $accentDark
                ),
            ],
            [
                'name' => 'About Dawat',
                'slug' => 'about-dawat',
                'status' => true,
                'content' => $this->banner(
                    'Built for celebrations and everyday cravings',
                    'Learn why Dawat exists and how we connect great restaurants with people who love food.',
                    'About us',
                    $urls['about'],
                    $accent
                ),
            ],
        ];

        foreach ($ctas as $cta) {
            BlogCta::updateOrCreate(
                ['slug' => $cta['slug']],
                $cta
            );
        }
    }

    protected function banner(string $title, string $text, string $buttonLabel, string $url, string $buttonColor): string
    {
        $url = e($url);
        $title = e($title);
        $text = e($text);
        $buttonLabel = e($buttonLabel);

        return <<<HTML
<div style="margin: 2.5rem 0; padding: 1.5rem 1.6rem; border-radius: 1rem; border: 1px solid rgba(255, 98, 16, 0.28); background: linear-gradient(135deg, #1a0d05 0%, #0b0b0b 100%); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem;">
  <div style="flex: 1 1 16rem;">
    <h3 style="margin: 0; color: #ffffff; font-size: 1.25rem; font-weight: 700; line-height: 1.35;">{$title}</h3>
    <p style="margin: 0.55rem 0 0; color: rgba(255,255,255,0.72); font-size: 0.95rem; line-height: 1.6;">{$text}</p>
  </div>
  <div style="flex: 0 0 auto;">
    <a href="{$url}" style="display: inline-flex; align-items: center; justify-content: center; background-color: {$buttonColor}; color: #ffffff; text-decoration: none; padding: 0.75rem 1.4rem; border-radius: 999px; font-weight: 600; font-size: 0.92rem; line-height: 1.2; box-shadow: 0 10px 24px rgba(255, 98, 16, 0.28);">{$buttonLabel}</a>
  </div>
</div>
HTML;
    }
}
