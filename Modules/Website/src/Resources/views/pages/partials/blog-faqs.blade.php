@if($blog->faqs->isNotEmpty())
    <section aria-labelledby="blog-faq-heading" class="website-blog-faqs font-urbanist">
        <h2 id="blog-faq-heading">Frequently Asked Questions</h2>
        <div class="website-blog-faq-list">
            @foreach($blog->faqs as $faq)
                <details class="website-blog-faq-item">
                    <summary>
                        <span>{{ $faq->question }}</span>
                        <span class="website-blog-faq-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <path d="M5 12h14" />
                                <path class="website-blog-faq-plus" d="M12 5v14" />
                            </svg>
                        </span>
                    </summary>
                    <p>{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </section>
@endif
