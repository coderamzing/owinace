@extends('layouts.frontend')

@section('title', 'How Owinace Works | Generate AI Proposals - ' . config('app.name', 'Owinace'))



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
        color: #667eea;
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
<!-- Main Content -->
<div class="w-full py-12 md:py-16">
    <div class="container mx-auto px-4">
        <!-- How It Works Section -->
        <section class="mb-16">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full lg:w-1/2 px-4 mb-8 lg:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold section-title">How It Works</h1>
                    <p class="text-gray-700 mb-4 leading-relaxed">
                        Owinace begins with a simple setup process where admins create the workspace, add team members, assign roles, and define permissions. This ensures everyone is aligned and ready to collaborate. Once the team is set, you can build your core assets, including proposals, portfolios, services, and structured lead pipelines that every member can use.
                    </p>
                    <p class="text-gray-700 mb-4 leading-relaxed">
                        Leads can be added through Kanban, list view, or imports, and each one stores conversations, files, costs, tasks, comments, and activities in a single timeline. As the team engages with leads, admins set performance goals and targets, giving members clear direction on expectations and achievements.
                    </p> 
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        When it's time to pitch, the AI Proposal Generator creates personalized proposals instantly. All lead records, proposal interactions, team activities, and analytics are automatically tracked—providing complete visibility into performance, conversions, and growth.
                    </p>
                </div>
                <div class="w-full lg:w-1/2 px-4">
                    <div class="relative">
                        <div class="mission-image text-center bg-gradient-to-br from-purple-50 to-blue-50 rounded-3xl">
                            <iframe 
                                width="560" 
                                height="315" 
                                src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" 
                                allowfullscreen
                                class="w-full aspect-video">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- FAQ Section -->
        <section class="mt-16">
            <div class="mb-12 text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">How Owinace Works</h2>
                <p class="text-xl text-gray-600">Everything you need to know about Owinace</p>
            </div>

            <div class="max-w-5xl mx-auto space-y-4">
                <!-- FAQ Item 1 -->
                <div class="accordion-item">
                    <button class="accordion-button active" onclick="toggleAccordion(this)">
                        <span>Curious About Your Team's Performance?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content active">
                        <p class="text-gray-700 mb-4">
                            Visualize every team member's progress with dynamic graphs and analytics. Track growth over time, compare with past performance, and gain actionable insights to maximize productivity and drive success.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
                        <span>Want to Keep Track of Every Cost?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-gray-700 mb-4">
                            Easily monitor how resources are spent across different channels and team members. Gain complete visibility into expenses and make informed decisions to manage your budget efficiently.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
                        <span>Want to Onboard Your Team in Just Minutes?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-gray-700 mb-4">
                            Launch your workspace by adding team members, assigning smart roles, and sending quick invitations. Each person enters with the right permissions, creating a well-organized, collaborative environment from the very first step.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
                        <p class="text-gray-700 mb-4">
                            Our smart CRM, paired with a powerful browser extension, lets you create professional, personalized proposals in seconds. Streamline your workflow, save valuable time, and deliver polished proposals directly to your clients—effortlessly and efficiently.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
                        <p class="text-gray-700 mb-4">
                            Visualize and manage every lead effortlessly with Kanban boards, review last month's leads at a glance, and keep detailed client information organized—making lead tracking simple, efficient, and fully transparent.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
                        <span>How Do Credits Work in Owinace?</span>
                        <svg class="accordion-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content">
                        <p class="text-gray-700 mb-4">
                            Creating proposals is easy with our flexible credit system—simply use credits when generating proposals, while all other features remain completely free. Control your usage and enjoy full access without hidden costs.
                        </p>
                        <iframe 
                            width="100%" 
                            height="315" 
                            src="https://www.youtube.com/embed/a1bwjUTeYUs?si=iN9ieI44ieWJt7cg" 
                            title="YouTube video player" 
                            frameborder="0" 
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
</div>
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
