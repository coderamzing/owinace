@extends('layouts.frontend')


@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="LeadCliq is a lead CRM for freelancers, agencies, and small companies. Manage your pipeline with Kanban, follow-up reminders, proposal history, portfolios, team goals, lead cost and ROI—plus AI cover letters and a Chrome extension for Upwork.">
<meta name="keywords" content="lead CRM, CRM for small business, CRM for agencies, CRM for freelancers, lead management, follow up reminders, kanban CRM, lead cost tracking, ROI tracking, proposal management, AI cover letter, Upwork cover letter, Chrome extension, portfolio matching, team performance, member goals">
<meta name="author" content="LeadCliq">
<meta name="robots" content="index, follow">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="AI Proposals + Lead CRM - LeadCliq">
<meta property="og:description" content="LeadCliq combines AI proposal generation with a lightweight lead CRM. Streamline your lead management with intelligent automation.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">
<meta property="og:site_name" content="LeadCliq">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="AI Proposals + Lead CRM - LeadCliq">
<meta property="twitter:description" content="LeadCliq combines AI proposal generation with a lightweight lead CRM. Streamline your lead management with intelligent automation.">
<meta property="twitter:image" content="{{ asset('images/leadcliq-og.png') }}">
@endpush

@section('content')
<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [[
        "@type" => "ListItem",
        "position" => 1,
        "name" => "Home",
        "item" => url('/')
    ]]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "name" => "LeadCliq",
    "url" => url('/'),
    "logo" => asset('images/leadcliq-logo.png'),
    "description" => "AI-powered proposal generation and lead management platform",
    "contactPoint" => [
        "@type" => "ContactPoint",
        "telephone" => "+91-7710113366",
        "contactType" => "Customer Service",
        "email" => "support@leadcliq.ai",
        "availableLanguage" => ["English"]
    ],
    "sameAs" => []
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "SoftwareApplication",
    "name" => "LeadCliq",
    "applicationCategory" => "BusinessApplication",
    "operatingSystem" => "Web",
    "offers" => [
        "@type" => "Offer",
        "price" => "0",
        "priceCurrency" => "USD"
    ],
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => "4.5",
        "ratingCount" => "100"
    ],
    "description" => "AI-powered proposal generation and lead management CRM platform"
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "LeadCliq",
    "url" => url('/'),
    "potentialAction" => [
        "@type" => "SearchAction",
        "target" => [
            "@type" => "EntryPoint",
            "urlTemplate" => url('/') . "?q={search_term_string}"
        ],
        "query-input" => "required name=search_term_string"
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "Product",
    "name" => "LeadCliq",
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => "4.5",
        "reviewCount" => "100"
    ],
    "review" => [
        [
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => "Billy Vasquez"
            ],
            "datePublished" => "2024-08-15",
            "reviewBody" => "LeadCliq drafts AI cover letters that already match our portfolio. We edit less, submit faster, and spend more time closing.",
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => "5",
                "bestRating" => "5"
            ]
        ],
        [
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => "Louis Ferguson"
            ],
            "datePublished" => "2024-09-04",
            "reviewBody" => "Lead handoffs are smoother—comments, attachments, and contacts stay tied to each deal. Execs finally get reliable numbers on cost and performance.",
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => "4",
                "bestRating" => "5"
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<main>

    <!-- Hero Section -->
    <section id="hero" class="bg-white lg:py-20 md:py-22.5 pt-10 pb-17.5">
        <div class="max-w-7xl mx-auto px-5">

            <div class="text-center" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h1 class="md:text-6xl text-4xl mb-2.5">LeadCliq CRM for teams: leads, follow-ups, ROI & AI cover letters</h1>
            </div>

            <div class="lg:mb-8 md:mb-10 mb-7.5 lg:mx-auto text-center aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="600" data-aos-easing="ease-in-out">
                <p class="mb-2.5">LeadCliq brings your lead pipeline, Kanban board, follow-up reminders, proposal history, portfolios, and team performance into one simple workspace. Built for freelancers, agencies, and small companies: generate AI cover letters that auto-match your portfolio, track lead cost & ROI, set member goals, and win faster—without a steep learning curve.</p>
            </div>

            <div class="flex justify-center items-center md:gap-5 md:flex-row gap-2.5 flex-col mb-7.5 aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="600" data-aos-easing="ease-in-out">
                <div class="flex">
                    <img src="/images/profile.png" alt="Happy customer profile" class="size-10 rounded-full" width="40" height="40" loading="eager">
                    <img src="/images/woman.png" alt="Happy customer" class="size-10 rounded-full -ms-2.5" width="40" height="40" loading="eager">
                    <img src="/images/profile-2.png" alt="Happy customer profile" class="size-10 rounded-full -ms-2.5" width="40" height="40" loading="eager">
                    <img src="/images/man.png" alt="Happy customer" class="size-10 rounded-full -ms-2.5" width="40" height="40" loading="eager">
                </div>

                <div class="flex justify-center items-center gap-1.25">
                    <div>Built for freelancers & teams</div>
                    <i class="iconify tabler--star-filled text-yellow-400"></i>
                    <div>4.5 (Reviews)</div>
                </div>
            </div>

            <div class="flex md:gap-5 gap-2.5 md:flex-row flex-col items-center justify-center">
                <div>
                    <a href="{{ route('register') }}" class="py-3.5 md:px-7.5 px-6.5 inline-flex items-center text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary">
                        Get started
                    </a>
                </div>

                <div>
                    <a href="{{ route('login') }}" class="py-3.5 md:px-7.5 px-6.5 inline-flex items-center text-center bg-primary font-medium rounded-2xl text-black transition-all duration-300 hover:text-primary hover:bg-black">
                        Login
                    </a>
                </div>
            </div>

 
            <div class="grid md:grid-cols-4 lg:gap-7.5 md:gap-5 lg:my-25 md:my-17.5 my-15 gap-5">
                <div class="md:col-span-1" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="relative lg:pe-10">
                        <img src="/images/home-banner-1.png" alt="LeadCliq dashboard preview" class="rounded-2xl" width="600" height="800" loading="lazy">
                        <img src="/images/3-BdEAuNWq.svg" alt="Analytics chart preview" class="md:absolute md:block md:top-11.75 md:-end-2.5 md:border md:border-neutral-200 md:rounded-2xl hidden" width="200" height="200" loading="lazy">
                    </div>
                </div>

                <div class="md:col-span-1" data-aos="fade-up" data-aos-delay="500" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <img src="/images/home-middle-banner.png" alt="LeadCliq CRM features" class="h-full rounded-2xl object-cover max-w-full shadow-[0_0_10px_#00000026]" width="400" height="800" loading="lazy">
                </div>

                <div class="md:col-span-2" data-aos="fade-up" data-aos-delay="700" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <img src="/images/banner-1.png" alt="LeadCliq proposal generation interface" class="rounded-2xl max-w-full object-center" width="1200" height="800" loading="lazy">
                </div>
            </div>

            <!-- <div>
                    <div class="text-center">
                        <div class="mb-7">Trusted by teams that own revenue</div>
                        <div class="flex lg:gap-7.5 lg:flex-row justify-center">
                            <img src="data:image/svg+xml,%3csvg%20width='142'%20height='30'%20viewBox='0%200%20142%2030'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20fill-rule='evenodd'%20clip-rule='evenodd'%20d='M11.2883%2018.75V3.75L4.80078%207.5V22.5L17.7656%2030L24.2532%2026.25L11.2883%2018.75Z'%20fill='%23181717'/%3e%3cpath%20fill-rule='evenodd'%20clip-rule='evenodd'%20d='M17.7656%2015L30.7305%2022.5V15L24.2532%2011.25V3.75L17.7656%200V15Z'%20fill='%23181717'/%3e%3cg%20clip-path='url(%23clip0_242_1543)'%3e%3cpath%20d='M42.474%2019.59H55.884V21.48H40.278V9.20398H42.474V19.59ZM57.1621%2016.962C57.1621%2013.686%2059.6101%2012.228%2064.8481%2012.228C70.1041%2012.228%2072.5341%2013.686%2072.5341%2016.962C72.5341%2020.238%2070.1041%2021.696%2064.8481%2021.696C59.6101%2021.696%2057.1621%2020.238%2057.1621%2016.962ZM59.2141%2016.962C59.2141%2019.086%2060.6541%2019.932%2064.8481%2019.932C69.0421%2019.932%2070.4821%2019.086%2070.4821%2016.962C70.4821%2014.856%2069.0421%2013.992%2064.8481%2013.992C60.6541%2013.992%2059.2141%2014.856%2059.2141%2016.962ZM88.4471%2012.912V20.076C88.4471%2023.64%2086.2331%2024.954%2081.0851%2024.954C79.1591%2024.954%2076.6211%2024.666%2074.7671%2024.126L75.2351%2022.524C76.9991%2022.992%2079.0331%2023.316%2081.1931%2023.316C85.2611%2023.316%2086.5391%2022.38%2086.4671%2020.094L86.4311%2019.662C84.5591%2020.436%2082.2911%2021.03%2079.9331%2021.03C76.2611%2021.03%2074.3711%2019.752%2074.3711%2016.71C74.3711%2013.38%2077.0711%2012.282%2081.2111%2012.282C84.0011%2012.282%2086.5571%2012.606%2088.4471%2012.912ZM76.4231%2016.692C76.4231%2018.618%2077.5391%2019.41%2080.0951%2019.41C82.1651%2019.41%2084.3071%2018.978%2086.3771%2018.276V14.352C84.7751%2014.136%2082.7051%2013.974%2080.9411%2013.974C78.1151%2013.974%2076.4231%2014.676%2076.4231%2016.692ZM92.8847%208.73598V11.166H90.8327V8.73598H92.8847ZM90.8327%2021.48V12.462H92.8847V21.48H90.8327ZM109.082%2012.912V24.72H107.03V20.004C105.122%2020.886%20102.818%2021.66%20100.424%2021.66C97.0042%2021.66%2095.0782%2020.292%2095.0782%2017.052C95.0782%2013.452%2097.7602%2012.282%20101.792%2012.282C104.582%2012.282%20107.192%2012.588%20109.082%2012.912ZM97.1302%2016.962C97.1302%2019.086%2098.2462%2019.932%20100.802%2019.932C102.872%2019.932%20104.96%2019.338%20107.03%2018.564V14.352C105.428%2014.136%20103.34%2013.974%20101.576%2013.974C98.7502%2013.974%2097.1302%2014.766%2097.1302%2016.962ZM111.307%2017.376V12.462H113.359V17.106C113.359%2018.924%20114.475%2019.95%20117.211%2019.95C118.723%2019.95%20120.793%2019.518%20123.007%2018.78V12.462H125.059V21.48H123.277L123.097%2020.184C121.171%2020.994%20118.849%2021.696%20116.563%2021.696C113.251%2021.696%20111.307%2020.472%20111.307%2017.376ZM134.682%2020.04C137.31%2020.04%20139.524%2019.644%20140.442%2019.446L140.874%2020.994C139.344%2021.39%20136.824%2021.696%20134.52%2021.696C129.588%2021.696%20127.176%2020.454%20127.176%2016.962C127.176%2013.542%20129.786%2012.246%20134.232%2012.246C138.318%2012.246%20141.198%2013.29%20141.198%2016.494C141.198%2016.8%20141.162%2017.304%20141.126%2017.502H129.192C129.444%2019.554%20131.118%2020.04%20134.682%2020.04ZM134.16%2013.704C130.956%2013.704%20129.408%2014.352%20129.192%2016.332H139.182C139.02%2014.55%20137.85%2013.704%20134.16%2013.704Z'%20fill='%23181717'/%3e%3c/g%3e%3cdefs%3e%3cclipPath%20id='clip0_242_1543'%3e%3crect%20width='102.6'%20height='30'%20fill='white'%20transform='translate(39)'/%3e%3c/clipPath%3e%3c/defs%3e%3c/svg%3e" alt="Partner logo" class="lg:h-7.5 h-6">
                            <img src="/images/4-BUCeMoND.svg" alt="Partner logo" class="lg:h-7.5 h-6">
                            <img src="/images/5-DeKq9tIn.svg" alt="Partner logo" class="lg:h-7.5 h-6">
                            <img src="/images/7-Dcq25DS2.svg" alt="Partner logo" class="lg:h-7.5 h-6 lg:block hidden">
                        </div>
                    </div>
                </div> -->
        </div>
    </section>

    <!-- Why choose Section  -->
    <section id="why-choose" class="lg:py-25 md:py-22.5 py-17.5">
        <div class="max-w-7xl mx-auto px-5">
            <div class="lg:mb-12.5 lg:mx-auto text-center mb-7.5" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="mb-2.5 lg:text-[40px] md:text-4.6xl text-3.4xl">Why freelancers, agencies, and small teams choose LeadCliq</h2>
                <p class="text-base mb-2.5">Stop juggling spreadsheets, notes, and tabs. LeadCliq helps you follow up on time, understand ROI, and write better proposals—with clear visibility for every member, every lead, and every stage.</p>
            </div>

            <div class="grid md:grid-cols-2 md:gap-25 items-center gap-7.5 lg:pb-25 pb-17.5">
                <div data-aos="fade-right" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <h3 class="lg:text-4xl mb-2.5 md:text-3.4xl text-2.6xl">Follow-ups, pipeline, and proposals—organized in one CRM</h3>
                    <p class="mb-2.5">Capture every lead, move it through a Kanban pipeline, and never miss a follow-up. Keep contacts, notes, attachments, comments, and proposal history tied to the deal—so you can act fast and stay consistent.</p>

                    <div class="flex flex-wrap gap-5 mt-10">
                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Kanban pipeline + follow-up reminders</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Contacts, comments, files & proposal history per lead</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Portfolio library in one place (easy reuse)</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Built for beginners: simple, clean, and fast</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-left" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <img src="/images/banner-3.png" alt="Sales and revenue insights dashboard" class="rounded-2xl" width="800" height="600" loading="lazy">
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:gap-25 md:gap-7.5 items-center gap-7.5">
                <div class="md:order-1 order-2" data-aos="fade-right" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <img src="/images/banner-4.png" alt="Statistical sales and lead analytics" class="rounded-2xl" width="800" height="600" loading="lazy">
                </div>

                <div class="md:order-2 order-1" data-aos="fade-left" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <h3 class="lg:text-4xl mb-2.5 md:text-3.4xl text-2.6xl">Lead cost, ROI, goals, and member performance—measured clearly</h3>
                    <p class="mb-2.5">Know what’s working. Track lead cost, expected pipeline value, ROI, and conversions by stage. Set member goals and review performance dashboards—so teams improve week over week.</p>
                    <div class="flex flex-wrap gap-5 mt-10">
                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Lead cost tracking by source & member</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Pipeline health: stages, expected value & win rate</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>Member goals + performance reporting</p>
                        </div>

                        <div class="flex gap-2.5">
                            <i class="iconify tabler--circle-check size-6 text-black"></i>
                            <p>ROI insights to scale what converts</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-4 md:grid-cols-2 lg:gap-7.5 lg:mt-32.5 mt-12.5 gap-5">
                <div data-aos="fade-up" data-aos-delay="50" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="flex items-center gap-2.5">
                        <div class="size-10 bg-dark rounded-full inline-flex items-center justify-center min-h-[40px] min-w-[40px]">
                            <i class="iconify solar--shield-check-outline size-5.5 text-primary"></i>
                        </div>
                        <div class=" text-black ">
                            <h4 class="lg:text-1.5xl text-lg font-medium">Robust security</h4>
                            <p>Protects lead data with strong access controls.</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="flex items-center gap-2.5">
                        <div class="size-10 bg-dark rounded-full inline-flex items-center justify-center min-h-[40px] min-w-[40px]">
                            <i class="iconify solar--smartphone-2-outline size-5.5 text-primary"></i>
                        </div>
                        <div class=" text-black ">
                            <h4 class="lg:text-1.5xl text-lg font-medium">Browser Based</h4>
                            <p>Access your system directly from any web browser.</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="flex items-center gap-2.5 flex-row">
                        <div class="size-10 bg-dark rounded-full inline-flex items-center justify-center min-h-[40px] min-w-[40px]">
                            <!-- <i class="iconify solar--headphones-round-outline size-5.5 text-primary"></i> -->
                            <img src="/images/mail.png" alt="Email icon" class="size-5.5 text-primary">
                        </div>
                        <div class=" text-black ">
                            <h4 class="lg:text-1.5xl text-lg font-medium">Email notifications</h4>
                            <p>Get alerts for updates, follow-ups, and important events.</p>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="flex items-center gap-2.5 flex-row">
                        <div class="size-10 bg-dark rounded-full inline-flex items-center justify-center min-h-[40px] min-w-[40px]">
                            <!-- <i class="iconify solar--lightbulb-outline size-5.5 text-primary"></i> -->
                            <img src="/images/ai-assistant.png" alt="AI icon" class="size-5.5 text-primary">
                        </div>
                        <div class=" text-black ">
                            <h4 class="lg:text-1.5xl text-lg font-medium">AI insights</h4>
                            <p>Spot risks, next steps, and improvements across your pipeline.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section  -->
    <section id="testimonials" class="bg-white lg:py-25 md:py-22.5 py-17.5">
        <div class="max-w-7xl mx-auto px-5">
            <div class="grid md:grid-cols-5 grid-cols-1 lg:gap-12.5 gap-5">
                <div class="md:col-span-2" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="bg-primary rounded-2xl lg:p-10 p-5 h-full">
                        <div class="flex mb-17.5">
                            <img src="/images/1-D6bexPI2.png" alt="Customer testimonial avatar" class="md:size-15 size-12.5 rounded-full" width="60" height="60" loading="lazy">
                            <img src="/images/2-C2hgbhv6.png" alt="Customer testimonial avatar" class="md:size-15 size-12.5 rounded-full -ml-3.75" width="60" height="60" loading="lazy">
                            <img src="/images/4-BTKjeLDb.png" alt="Customer testimonial avatar" class="md:size-15 size-12.5 rounded-full -ml-3.75" width="60" height="60" loading="lazy">
                            <img src="/images/5-lY0eiWKq.png" alt="Customer testimonial avatar" class="md:size-15 size-12.5 rounded-full -ml-3.75" width="60" height="60" loading="lazy">
                        </div>
                        <p class="text-lg font-medium text-black">“LeadCliq replaced our spreadsheet chaos. Follow-ups are consistent, proposals are faster, and everyone can see what’s happening in the pipeline.”</p>
                    </div>
                </div>

                <div class="md:col-span-3" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div class="bg-dark rounded-2xl lg:p-10 p-5 h-full">
                        <div class="swiper testiSwiper w-full">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="inline-flex flex-col">
                                        <div class="flex">
                                            <div class="flex gap-1.5 flex-row">
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-half-filled size-5 text-yellow-400"></i>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-white whitespace-normal mt-3.75 mb-11.25">“LeadCliq drafts AI cover letters that already match our portfolio. We edit less, submit faster, and spend more time closing.”</p>
                                        </div>

                                        <div class="flex justify-between items-end lg:mb-10 mb-5">
                                            <div class="flex gap-3.75 flex-row items-center">
                                                <img src="/images/3-CUvHyKCI.png" alt="Billy Vasquez profile photo" class="lg:size-15 size-12.5 rounded-full" width="60" height="60" loading="lazy">
                                                <div>
                                                    <div class="text-white">Billy Vasquez</div>
                                                    <div class="text-sm text-white">Retention Manager</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="swiper-slide">
                                    <div class="inline-flex flex-col">
                                        <div class="flex">
                                            <div class="flex gap-1.5 flex-row">
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-filled size-5 text-yellow-400"></i>
                                                <i class="iconify tabler--star-half-filled size-5 text-yellow-400"></i>
                                            </div>
                                        </div>

                                        <p class="text-white mt-3.75 mb-11.25">“Lead handoffs are smoother—comments, attachments, and contacts stay tied to each deal. Execs finally get reliable numbers on cost and performance.”</p>

                                        <div class="flex justify-between items-end lg:mb-10 mb-5">
                                            <div class="flex gap-3.75 flex-row items-center">
                                                <img src="/images/6-BZKiNfSv.png" alt="Louis Ferguson profile photo" class="lg:size-15 size-12.5 rounded-full" width="60" height="60" loading="lazy">
                                                <div>
                                                    <div class="text-white">Louis Ferguson</div>
                                                    <div class="text-sm text-white">Web Developer</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative z-10 flex gap-2.5 justify-end -mt-10">
                                <div class="custom-prev cursor-pointer size-8.75 bg-white/10 rounded-full inline-flex items-center justify-center">
                                    <i class="iconify tabler--chevron-left size-5.5 text-white"></i>
                                </div>

                                <div class="custom-next cursor-pointer size-8.75 bg-white/10 rounded-full inline-flex items-center justify-center">
                                    <i class="iconify tabler--chevron-right size-5.5 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 md:grid-cols-2 md:gap-10 md:flex-row lg:mt-25 mt-15 gap-7.5 flex-col">
                <div class="flex gap-4 flex-row" data-aos="fade-up" data-aos-delay="50" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div>
                        <div class="size-12.5 bg-dark rounded-full inline-flex items-center justify-center">
                            <!-- <i class="iconify solar--bolt-linear size-7 text-primary"></i> -->
                            <img src="/images/cover-letter.png" alt="Cover letter" class="size-7 text-primary">
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-2.5 text-1.5xl text-xl">AI cover letters + proposal history</h2>
                        <p class="lg:mb-5 lg:mt-1.25 mb-1.25">Generate and track every proposal, reuse what works, and stay consistent.</p>
                        <a href="{{ route('home') }}#why-choose" class="text-dark underline font-medium">Learn more</a>
                    </div>
                </div>

                <div class="flex gap-4 flex-row" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div>
                        <div class="size-12.5 bg-dark rounded-full inline-flex items-center justify-center">
                            <!-- <i class="iconify solar--shield-check-outline size-7 text-primary"></i> -->
                            <img src="/images/puzzle.png" alt="Chrome extension" class="size-7 text-primary">
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-2.5 text-1.5xl text-xl">Chrome extension for Upwork</h2>
                        <p class="lg:mb-5 lg:mt-1.25 mb-1.25">Write cover letters directly where you bid—fast, focused, and on-brand.</p>
                        <a href="{{ route('home') }}#clients" class="text-dark underline font-medium">Learn more</a>
                    </div>
                </div>

                <div class="flex gap-4 flex-row" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                    <div>
                        <div class="size-12.5 bg-dark rounded-full inline-flex items-center justify-center">
                            <!-- <i class="iconify solar--smartphone-2-outline size-7 text-primary"></i> -->
                            <img src="/images/network-connection.png" alt="Teams icon" class="size-7 text-primary">
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-2.5 text-1.5xl text-xl">Multi-team + multi-admin workspaces</h2>
                        <p class="lg:mb-5 lg:mt-1.25 mb-1.25">Invite members, assign roles, and manage multiple teams with clarity.</p>
                        <a href="{{ route('home') }}#workwithus" class="text-dark underline font-medium">Learn more</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Client Section  -->
    <section id="clients" class="lg:py-25 md:py-22.5 py-17.5">
        <div class="max-w-7xl mx-auto px-5">
            <div class="text-center mb-10" data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                <h2 class="mb-2.5 lg:text-[40px] md:text-4.6xl text-3.4xl">Everything you need to win more leads</h2>
            </div>

            <div class="grid lg:grid-cols-4 md:grid-cols-2 lg:gap-12.5 gap-7.5">
                <!-- Client Item 1  -->
                <div data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <div class="flex justify-center items-center bg-white rounded-2xl mb-4">
                        <img src="/images/followup_email_2.png" alt="flash" class=" rounded-2xl min-h-[180px] object-cover">
                    </div>
                    <h2>Follow-up reminders</h2>
                    <p class="mt-2.5">Never forget a follow-up—LeadCliq keeps your pipeline moving.</p>
                </div>

                <!-- Client Item 2  -->
                <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <!-- <div class="flex justify-center items-center bg-white rounded-2xl px-[10px] py-[30px] mb-4">
                        <img src="/images/donut-chart.png" alt="donut-chart" class="max-w-[200px]">
                    </div> -->
                    <div class="flex justify-center items-center bg-white rounded-2xl mb-4">
                        <img src="/images/Creating-customer-profiles.svg" alt="flash" class=" rounded-2xl min-h-[180px] object-cover">
                    </div>
                    <h2>ROI & lead cost</h2>
                    <p class="mt-2.5">Understand cost-per-lead, ROI, and which sources convert.</p>
                </div>

                <!-- Client Item 3  --> 
                <div data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <!-- <div class="flex justify-center items-center bg-white rounded-2xl px-[10px] py-[30px] mb-4">
                        <img src="/images/kanban-pipeline.png" alt="repeat" class="max-w-[200px]">
                    </div> -->
                    <div class="flex justify-center items-center bg-white rounded-2xl mb-4">
                        <img src="/images/kanban-pipeline.png" alt="flash" class=" rounded-2xl min-h-[180px] object-cover">
                    </div>
                    <h2>Kanban pipeline</h2>
                    <p class="mt-2.5">Drag, drop, and track every lead stage in seconds.</p>
                </div>

                <!-- Client Item 4  -->
                <div data-aos="fade-up" data-aos-delay="400" data-aos-duration="600" data-aos-easing="ease-in-out">
                    <!-- <div class="flex justify-center items-center bg-white rounded-2xl px-[10px] py-[30px] mb-4">
                        <img src="/images/conversion-rate.png" alt="conversion-rate" class="max-w-[200px]">
                    </div> -->
                    <div class="flex justify-center items-center bg-white rounded-2xl mb-4">
                        <img src="/images/portfolio.jpg" alt="flash" class=" rounded-2xl min-h-[180px] object-cover">
                    </div>
                    <h2>Portfolio matching</h2>
                    <p class="mt-2.5">Auto-match portfolios inside AI cover letters for stronger bids.</p>
                </div>
            </div>

            <div class="flex lg:mt-22.5 mt-15 text-center gap-1.25 flex-col" data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                <div>
                    <a href="{{ route('home') }}#why-choose" class="py-3.5 lg:px-7.5 px-6.5 inline-flex items-center text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary">
                        See how
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- User Step Section  -->
    <section class="bg-dark lg:py-25 py-17.5" id="workwithus">
        <div class="max-w-7xl mx-auto px-5">
            <div class="grid lg:grid-cols-2 lg:gap-15 gap-5" data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                <div>
                    <div class="relative">
                        <img src="/images/4-step-banner.png" alt="4 easy steps workflow diagram" class="rounded-2xl" width="500" height="400" loading="lazy">
                        <div class="bg-primary py-0.5 px-3.75 rounded-full font-medium text-sm inline-flex text-dark absolute -top-2.5 start-7.5">Get started in 4 easy steps</div>
                    </div>

                    <div class="flex mt-7.5 gap-2.5 md:flex-row flex-col">
                        <p class="text-white">Ready to experience AI proposals, lead clarity, and team analytics together?</p>
                        <a href="{{ route('register') }}" class="inline-flex text-white underline text-nowrap">
                            Register for LeadCliq
                        </a>
                    </div>
                </div>

                <div>
                    <div class="relative">
                        <div class="absolute start-7.5 h-11/12 w-0.75 bg-neutral-100/20"></div>
                        <div class="relative z-10">
                            <!-- Step 1  -->
                            <div class="flex gap-5 flex-row mb-12.5">
                                <div class="flex-shrink-1">
                                    <div class="size-15 bg-primary rounded-full inline-flex items-center justify-center">
                                        <h3 class="lg:text-1.5xl text-2.5xl">01</h3>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-1.5xl mb-2.5 text-white">Sign up</h2>
                                    <p class="text-white">Create your workspace, invite teammates, and start with AI proposal templates.</p>
                                </div>
                            </div>

                            <!-- Step 2  -->
                            <div class="flex gap-5 flex-row mb-12.5">
                                <div class="flex-shrink-1">
                                    <div class="size-15 bg-primary rounded-full inline-flex items-center justify-center">
                                        <h3 class="lg:text-1.5xl text-2.5xl">02</h3>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-1.5xl mb-2.5 text-white">Set up your profile</h2>
                                    <p class="text-white">Add services, rates, and portfolio items so proposals auto-match every job.</p>
                                </div>
                            </div>

                            <!-- Step 3  -->
                            <div class="flex gap-5 flex-row mb-12.5">
                                <div class="flex-shrink-1">
                                    <div class="size-15 bg-primary rounded-full inline-flex items-center justify-center">
                                        <h3 class="lg:text-1.5xl text-2.5xl">03</h3>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-1.5xl mb-2.5 text-white">Customize your workspace</h2>
                                    <p class="text-white">Set up pipelines, SLAs, and roles; connect files and comments to every lead.</p>
                                </div>
                            </div>

                            <!-- Step 4  -->
                            <div class="flex gap-5 flex-row mb-12.5">
                                <div class="flex-shrink-1">
                                    <div class="size-15 bg-primary rounded-full inline-flex items-center justify-center">
                                        <h3 class="lg:text-1.5xl text-2.5xl">04</h3>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-1.5xl mb-2.5 text-white">Launch and optimize</h2>
                                    <p class="text-white">Monitor costs, track goals, and optimize proposals with real-time analytics.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('register') }}" class="py-3.5 lg:px-7.5 px-6.5 rounded-2xl inline-flex font-medium bg-primary text-dark hover:text-primary hover:bg-black transition-all duration-300">
                        Get started now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="bg-white lg:py-25 py-17.5">
        <div class="max-w-7xl mx-auto px-5">
            <div class="lg:mb-12.5 text-center mb-7.5" data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">
                <h2 class="mb-2.5 lg:text-[40px] md:text-4.6xl text-3.4xl">Learn how high-performing teams win consistently</h2>
                <p class="text-base mb-2.5">Practical guides on follow-ups, proposal writing, ROI tracking, and building a reliable lead pipeline with LeadCliq—whether you’re solo or running a team.</p>
            </div>
            <div class="grid md:grid-cols-3 lg:gap-12.5 md:gap-5 gap-10" data-aos="fade-up" data-aos-duration="600" data-aos-easing="ease-in-out">

                <!-- Blog Item 1  -->
                <a href="#">
                    <div class="overflow-hidden rounded-2xl max-h-[480px] object-cover">
                        <img src="/images/Cover.png" alt="Blog article cover: AI-matched portfolios" class="duration-300 hover:scale-105 transition-all" width="400" height="480" loading="lazy">
                    </div>

                    <div class="md:mt-5 mt-2.5">
                        <p class="text-dark">August 6, 2024</p>
                        <h2 class="text-2xl md:mt-2.5 mt-1.25">How AI-matched portfolios cut proposal time in half</h2>
                    </div>
                </a>

                <!-- Blog Item 2  -->
                <a href="#">
                    <div class="overflow-hidden rounded-2xl max-h-[480px] object-cover">
                        <img src="/images/Cover-2.png" alt="Blog article cover: Cost-per-lead tracking" class="duration-300 hover:scale-105 transition-all" width="400" height="480" loading="lazy">
                    </div>

                    <div class="md:mt-5 mt-2.5">
                        <p class="text-dark">September 4, 2024</p>
                        <h2 class="text-2xl md:mt-2.5 mt-1.25">Playbook: tracking cost-per-lead and win rate in one view</h2>
                    </div>
                </a>

                <!-- Blog Item 3  -->
                <a href="#">
                    <div class="overflow-hidden rounded-2xl max-h-[480px] object-cover">
                        <img src="/images/banner-5.png" alt="Blog article cover: Lead routing and team alignment" class="duration-300 hover:scale-105 transition-all" width="400" height="480" loading="lazy">
                    </div>

                    <div class="md:mt-5 mt-2.5">
                        <p class="text-dark">September 4, 2024</p>
                        <h2 class="text-2xl md:mt-2.5 mt-1.25">Lead routing, goals, and comments: how teams stay aligned</h2>
                    </div>
                </a>
            </div>
        </div>
    </section>

</main>
@endsection