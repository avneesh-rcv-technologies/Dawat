{{--
    Footer Component — Dawat Website
    Matches Figma Frame 2147229043 & Frame 2147228313
--}}
<footer data-aos="fade-up" class="bg-black border-t border-white/20 font-outfit relative z-10 pt-10 md:pt-20 pb-6 md:pb-12">
    <div class="max-w-[1500px] mx-auto px-4 md:px-6">

        {{-- Main Footer Body --}}
        <div class="flex flex-col lg:flex-row justify-between items-start gap-8 lg:gap-8 pb-8 md:pb-16">

            {{-- Left Column: Logo & Socials (Frame 2147227841) --}}
            <div class="flex flex-col gap-5 md:gap-10">
                {{-- Logo --}}
                <a href="{{ url('/') }}" class="inline-block">
                    <img src="{{ asset('website/images/dawat-wordmark.png') }}" alt="Dawat" class="block w-[100px] md:w-[132px] h-auto" />
                </a>

                {{-- Social Media Icons (Twitter, Facebook, Instagram, LinkedIn) --}}
                <div class="flex items-center gap-3 md:gap-4">
                    {{-- Twitter / X --}}
                    <a href="#" target="_blank" aria-label="Twitter"
                       class="w-9 h-9 md:w-14 md:h-14 rounded-full border border-white/10 hover:border-white/40 flex items-center justify-center bg-white/[0.02] hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>

                    {{-- Facebook --}}
                    <a href="#" target="_blank" aria-label="Facebook"
                       class="w-9 h-9 md:w-14 md:h-14 rounded-full border border-white/10 hover:border-white/40 flex items-center justify-center bg-white/[0.02] hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.514c-1.49 0-1.955.93-1.955 1.887v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                        </svg>
                    </a>

                    {{-- Instagram --}}
                    <a href="#" target="_blank" aria-label="Instagram"
                       class="w-9 h-9 md:w-14 md:h-14 rounded-full border border-white/10 hover:border-white/40 flex items-center justify-center bg-white/[0.02] hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>

                    {{-- LinkedIn --}}
                    <a href="#" target="_blank" aria-label="LinkedIn"
                       class="w-9 h-9 md:w-14 md:h-14 rounded-full border border-white/10 hover:border-white/40 flex items-center justify-center bg-white/[0.02] hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4 md:w-5 md:h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Right Columns (Frame 2147227915): About, Help, Policy --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-6 sm:gap-16 lg:gap-20 w-full lg:w-auto">

                {{-- Column 1: About --}}
                <div class="flex flex-col gap-3 md:gap-5">
                    <h5 class="font-outfit font-medium text-[12px] md:text-[16px] text-white">About</h5>
                    <ul class="flex flex-col gap-2.5 md:gap-3.5">
                        <li>
                            <a href="{{ route('website.about') }}" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                About Us
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('website.contact') }}" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Contact Us
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('website.blogs') }}" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Blogs
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('website.partner') }}" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Partner
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Column 2: Help --}}
                <div class="flex flex-col gap-3 md:gap-5">
                    <h5 class="font-outfit font-medium text-[12px] md:text-[16px] text-white">Help</h5>
                    <ul class="flex flex-col gap-2.5 md:gap-3.5">
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Payments
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Shipping
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Cancellations &amp; Returns
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                FAQ
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Column 3: Policy --}}
                <div class="flex flex-col gap-3 md:gap-5">
                    <h5 class="font-outfit font-medium text-[12px] md:text-[16px] text-white">Policy</h5>
                    <ul class="flex flex-col gap-2.5 md:gap-3.5">
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Terms of Use
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Security
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Privacy
                            </a>
                        </li>
                        <li>
                            <a href="#" class="font-outfit text-[12px] md:text-[16px] text-white/80 hover:text-white transition-colors">
                                Sitemap
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- Divider Line (Shape: 1240px, border: 1px solid rgba(255, 255, 255, 0.1)) --}}
        <div class="w-full border-t border-white/10 my-8"></div>

        {{-- Bottom Copyright Bar (Frame 2147228313) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-white/80 text-[12px] md:text-[16px]">
            <p>©2026 Dawat Curated By ROILift</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-white transition-colors">Teams &amp; Conditions</a>
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
            </div>
        </div>

    </div>
</footer>
