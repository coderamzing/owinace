@extends('layouts.frontend')

@section('title', 'How LeadCliq Works | Lead CRM + AI Cover Letters for Teams - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="Learn how LeadCliq works. Discover how to generate AI-powered proposals, manage leads, track analytics, and streamline your sales workflow in 4 easy steps.">
<meta name="keywords" content="how LeadCliq works, AI proposal generation, lead management workflow, sales automation, CRM tutorial">
<meta name="author" content="LeadCliq">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="How LeadCliq Works - AI Proposals & Lead CRM">
<meta property="og:description" content="Learn how LeadCliq works. Generate AI-powered proposals, manage leads, track analytics, and streamline your sales workflow in 4 easy steps.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="How LeadCliq Works - AI Proposals & Lead CRM">
<meta property="twitter:description" content="Learn how LeadCliq works. Generate AI-powered proposals, manage leads, track analytics, and streamline your sales workflow.">
<meta property="twitter:image" content="{{ asset('images/leadcliq-og.png') }}">
@endpush



@push('styles')
<style>
    .step-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border: 1px solid rgba(102, 126, 234, 0.2);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .step-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }

    .step-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        border-color: rgba(102, 126, 234, 0.4);
    }

    .step-number {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .workflow-section {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
        border-radius: 25px;
        padding: 3rem;
        margin: 3rem 0;
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    .benefit-card {
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background: white;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .credit-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .process-flow {
        position: relative;
        padding: 2rem 0;
    }

    .process-flow::before {
        content: '';
        position: absolute;
        top: 35%;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        z-index: 1;
    }

    .process-step {
        position: relative;
        z-index: 2;
        background: white;
        border: 3px solid #667eea;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: #667eea;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .how-it-works-right {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
    }

    @media (max-width: 768px) {
        .process-flow::before {
            display: none;
        }
        
        .step-card {
            margin-bottom: 2rem;
        }
    }

    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin: 3rem 0;
    }

    .stat-item {
        text-align: center;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background: white;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 0.5rem;
    }

    .mission-image {
        padding: 2rem;
    }

    .mission-image iframe {
        border-radius: 15px;
        width: 100%;
        max-width: 560px;
    }

    .section-title {
        color: #061d19;
        margin-bottom: 1.5rem;
    }

    .min-h-75 {
        min-height: 75vh;
    }

    .accordion-item {
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1rem;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .accordion-button {
        width: 100%;
        text-align: left;
        padding: 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .accordion-button:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    }

    .accordion-button.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: #667eea;
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        padding: 0 1.5rem;
    }

    .accordion-content.active {
        max-height: 1000px;
        padding: 0 1.5rem 1.5rem;
    }

    .accordion-icon {
        transition: transform 0.3s ease;
    }

    .accordion-button.active .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-content iframe {
        margin-top: 1rem;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')
<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => url('/')
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "How It Works",
            "item" => url()->current()
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "HowTo",
    "name" => "How to Use LeadCliq for AI Proposals and Lead Management",
    "description" => "Step-by-step guide to using LeadCliq for generating AI proposals and managing leads",
    "step" => [
        [
            "@type" => "HowToStep",
            "position" => 1,
            "name" => "Sign up",
            "text" => "Create your workspace, invite teammates, and start with AI proposal templates."
        ],
        [
            "@type" => "HowToStep",
            "position" => 2,
            "name" => "Set up your profile",
            "text" => "Add services, rates, and portfolio items so proposals auto-match every job."
        ],
        [
            "@type" => "HowToStep",
            "position" => 3,
            "name" => "Customize your workspace",
            "text" => "Set up pipelines, SLAs, and roles; connect files and comments to every lead."
        ],
        [
            "@type" => "HowToStep",
            "position" => 4,
            "name" => "Launch and optimize",
            "text" => "Monitor costs, track goals, and optimize proposals with real-time analytics."
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<!-- Main Content -->
<section class="w-full lg:py-25 md:py-22.5 py-17.5">
    <div class="max-w-7xl mx-auto px-5">
        <!-- How It Works Section -->
        <section class="mb-16">
            <div class="text-center mb-12 aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">How It Works</h1>
                <p class="mb-2.5">A simple workflow to track leads, follow up on time, and write better cover letters</p>
            </div>
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full lg:w-1/2 px-4 mb-8 lg:mb-0">
                    <p class="text-dark mb-4 leading-relaxed">
                        LeadCliq starts with a clean workspace setup: create a company or agency workspace, invite members, and assign roles so everyone has the right access. Then add your portfolios and services once—so every proposal stays consistent and professional.
                    </p>
                    <p class="text-dark mb-4 leading-relaxed">
                        Next, track your pipeline in a Kanban board built for freelancers, agencies, and small companies. Each lead keeps contacts, notes, attachments, comments, follow-up reminders, and proposal history together—so nothing gets lost between chats, tabs, and tools.
                    </p> 
                    <p class="text-dark mb-6 leading-relaxed">
                        Finally, generate AI cover letters that automatically match your portfolio, monitor lead costs and ROI, and set member goals. With performance dashboards and AI insights, you can see what’s working and improve your win rate over time.
                    </p>
                </div>
                <div class="w-full lg:w-1/2 px-4">
                    <div class="relative">
                        <div class="mission-image text-center bg-gradient-to-br from-purple-50 to-blue-50 rounded-3xl">
                            <iframe 
                                width="560" 
                                height="315" 
                                src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                                title="How LeadCliq Works - Video Tutorial" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" 
                                allowfullscreen
                                class="w-full aspect-video"
                                style="border: none;">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- FAQ Section -->
        <section class="mt-16">
            <div class="mb-12 text-center aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">How LeadCliq Works</h2>
                <p class="mb-2.5">Step-by-step guide to using LeadCliq for generating AI proposals and managing leads</p>
            </div>

            <div class="max-w-5xl mx-auto space-y-4">
                <!-- FAQ Item 1 -->
                <div class="accordion-item">
                    <button class="accordion-button active" onclick="toggleAccordion(this)">
                        <span>How do I track member performance and goals?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content active">
                        <p class="text-dark mb-4">
                            Use dashboards to review member performance, set clear goals, and spot gaps in follow-ups and conversions. LeadCliq helps you coach with data—without adding admin overhead.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="accordion-item">
                    <button class="accordion-button" onclick="toggleAccordion(this)">
                        <span>How does LeadCliq track lead cost and ROI?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-dark mb-4">
                            Track cost-per-lead by source and monitor ROI based on outcomes in your pipeline. You’ll know which channels and campaigns deserve more budget—and which ones to stop.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="accordion-item">
                    <button class="accordion-button" onclick="toggleAccordion(this)">
                        <span>Can I manage multiple teams and admins?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-dark mb-4">
                            Yes. Create multiple teams, invite members, and assign multiple admins with role-based access. Everyone sees what they need—while you keep control of permissions and workflows.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="accordion-item">
                    <button class="accordion-button" onclick="toggleAccordion(this)">
                        <span>Ready to Generate Proposals Instantly?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-dark mb-4">
                            Our smart CRM, paired with a powerful browser extension, lets you create professional, personalized proposals in seconds. Streamline your workflow, save valuable time, and deliver polished proposals directly to your clients—effortlessly and efficiently.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="accordion-item">
                    <button class="accordion-button" onclick="toggleAccordion(this)">
                        <span>Ready to Streamline Your Lead Management?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-dark mb-4">
                            Visualize and manage every lead effortlessly with Kanban boards, review last month's leads at a glance, and keep detailed client information organized—making lead tracking simple, efficient, and fully transparent.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="accordion-item">
                    <button class="accordion-button" onclick="toggleAccordion(this)">
                        <span>How do AI credits work in LeadCliq?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-dark mb-4">
                            LeadCliq uses a simple credit system for AI actions (like generating cover letters). Use credits only when you generate with AI—everything else (lead CRM, Kanban, follow-ups, goals, analytics, and team management) stays available so you can scale at your own pace.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="Team Performance Analytics Video" 
                            style="border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen
                            class="w-full aspect-video rounded-lg">
                        </iframe>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function toggleAccordion(button) {
        const content = button.nextElementSibling;
        const isActive = button.classList.contains('active');
        
        // Close all accordions
        document.querySelectorAll('.accordion-button').forEach(function(btn) {
            btn.classList.remove('active');
            btn.nextElementSibling.classList.remove('active');
        });
        
        // Open clicked accordion if it wasn't active
        if (!isActive) {
            button.classList.add('active');
            content.classList.add('active');
        }
    }

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all step cards and benefit cards
    document.querySelectorAll('.step-card, .benefit-card, .stat-item').forEach(function(card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>
@endpush
