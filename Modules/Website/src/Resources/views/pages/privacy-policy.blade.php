@extends('website::layouts.app')

@section('title', 'Privacy Policy — Dawat')
@section('meta_description', 'Read the Dawat Privacy Policy and learn how we collect, use, and protect your information.')

@section('styles')
<style>
    .privacy-hero::before { content: ''; position: absolute; inset: -8px; background: linear-gradient(180deg, rgba(0,0,0,.55), rgba(0,0,0,.73) 68%, #000), url("{{ \Modules\Website\Support\WebsiteImages::url('website/images/e4c902957ed634404076ae416d26b967b43bc0f4.jpg') }}") center 52% / cover; filter: blur(2px); }
</style>
@endsection

@section('content')
<div class="overflow-hidden bg-black">
    <section class="privacy-hero relative isolate flex min-h-[340px] items-center justify-center overflow-hidden px-5 pb-10 pt-24 text-center sm:min-h-[390px] md:min-h-[420px]">
        <div class="relative z-10 mx-auto max-w-[900px]">
            <h1 data-aos="fade-down" class="font-newyork text-[42px] leading-tight text-white sm:text-5xl md:text-[65px]">Privacy &amp; Policy</h1>
            <p data-aos="fade-up" data-aos-delay="100" class="mx-auto mt-4 max-w-[800px] font-urbanist text-sm leading-relaxed text-white/85 sm:text-base md:text-[18px]">At Dawat, we value your privacy. This policy explains how we collect, use, and protect information when you use our services.</p>
            <p data-aos="fade-up" data-aos-delay="180" class="mt-6 font-urbanist text-sm text-white/75 md:text-base">Last updated | 11-12-2026 | 01:50 PM</p>
        </div>
    </section>

    @php
        $privacySections = [
            ['title' => 'Information We Collect', 'paragraphs' => ['We collect information you provide when creating an account, placing an order, contacting support, or partnering with Dawat. This may include your name, email address, phone number, delivery address, and order details.']],
            ['title' => 'How We Use Your Information', 'paragraphs' => ['We use your information to provide and improve Dawat services, process orders, communicate updates, respond to requests, and maintain a safe, reliable experience.'], 'bullets' => ['Process and deliver your orders.', 'Personalise restaurant and food recommendations.', 'Send service updates and support communications.', 'Improve our platform, products, and customer experience.']],
            ['title' => 'Account Access and Security', 'paragraphs' => ['You are responsible for keeping your account credentials confidential. Notify us immediately via {company_email} if you believe your account has been accessed without your permission.']],
            ['title' => 'Cookies and Similar Technologies', 'paragraphs' => ['We may use cookies and similar technologies to remember preferences, understand how the platform is used, and improve the performance of our services.']],
            ['title' => 'Payment Information', 'paragraphs' => ['Payments are processed through authorised payment providers. Dawat does not store complete payment card details on its own systems.']],
            ['title' => 'Third-Party Services', 'paragraphs' => ['Our platform may connect with third-party services such as payment processors, analytics providers, and delivery partners. These services handle information according to their own privacy policies.']],
            ['title' => 'Data Retention', 'paragraphs' => ['We retain personal information only for as long as needed to provide our services, meet legal obligations, resolve disputes, and enforce our agreements.']],
            ['title' => 'Your Choices', 'paragraphs' => ['You may update certain account details through the platform. You can also contact us to request access, correction, or deletion of your personal information, subject to applicable law.']],
            ['title' => 'Children’s Privacy', 'paragraphs' => ['Dawat is not intended for individuals under the age required to use our services in their location. We do not knowingly collect personal information from children.']],
            ['title' => 'Changes to This Policy', 'paragraphs' => ['We may update this Privacy Policy from time to time. Continued use of Dawat after a policy update means you acknowledge the revised policy.']],
            ['title' => 'Contact Us', 'paragraphs' => ['For questions or concerns about this Privacy Policy, please contact us via {company_email}.']],
        ];
    @endphp

    <article class="mx-auto w-[90%] max-w-[1700px] py-10 md:py-14">
        <div data-aos="fade-up" class="max-w-[1450px] font-urbanist text-sm leading-relaxed text-white/75 md:text-base">
            <p>We are committed to treating your personal information with care and transparency. Please read this policy to understand how your information is handled when you browse Dawat, create an account, place an order, or contact our team.</p>
            <p class="mt-5">This policy applies to information collected through our website, mobile experiences, and related customer support channels.</p>
        </div>

        <div class="mt-10 space-y-9 md:mt-14 md:space-y-11">
            @foreach($privacySections as $index => $section)
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
