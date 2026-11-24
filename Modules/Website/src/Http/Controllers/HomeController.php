<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('website::pages.home');
    }

    public function about(Request $request)
    {
        return view('website::pages.about');
    }

    public function howItWorks(Request $request)
    {
        return view('website::pages.how-it-works');
    }

    public function blogs(Request $request)
    {
        $blogs = \Modules\Blogs\Models\Blog::with('outerImageLinks')->published()
            ->whereNotNull('post_url')
            ->where('post_url', '!=', '')
            ->orderBy('sort_order')
            ->latestFirst()
            ->paginate(12);

        return view('website::pages.blogs', compact('blogs'));
    }

    public function blogDetail(Request $request, string $slug)
    {
        $blog = \Modules\Blogs\Models\Blog::with(['author', 'faqs', 'outerImageLinks', 'innerImageLinks'])->published()->where('post_url', $slug)->firstOrFail();

        return view('website::pages.blog-detail', [
            'blog' => $blog,
            'isPreview' => false,
            'latestBlogs' => $this->latestBlogs($blog),
        ]);
    }

    public function blogPreview(string $slug)
    {
        $blog = \Modules\Blogs\Models\Blog::with(['author', 'faqs', 'outerImageLinks', 'innerImageLinks'])->where('post_url', $slug)->firstOrFail();

        return response()->view('website::pages.blog-detail', [
            'blog' => $blog,
            'isPreview' => true,
            'latestBlogs' => $this->latestBlogs($blog),
        ])
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->header('Cache-Control', 'private, no-store');
    }

    public function authorDetail(Request $request, string $slug)
    {
        $author = \Modules\Blogs\Models\BlogAuthor::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $blogs = \Modules\Blogs\Models\Blog::with('outerImageLinks')
            ->published()
            ->where('author_id', $author->id)
            ->whereNotNull('post_url')
            ->where('post_url', '!=', '')
            ->orderBy('sort_order')
            ->latestFirst()
            ->paginate(12);

        return view('website::pages.author-detail', compact('author', 'blogs'));
    }

    protected function latestBlogs(\Modules\Blogs\Models\Blog $blog)
    {
        return \Modules\Blogs\Models\Blog::with('outerImageLinks')->published()
            ->where('id', '!=', $blog->id)
            ->whereNotNull('post_url')->where('post_url', '!=', '')
            ->latestFirst()->limit(3)->get();
    }

    public function contact(Request $request)
    {
        return view('website::pages.contact');
    }

    public function partner(Request $request)
    {
        return view('website::pages.partner');
    }

    public function verifyPartnerCaptcha(Request $request)
    {
        $enabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();
        $request->validate([
            'g-recaptcha-response' => [
                \Illuminate\Validation\Rule::requiredIf($enabled),
                ...($enabled ? [new \Modules\Website\Rules\Recaptcha] : []),
            ],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);

        return response()->json(['success' => true]);
    }

    public function partnerSignup(Request $request)
    {
        return view('website::pages.partner-signup');
    }

    public function verifyPartnerSignupCaptcha(Request $request)
    {
        if (! app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled()) {
            return response()->json(['success' => true]);
        }
        $validated = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $this->verifyPartnerCaptcha($request);

        return response()->json([
            'success' => true,
            'verification_token' => app(\Modules\Website\Support\PartnerSignupCaptcha::class)->issue($validated['email']),
            'expires_in' => \Modules\Website\Support\PartnerSignupCaptcha::LIFETIME,
        ]);
    }

    public function partnerLogin(Request $request)
    {
        return view('website::pages.partner-signup', ['isLogin' => true]);
    }

    public function privacyPolicy(Request $request)
    {
        return view('website::pages.privacy-policy');
    }

    public function termsAndConditions(Request $request)
    {
        return view('website::pages.terms-and-conditions');
    }
}
