@extends('website::layouts.app')

@section('title', 'Terms & Conditions — Dawat')
@section('meta_description', 'Read the terms and conditions for using Dawat, managing your account, and ordering from participating restaurants.')

@section('styles')
<style>
    .terms-hero::before { content: ''; position: absolute; inset: -8px; background: linear-gradient(180deg, rgba(0,0,0,.55), rgba(0,0,0,.73) 68%, #000), url("{{ \Modules\Website\Support\WebsiteImages::url('website/images/e4c902957ed634404076ae416d26b967b43bc0f4.jpg') }}") center 52% / cover; filter: blur(2px); }
</style>
@endsection

@section('content')
<div class="overflow-hidden bg-black">
    <section class="terms-hero relative isolate flex min-h-[340px] items-center justify-center overflow-hidden px-5 pb-10 pt-24 text-center sm:min-h-[390px] md:min-h-[420px]">
        <div class="relative z-10 mx-auto max-w-[900px]">
            <h1 data-aos="fade-down" class="font-newyork text-[42px] leading-tight text-white sm:text-5xl md:text-[65px]">Terms &amp; Conditions</h1>
            <p data-aos="fade-up" data-aos-delay="100" class="mx-auto mt-4 max-w-[800px] font-urbanist text-sm leading-relaxed text-white/85 sm:text-base md:text-[18px]">Please read these terms before using Dawat. They explain how our platform works and your responsibilities when discovering restaurants and placing orders.</p>
            <p data-aos="fade-up" data-aos-delay="180" class="mt-6 font-urbanist text-sm text-white/75 md:text-base">Last updated | 03 November 2026</p>
        </div>
    </section>

    @php
        $termsSections = [
            ['title' => 'Using Dawat', 'paragraphs' => ['These terms apply when you access the Dawat website, use our mobile experiences, or place an order through our platform. Please read them alongside our Privacy Policy and the information displayed during checkout.']],
            ['title' => 'Your Account', 'paragraphs' => ['Provide accurate account and contact information, keep your login details confidential, and make sure your delivery address is correct before placing an order. Contact our team if you suspect unauthorised access to your account.']],
            ['title' => 'Restaurant Listings and Availability', 'paragraphs' => ['Menus, prices, opening hours, promotions, and item availability may vary by restaurant and location. Review the details shown for your selected restaurant before ordering. If you have an allergy or dietary requirement, confirm the ingredients and preparation arrangements with the restaurant before placing your order.']],
            ['title' => 'Placing an Order', 'paragraphs' => ['Check your selected items, customisations, delivery details, and total before submitting an order. Follow the order status shown on the platform for confirmation and updates. If an item is unavailable or an order cannot be fulfilled, contact support for help with the available next steps.']],
            ['title' => 'Prices and Payments', 'paragraphs' => ['Review the checkout summary for item prices, applicable taxes, delivery charges, and any other fees before confirming payment. Available payment methods and offers are displayed during checkout. Keep your payment information accurate and use only payment methods you are authorised to use.']],
            ['title' => 'Delivery and Collection', 'paragraphs' => ['Delivery estimates depend on restaurant preparation, distance, traffic, weather, and other operating conditions. Provide clear delivery instructions and remain available at the contact number supplied with your order. For collection orders, follow the restaurant instructions and order updates.']],
            ['title' => 'Cancellations, Refunds and Order Issues', 'paragraphs' => ['Available cancellation and refund options depend on the order status, the issue reported, and the policies shown when ordering. If your order is missing, incorrect, damaged, or delayed, contact support promptly and include your order details. Our team can explain the options available for your order.']],
            ['title' => 'Offers and Promotions', 'paragraphs' => ['Each offer may have its own eligibility requirements, expiry date, minimum order value, or usage limit. Read the conditions displayed with a promotion before applying it. The checkout summary shows whether an offer has been applied to your order.']],
            ['title' => 'Responsible Use', 'paragraphs' => ['Use Dawat responsibly and respect other customers, restaurant teams, and delivery partners.'], 'bullets' => ['Do not submit fraudulent orders or use payment details without permission.', 'Do not interfere with the platform, attempt unauthorised access, or misuse account information.', 'Keep reviews and other contributions relevant, accurate, and respectful.', 'Do not upload unlawful, abusive, or misleading content.']],
            ['title' => 'Content and Third-Party Services', 'paragraphs' => ['Dawat branding, website content, and platform features must not be copied or reused without the relevant permission. Restaurant materials and third-party services may be subject to their own terms. Review those terms when using a linked service.']],
            ['title' => 'Privacy', 'paragraphs' => ['Our Privacy Policy describes how information is collected, used, and protected when you use Dawat. Please review it to understand the choices available to you.']],
            ['title' => 'Updates to These Terms', 'paragraphs' => ['We may update these terms as our platform and services change. The date at the top of this page identifies the latest version. Check this page when you need the current terms.']],
            ['title' => 'Contact Us', 'paragraphs' => ['If you have questions about these terms or need assistance with an order, please contact us via {company_email}.']],
        ];
    @endphp

    <article class="mx-auto w-[90%] max-w-[1700px] py-10 md:py-14">
        <div data-aos="fade-up" class="max-w-[1450px] font-urbanist text-sm leading-relaxed text-white/75 md:text-base">
            <p>Dawat helps you discover restaurants, explore cuisines, and order the food you love. These terms describe the information you should review and the responsibilities that come with using our services.</p>
            <p class="mt-5">For details about how we handle your personal information, please read our <a href="{{ route('website.privacy-policy') }}" class="text-[#FF6210] hover:underline">Privacy Policy</a>.</p>
        </div>

        <div class="mt-10 space-y-9 md:mt-14 md:space-y-11">
            @foreach($termsSections as $index => $section)
                <section data-aos="fade-up" data-aos-delay="{{ min($index * 35, 210) }}" class="max-w-[1550px]">
                    <h2 class="font-urbanist text-[24px] font-semibold leading-tight text-white md:text-[30px]">{{ $index + 1 }}. {{ $section['title'] }}</h2>
                    @foreach($section['paragraphs'] as $paragraph)
                        <p class="mt-4 font-urbanist text-sm leading-relaxed text-white/70 md:text-base">{!! str_replace('{company_email}', $companyEmail !== '' ? '<a class="text-[#FF6210] hover:underline" href="mailto:'.e($companyEmail).'">'.e($companyEmail).'</a>' : '<a class="text-[#FF6210] hover:underline" href="'.e(route('website.contact')).'">our contact form</a>', e($paragraph)) !!}</p>
                    @endforeach
                    @if(isset($section['bullets']))
                        <ul class="mt-4 space-y-1 font-urbanist text-sm text-white/70 md:text-base">
                            @foreach($section['bullets'] as $bullet)<li>– {{ $bullet }}</li>@endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    </article>
</div>
@endsection
