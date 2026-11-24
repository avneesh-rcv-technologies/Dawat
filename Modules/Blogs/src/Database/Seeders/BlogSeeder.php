<?php

namespace Modules\Blogs\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\Blogs\Models\Blog;
use Modules\Blogs\Models\BlogAuthor;
use Modules\Blogs\Models\BlogCategory;
use Illuminate\Http\UploadedFile;

class BlogSeeder extends Seeder
{
    /**
     * Seed five authors, categories, and ten Dawat blogs with real content.
     * Cover/avatar images reuse files already shipped under public/website/images.
     */
    public function run(): void
    {
        $authors = $this->seedAuthors();
        $categories = $this->seedCategories();
        $this->seedBlogs($authors, $categories);
    }

    /**
     * @return array<string, BlogAuthor>
     */
    protected function seedAuthors(): array
    {
        $authors = [
            [
                'slug' => 'manjinder-singh',
                'name' => 'Manjinder Singh',
                'designation' => 'Editor-in-Chief, Food Culture',
                'bio' => 'Manjinder writes about hospitality, regional Indian flavours, and the small details that turn a meal into a memory. He has spent a decade interviewing chefs, hosts, and diners across the UK.',
                'email' => 'manjinder@dawat.example',
                'twitter_url' => 'https://twitter.com/dawat',
                'linkedin_url' => 'https://www.linkedin.com/',
                'instagram_url' => 'https://www.instagram.com/',
                'website_url' => url('/about'),
                'avatar' => 'website/images/Blog-details/Ellipse 58.png',
            ],
            [
                'slug' => 'aisha-khan',
                'name' => 'Aisha Khan',
                'designation' => 'Culinary Storyteller',
                'bio' => 'Aisha explores home kitchens, street food legends, and modern restaurant menus. Her work focuses on recipes people actually cook and the stories behind every spice blend.',
                'email' => 'aisha@dawat.example',
                'twitter_url' => 'https://twitter.com/dawat',
                'linkedin_url' => 'https://www.linkedin.com/',
                'instagram_url' => 'https://www.instagram.com/',
                'facebook_url' => 'https://www.facebook.com/',
                'website_url' => url('/blogs'),
                'avatar' => 'website/images/about/girl.png',
            ],
            [
                'slug' => 'rohan-mehta',
                'name' => 'Rohan Mehta',
                'designation' => 'Restaurant Partnerships Writer',
                'bio' => 'Rohan covers how restaurants grow on delivery platforms, from menu design to peak-hour operations. He works closely with Dawat partner kitchens across the country.',
                'email' => 'rohan@dawat.example',
                'linkedin_url' => 'https://www.linkedin.com/',
                'youtube_url' => 'https://www.youtube.com/',
                'website_url' => url('/partner'),
                'avatar' => 'website/images/Blog-details/Ellipse 58.png',
            ],
            [
                'slug' => 'priya-sharma',
                'name' => 'Priya Sharma',
                'designation' => 'Food Trends Correspondent',
                'bio' => 'Priya tracks what diners are craving next — from healthier comfort food to festive menus and late-night classics. She believes every city has a flavour story worth telling.',
                'email' => 'priya@dawat.example',
                'twitter_url' => 'https://twitter.com/dawat',
                'instagram_url' => 'https://www.instagram.com/',
                'linkedin_url' => 'https://www.linkedin.com/',
                'website_url' => url('/'),
                'avatar' => 'website/images/about/cravings-woman.png',
            ],
            [
                'slug' => 'daniel-brooks',
                'name' => 'Daniel Brooks',
                'designation' => 'Delivery Experience Editor',
                'bio' => 'Daniel writes practical guides for ordering smarter, tipping thoughtfully, and getting hot food to the table without the stress. He is obsessed with packaging, timing, and great customer care.',
                'email' => 'daniel@dawat.example',
                'linkedin_url' => 'https://www.linkedin.com/',
                'twitter_url' => 'https://twitter.com/dawat',
                'website_url' => url('/how-it-works'),
                'avatar' => 'website/images/Blog-details/Ellipse 58.png',
            ],
        ];

        $map = [];

        foreach ($authors as $data) {
            $avatarPath = $data['avatar'] ?? null;
            unset($data['avatar']);

            $author = BlogAuthor::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'status' => true,
                    'avatar' => null,
                    'facebook_url' => $data['facebook_url'] ?? null,
                    'youtube_url' => $data['youtube_url'] ?? null,
                ])
            );

            if ($avatarPath) {
                $this->uploadImage(
                    public_path($avatarPath),
                    basename($avatarPath),
                    'blog_author_avatar',
                    $author->id
                );
            }

            $map[$author->slug] = $author->fresh();
        }

        return $map;
    }

    /**
     * @return array<string, BlogCategory>
     */
    protected function seedCategories(): array
    {
        $items = [
            [
                'slug' => 'restaurant',
                'name' => 'Restaurant',
                'description' => 'Stories from kitchens, hosts, and dining rooms that shape great restaurant experiences.',
                'position' => 1,
                'image' => 'website/images/restaurants/spice-palace-comp.jpg',
            ],
            [
                'slug' => 'food-culture',
                'name' => 'Food Culture',
                'description' => 'Traditions, regional flavours, and the culture behind everyday meals.',
                'position' => 2,
                'image' => 'website/images/categories/entrees.jpg',
            ],
            [
                'slug' => 'delivery-guides',
                'name' => 'Delivery Guides',
                'description' => 'Practical tips for ordering, packaging, and getting food delivered at its best.',
                'position' => 3,
                'image' => 'website/images/categories/appetizers.jpg',
            ],
            [
                'slug' => 'lifestyle',
                'name' => 'Lifestyle',
                'description' => 'Hosting ideas, weeknight habits, and food moments worth celebrating.',
                'position' => 4,
                'image' => 'website/images/categories/desserts.jpg',
            ],
            [
                'slug' => 'recipes-inspiration',
                'name' => 'Recipes & Inspiration',
                'description' => 'Dish ideas, menu inspiration, and flavour combinations to try next.',
                'position' => 5,
                'image' => 'website/images/food-items/butter-chicken.jpg',
            ],
        ];

        $categories = [];

        foreach ($items as $item) {
            $category = BlogCategory::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'position' => $item['position'],
                    'status' => 1,
                    'meta_title' => $item['name'] . ' | Dawat Blog',
                    'meta_description' => $item['description'],
                    'meta_keywords' => 'dawat, food, ' . strtolower($item['name']),
                    'banner_path' => null,
                ]
            );

            $this->uploadImage(
                public_path($item['image']),
                basename($item['image']),
                'blog_category_banner',
                $category->id
            );

            $categories[$item['slug']] = $category->fresh();
        }

        return $categories;
    }

    /**
     * @param  array<string, BlogAuthor>  $authors
     * @param  array<string, BlogCategory>  $categories
     */
    protected function seedBlogs(array $authors, array $categories): void
    {
        $posts = $this->posts();

        foreach ($posts as $index => $post) {
            $author = $authors[$post['author']] ?? reset($authors);
            $publishedAt = Carbon::now()->subDays($post['days_ago'])->setTime(10, 30);

            $blog = Blog::updateOrCreate(
                ['post_url' => $post['slug']],
                [
                    'title' => $post['title'],
                    'short_description' => $post['short'],
                    'long_description' => $post['body'],
                    'author_name' => $author->name,
                    'author_id' => $author->id,
                    'meta_title' => $post['title'] . ' | Dawat Blog',
                    'meta_description' => $post['short'],
                    'meta_keywords' => $post['keywords'],
                    'status' => 'published',
                    'published_at' => $publishedAt,
                    'scheduled_at' => null,
                    'scheduled_timezone' => null,
                    'sort_order' => $index + 1,
                    'inner_image' => null,
                    'outer_image' => null,
                ]
            );

            $categoryIds = collect($post['categories'])
                ->map(fn(string $slug) => $categories[$slug]->id ?? null)
                ->filter()
                ->values()
                ->all();

            $blog->categories()->sync($categoryIds);

            $this->uploadImage(
                public_path($post['inner']),
                basename($post['inner']),
                'blog_inner_image',
                $blog->id
            );

            if (
                $this->uploadImage(
                    public_path($post['outer']),
                    basename($post['outer']),
                    'blog_outer_image',
                    $blog->id
                )
            ) {
                $blog->update(['outer_image' => $blog->fresh()->outer_image_url]);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function posts(): array
    {
        return [
            [
                'title' => 'The Art of Dining: How Restaurants Create Memorable Food Experiences',
                'slug' => 'the-art-of-dining-how-restaurants-create-memorable-food-experiences',
                'author' => 'manjinder-singh',
                'categories' => ['restaurant', 'food-culture'],
                'short' => 'Discover how great restaurants combine delicious food, quality service, welcoming ambience, and hospitality to create unforgettable dining experiences.',
                'keywords' => 'dining experience, restaurants, hospitality, dawat',
                'days_ago' => 2,
                'outer' => 'website/images/restaurants/spice-palace-comp.jpg',
                'inner' => 'website/images/Blog-details/Rectangle 161125084.png',
                'body' => <<<'HTML'
<div class="blog-introduction"><p>Discover how great restaurants combine delicious food, quality service, welcoming ambience, and hospitality to create unforgettable dining experiences.</p></div>
<p>A memorable meal rarely happens by accident. It is the result of hundreds of small choices — from the first greeting at the door to the way a dish arrives at the table. Great restaurants treat every detail as part of the story they are telling.</p>
<h2>Food that feels intentional</h2>
<p>Flavour is the foundation, but intention is what guests remember. Chefs who season with care, plate with balance, and source with pride give diners a reason to return. Whether it is a slow-cooked curry or a perfectly crisp starter, the dish should feel considered rather than rushed.</p>
<p>On Dawat, the restaurants that stand out usually share one habit: they protect quality even when orders get busy. Consistency turns a good plate into a trusted favourite.</p>
{{discover-restaurants}}
<h2>Service that makes people feel seen</h2>
<p>Hospitality is more than politeness. It is anticipation — noticing when a table needs water, explaining a special with confidence, or packing an order so the food still tastes restaurant-fresh at home. Guests remember how they felt long after they forget the exact menu wording.</p>
<ul>
  <li>Warm, clear communication from the first interaction</li>
  <li>Honest recommendations instead of hard selling</li>
  <li>Careful packaging and timely delivery for takeaway orders</li>
</ul>
<h2>Ambience and the invisible extras</h2>
<p>Lighting, music, aroma, and pacing all shape appetite. Even for delivery, presentation still matters: sealed containers, clear labelling, and a thoughtful napkin or sauce portion can elevate an ordinary night in.</p>
<h2>Hospitality as a habit</h2>
<p>The best dining rooms train hospitality as a system, not a mood. Scripts are useful, but sincerity is better. When a team is aligned around guest comfort, every course — and every delivery bag — carries the same promise.</p>
{{partner-with-dawat}}
<p><strong>Final takeaway:</strong> memorable food experiences are built from flavour, service, atmosphere, and care working together. That is the standard Dawat looks for when helping diners discover restaurants worth celebrating.</p>
HTML,
            ],
            [
                'title' => 'How to Build a Weeknight Dinner Habit With Delivery',
                'slug' => 'weeknight-dinner-habit-with-delivery',
                'author' => 'daniel-brooks',
                'categories' => ['delivery-guides', 'lifestyle'],
                'short' => 'Turn chaotic evenings into calm dinners with a simple ordering routine that saves time without sacrificing flavour.',
                'keywords' => 'weeknight dinner, food delivery, dawat ordering tips',
                'days_ago' => 5,
                'outer' => 'website/images/young-people-enjoying-street-food 1.png',
                'inner' => 'website/images/food-items/butter-chicken.jpg',
                'body' => <<<'HTML'
<div class="blog-introduction"><p>Turn chaotic evenings into calm dinners with a simple ordering routine that saves time without sacrificing flavour.</p></div>
<p>Weeknights often fail at the same point: decision fatigue. By the time everyone is hungry, choosing a meal feels harder than cooking one. A light routine can fix that.</p>
<h2>Create a shortlist, not an open tab</h2>
<p>Save three to five restaurants you already trust. Rotate cuisines across the week — Indian comfort one night, lighter bowls the next, a shared thali when the household wants variety. Fewer options usually mean faster, happier orders.</p>
<ol>
  <li>Pick a default restaurant for busy Mondays.</li>
  <li>Keep one “celebration” spot for midweek wins.</li>
  <li>Leave Friday open for something new on Dawat.</li>
</ol>
{{how-dawat-works}}
<h2>Order for reheating, not just arrival</h2>
<p>Choose dishes that travel well: curries, rice, flatbreads, grilled proteins, and desserts that hold temperature. Ask for sauces on the side when packaging might soften crisp textures.</p>
<h2>Set a household ordering window</h2>
<p>Agree on a cut-off time. When orders land earlier, food arrives before energy crashes and everyone is more likely to sit down together.</p>
<p>A good delivery habit is not about eating out every night. It is about protecting weeknight calm with food you look forward to.</p>
{{discover-restaurants}}
HTML,
            ],
            [
                'title' => 'Spice, Balance, and Memory: What Makes Indian Comfort Food Special',
                'slug' => 'what-makes-indian-comfort-food-special',
                'author' => 'aisha-khan',
                'categories' => ['food-culture', 'recipes-inspiration'],
                'short' => 'From layered spices to slow sauces, Indian comfort food earns its place through balance, warmth, and deeply personal memories.',
                'keywords' => 'indian comfort food, spices, curry, dawat',
                'days_ago' => 8,
                'outer' => 'website/images/restaurants/curry-house-comp.jpg',
                'inner' => 'website/images/food-items/rogan-josh-ent-22.jpg',
                'body' => <<<'HTML'
<p>Comfort food is not only about richness. In Indian kitchens, comfort often comes from balance — heat against creaminess, tang against sweetness, crisp against soft.</p>
<h2>Spices that build in layers</h2>
<p>Whole spices bloom in oil. Ground spices deepen sauces. Fresh herbs finish the plate. When restaurants respect that sequence, even a familiar butter chicken tastes clearer and more aromatic.</p>
<p>That is why diners can taste the difference between a rushed gravy and one that has been given time.</p>
<h2>Textures that invite another bite</h2>
<p>Think soft naan tearing through a silky sauce, biryani grains that stay separate, or pakoras that stay crunchy until the last dunk in chutney. Texture memory is powerful — people reorder the dishes that feel right in the hand and on the tongue.</p>
{{discover-restaurants}}
<h2>Memory is the secret ingredient</h2>
<p>A plate becomes comfort when it reminds someone of home, a celebration, or a late-night craving after a long day. Restaurants that honour regional recipes while serving modern diners create that bridge.</p>
<p>On Dawat, exploring Indian comfort food is less about finding “the spiciest” dish and more about finding the one that tastes like it was made for you.</p>
{{read-more-stories}}
HTML,
            ],
            [
                'title' => 'Menu Design Tips for Restaurants Joining a Delivery Platform',
                'slug' => 'menu-design-tips-for-delivery-platforms',
                'author' => 'rohan-mehta',
                'categories' => ['restaurant', 'delivery-guides'],
                'short' => 'Help diners choose faster and order more confidently with a delivery menu that photographs well, travels well, and reads clearly.',
                'keywords' => 'restaurant menu, delivery platform, partner restaurants, dawat',
                'days_ago' => 11,
                'outer' => 'website/images/restaurants/royal-feast-comp.jpg',
                'inner' => 'website/images/food-items/lucknowi-biryani-ent-25.jpg',
                'body' => <<<'HTML'
<div class="blog-introduction"><p>Help diners choose faster and order more confidently with a delivery menu that photographs well, travels well, and reads clearly.</p></div>
<p>A dine-in menu and a delivery menu are cousins, not twins. Online, guests cannot ask a waiter for clarification in the same way, so clarity becomes part of hospitality.</p>
<h2>Lead with your signatures</h2>
<p>Put your best-sellers and most photogenic dishes near the top. Diners skim. If your strongest plates are buried under seasonal experiments, conversion suffers.</p>
<h2>Write for scanning</h2>
<ul>
  <li>Use short, concrete dish names</li>
  <li>Mention key ingredients and heat level</li>
  <li>Call out vegetarian, vegan, and allergen notes early</li>
  <li>Avoid internal kitchen jargon guests will not recognise</li>
</ul>
{{partner-with-dawat}}
<h2>Design for the journey home</h2>
<p>Some dishes lose magic in a box. Prioritise items that reheat cleanly, hold sauce well, and still look inviting when unpacked. Offer combo suggestions so guests build complete meals without guesswork.</p>
<h2>Keep packaging notes in the kitchen brief</h2>
<p>Train the team on sealing, portioning sauces, and separating crisp items. Menu promises are kept in the bag as much as on the plate.</p>
<p>Restaurants that treat delivery as a first-class channel — not an afterthought — earn better reviews and steadier repeat orders on Dawat.</p>
{{talk-to-dawat}}
HTML,
            ],
            [
                'title' => 'Host a Feast at Home Without Cooking All Day',
                'slug' => 'host-a-feast-at-home-without-cooking-all-day',
                'author' => 'priya-sharma',
                'categories' => ['lifestyle', 'food-culture'],
                'short' => 'Plan a generous home gathering with restaurant-quality dishes, smart timing, and a hosting flow that still lets you enjoy the room.',
                'keywords' => 'hosting dinner, feast at home, catering style ordering, dawat',
                'days_ago' => 14,
                'outer' => 'website/images/restaurants/flavor-garden-comp.jpg',
                'inner' => 'website/images/food-items/gulab-jamun.jpg',
                'body' => <<<'HTML'
<p>Hosting should feel generous, not exhausting. With the right restaurant partners, you can build a feast that looks abundant while leaving you free to greet guests.</p>
<h2>Start with a sharing map</h2>
<p>Think in courses rather than individual plates: one crisp starter, two mains with different spice profiles, one carb, one cooling side, and a dessert that can sit for a while. Balance heat and richness so every guest finds a favourite.</p>
<ol>
  <li>Starters ready as people arrive</li>
  <li>Mains landing just before everyone sits</li>
  <li>Dessert delayed until conversation peaks</li>
</ol>
{{discover-restaurants}}
<h2>Stage the table before the food arrives</h2>
<p>Set bowls, serving spoons, water, and napkins early. When delivery lands, you are plating and presenting — not scrambling for cutlery.</p>
<h2>Choose restaurants that package for sharing</h2>
<p>Look for kitchens that send family portions, clear labels, and sauces separately. That small operational care makes hosting smoother and food more beautiful on the table.</p>
<p>A great home feast is not about proving you cooked everything. It is about gathering people around flavour, warmth, and ease — exactly what Dawat is built to support.</p>
{{about-dawat}}
HTML,
            ],
            [
                'title' => 'The Rise of Healthier Comfort: Lighter Dishes Diners Still Crave',
                'slug' => 'healthier-comfort-food-diners-crave',
                'author' => 'priya-sharma',
                'categories' => ['food-culture', 'recipes-inspiration'],
                'short' => 'Comfort food is evolving. Diners still want warmth and satisfaction — just with brighter vegetables, smarter portions, and cleaner finishes.',
                'keywords' => 'healthy comfort food, lighter menus, food trends, dawat',
                'days_ago' => 17,
                'outer' => 'website/images/categories/entrees.jpg',
                'inner' => 'website/images/food-items/hariyali-tikka.jpg',
                'body' => <<<'HTML'
<p>Comfort used to mean heavy by default. Today’s diners still chase nostalgia, but many want meals that leave them energised rather than sluggish.</p>
<h2>What “lighter” actually means on a menu</h2>
<p>It is not about removing joy. It is about technique: grilling instead of deep frying when it suits the dish, using yogurt-based marinades, finishing with herbs and citrus, and offering sensible portion options.</p>
<ul>
  <li>Herb-led marinades and tandoor-style cooking</li>
  <li>Vegetable-forward sides that still feel indulgent</li>
  <li>Whole grains and pulses alongside classic breads</li>
</ul>
{{read-more-stories}}
<h2>Restaurants winning this trend</h2>
<p>The menus that resonate explain the benefit without sounding clinical. “Chargrilled, lemon-finished, served with mint yogurt” tells a better story than “low calorie.”</p>
<p>Dawat diners are proving that cravings and care can share the same order — especially when restaurants present healthier comfort as delicious first.</p>
{{discover-restaurants}}
HTML,
            ],
            [
                'title' => 'Packaging Matters: How Great Takeaway Food Survives the Journey',
                'slug' => 'packaging-matters-for-takeaway-food',
                'author' => 'daniel-brooks',
                'categories' => ['delivery-guides', 'restaurant'],
                'short' => 'Temperature, texture, and presentation all depend on packaging. Here is what restaurants and diners should look for on every delivery.',
                'keywords' => 'food packaging, takeaway quality, delivery tips, dawat',
                'days_ago' => 20,
                'outer' => 'website/images/restaurants/masala-kitchen-comp.jpg',
                'inner' => 'website/images/food-items/samosa-app-2.jpg',
                'body' => <<<'HTML'
<div class="blog-introduction"><p>Temperature, texture, and presentation all depend on packaging. Here is what restaurants and diners should look for on every delivery.</p></div>
<p>A brilliant kitchen can still lose a review if food arrives soggy, spilled, or lukewarm. Packaging is part of the recipe now.</p>
<h2>Separate what should stay crisp</h2>
<p>Fried starters, papad, and some breads need space and breathability. Sauces belong in secure cups. When everything is stacked into one sealed tray, steam becomes the enemy of texture.</p>
<h2>Protect heat without trapping misery</h2>
<p>Insulated bags help, but vents and sturdy containers matter too. Rice should stay fluffy, curries should stay sealed, and desserts should not melt into the main course.</p>
{{how-dawat-works}}
<h2>Clear labels reduce mistakes</h2>
<p>Allergy notes, spice levels, and “open first” stickers help households unpack smoothly. It is a small operational habit with a big guest-experience payoff.</p>
<p>Diners can help by adding delivery notes and ordering dishes that travel well. Restaurants that invest in packaging send a clear message: the meal is not finished until it reaches your table.</p>
{{partner-with-dawat}}
HTML,
            ],
            [
                'title' => 'Street Food Energy, Restaurant Polish: Why Diners Love Both',
                'slug' => 'street-food-energy-restaurant-polish',
                'author' => 'aisha-khan',
                'categories' => ['food-culture', 'lifestyle'],
                'short' => 'The best modern menus borrow street-food boldness and restaurant refinement — giving diners excitement without losing craft.',
                'keywords' => 'street food, restaurant dining, modern menus, dawat',
                'days_ago' => 23,
                'outer' => 'website/images/enjoying-street-food-fest1.png',
                'inner' => 'website/images/food-items/aloo-tikki.jpg',
                'body' => <<<'HTML'
<p>Street food thrives on immediacy: smoke, spice, speed, and the joy of eating with your hands. Restaurants bring pacing, plating, and consistency. When the two meet, menus become irresistible.</p>
<h2>Borrow the boldness</h2>
<p>Chaat-inspired starters, charcoal notes, punchy chutneys, and snackable formats give restaurant menus energy. Guests recognise the flavours and feel the excitement of the street in a more comfortable setting — including at home via delivery.</p>
<h2>Keep the polish</h2>
<p>Restaurant craft shows up in cleaner cuts, balanced seasoning, and reliable portioning. That polish is what makes a dish repeatable and review-worthy on a platform like Dawat.</p>
{{discover-restaurants}}
<h2>Why this hybrid wins</h2>
<p>Diners want nostalgia and novelty at once. A refined aloo tikki or elevated seekh kebab delivers both: familiar comfort with a sense of occasion.</p>
<p>Whether you are craving a late-night snack or planning a Friday feast, look for kitchens that respect street roots without losing restaurant standards.</p>
{{read-more-stories}}
HTML,
            ],
            [
                'title' => 'A Practical Guide to Ordering for Mixed Dietary Preferences',
                'slug' => 'ordering-for-mixed-dietary-preferences',
                'author' => 'manjinder-singh',
                'categories' => ['delivery-guides', 'lifestyle'],
                'short' => 'Feeding vegetarians, spice-lovers, and cautious eaters in one order is easier when you plan around shared sides and clear labels.',
                'keywords' => 'dietary preferences, vegetarian ordering, group orders, dawat',
                'days_ago' => 26,
                'outer' => 'website/images/restaurants/taste-of-india-comp.jpg',
                'inner' => 'website/images/food-items/paneer-tikka-masala-ent-20.jpg',
                'body' => <<<'HTML'
<p>Group orders fall apart when one person cannot eat half the table. A little structure keeps everyone included without turning dinner into a negotiation.</p>
<h2>Anchor the order with shared neutrals</h2>
<p>Rice, plain naan, salad, raita, and mild dals create a safe base. Build bolder mains around that centre so spice fans can go further without forcing heat on everyone.</p>
<ol>
  <li>Confirm allergies and non-negotiables first</li>
  <li>Choose one mild main and one adventurous main</li>
  <li>Add sauces and pickles on the side</li>
</ol>
{{how-dawat-works}}
<h2>Read the menu like a host</h2>
<p>Look for clear vegetarian marks, paneer and vegetable mains, and kitchens that note cross-contamination practices. When in doubt, use the dish description and restaurant notes rather than assumptions.</p>
<h2>Finish with something everyone can share</h2>
<p>A simple dessert or sweet lassi often becomes the peacekeeper of the meal. Shared endings matter as much as carefully chosen mains.</p>
<p>Inclusive ordering is hospitality. Dawat makes it easier when restaurants label honestly and diners plan with empathy.</p>
{{discover-restaurants}}
HTML,
            ],
            [
                'title' => 'From First Order to Regular: How Restaurants Earn Loyalty Online',
                'slug' => 'how-restaurants-earn-loyalty-online',
                'author' => 'rohan-mehta',
                'categories' => ['restaurant', 'delivery-guides'],
                'short' => 'Repeat orders are won through reliability, recognisable flavour, and small moments of care that turn a first delivery into a habit.',
                'keywords' => 'restaurant loyalty, repeat orders, online food delivery, dawat partners',
                'days_ago' => 29,
                'outer' => 'website/images/restaurants/taj-flavors-comp.jpg',
                'inner' => 'website/images/food-items/jalebi.jpg',
                'body' => <<<'HTML'
<div class="blog-introduction"><p>Repeat orders are won through reliability, recognisable flavour, and small moments of care that turn a first delivery into a habit.</p></div>
<p>A first order is curiosity. A second order is trust. Everything a restaurant does between those two moments decides whether a guest becomes a regular on Dawat.</p>
<h2>Be recognisably good</h2>
<p>Guests should be able to describe your food in one sentence: “the biryani with perfect grain,” “the tikka that actually tastes smoked,” “the gulab jamun that arrives warm.” Distinctiveness makes reordering emotional, not accidental.</p>
<h2>Operational trust beats occasional brilliance</h2>
<ul>
  <li>Accurate prep times</li>
  <li>Complete orders with the right sauces</li>
  <li>Packaging that protects the dish</li>
  <li>Consistent seasoning across busy nights</li>
</ul>
{{partner-with-dawat}}
<h2>Close the loop with care</h2>
<p>A thank-you note, a correctly packed cutlery option, or a quick resolution when something goes wrong can outweigh a perfect plate. Loyalty grows where guests feel respected.</p>
<p>Restaurants that treat every delivery bag as a brand moment earn the kind of repeat business no promotion can manufacture alone.</p>
{{talk-to-dawat}}
HTML,
            ],
        ];
    }

    protected function uploadImage(string $path, string $filename, string $objectType, int $objectId): bool
    {
        if (!is_file($path)) {
            $this->command?->warn("Image not found: {$path}");

            return false;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/png',
        };

        $uploadedFile = new UploadedFile(
            $path,
            $filename,
            $mime,
            null,
            true
        );

        fn_upload_image($uploadedFile, $objectType, $objectId, true);

        return true;
    }
}
