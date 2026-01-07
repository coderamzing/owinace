@extends('layouts.frontend')

@section('title', 'AI Proposals + Lead CRM | Owinace')

@push('meta')
<meta name="title" content="AI Proposals + Lead CRM - Owinace">
<meta name="description" content="Owinace combines AI proposal generation with a lightweight lead CRM: contacts per lead, attachments, comments, goals, activity logs, and team workspaces.">
<meta name="keywords" content="AI proposals, lead management, CRM for freelancers, portfolio matching, attachments, comments, goals, activity log, team workspaces">
<meta name="author" content="Owinace">
@endpush

@push('styles')
<style>
    .home-banner-list{
        list-style: circle;
        padding-left: 1.125rem;
    }
    .home-banner-list li,
    .how-help-section ul li{
        padding-bottom: 0.375rem;
    }
    .home-banner-avatar img{
        width: 2.5rem;
    }
    .features-div img{
        max-width: 4.375rem;
    }
    .how-help-section{
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
    }
    .how-help-section ul{
        padding-left: 1.125rem;
    }
    .stratery-div img{
        margin: 3.75rem 0;
    }
    .banner-section{
        min-height: 80vh;
        margin-bottom: 20.625rem;
        padding-bottom: 12.5rem;
    }
    .home-banner-heading{
        margin-top: 6.25rem;
    }
    .home-banner-image{
        position: absolute;
        left: 50%;
        margin-top: 3.125rem;
        transform: translate(-50%, 0px);
    }
    @media (max-width: 991.98px) {
        .navbar{
            border-bottom: 0px !important;
        }
        .banner-section{
            margin-bottom: 6.25rem;
        }
    }
    @media (max-width: 568px) {
        .banner-section{
            margin-bottom: 3.125rem;
        }
    }
    @media (max-width: 468px) {
        .banner-section{
            margin-bottom: 0px;
        }
    }
</style>
@endpush

@section('content')
<!-- Start: Hero Banner -->
<div class="w-full flex bg-gray-50 relative banner-section" id="top">
    <div class="container mx-auto px-4">
        <div class="w-full text-center relative">
            <h1 class="text-5xl md:text-[4rem] font-bold home-banner-heading">Owinace — Performance-Driven Lead Management Software</h1>
            <h2 class="text-[1.5rem] font-normal leading-normal mt-4">Maximize conversions with a smarter workflow. Track lead performance, automate engagement, streamline team actions, and turn every opportunity into predictable revenue.</h2>
            <div class="items-center flex flex-wrap gap-2 justify-center mt-6">
                <a href="{{ route('register') }}" aria-label="Start your free trial"
                    class="bg-gray-900 text-white px-[12px] py-[6px] rounded-lg hover:bg-gray-800 transition-colors">Start Free Trial</a>
            </div>
            <img src="{{ asset('images/home-banner.png') }}" alt="home-banner" class="home-banner-image max-w-full" data-aos="fade-up"/>
        </div>
    </div>
</div>

<!-- Software features -->
<div class="py-16 w-full overflow-hidden features-div">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap">
            <div class="w-full">
                <h1 class="font-bold mb-8 text-[2.5rem]">Sales lead tracking software features</h1>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/settings-sliders.png') }}" alt="settings"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Customizable CRM software</h4>
                    <p class="mb-0 leading-normal text-base">Set personalized follow-ups, alerts, and workflows so your team receives timely reminders and steps, ensuring every lead and customer gets attention and no opportunity is missed.</p>
                </div>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/users-alt.png') }}" alt="user-alt"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Painless lead management</h4>
                    <p class="mb-0 leading-normal text-base">Effortlessly manage leads with organized records, full customer details, and visual progress graphs—making follow-ups simple and ensuring no lead slips through the cracks.</p>
                </div>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/dashboard.png') }}" alt="dashboard"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Seamless CRM automation</h4>
                    <p class="mb-0 leading-normal text-base">Intelligent CRM automation streamlines your sales pipeline, saves time and effort, ensures every lead and opportunity is tracked, and helps your team achieve goals efficiently without missing anything.</p>
                </div>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/archery.png') }}" alt="archery"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Real-time insights</h4>
                    <p class="mb-0 leading-normal text-base">Leverage real-time lead and sales data to make informed decisions, optimize strategies, increase conversions, close deals faster, and keep your sales process running smoothly and efficiently.</p>
                </div>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/dashboard-monitor.png') }}" alt="dashboard-monitor"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Easy data import</h4>
                    <p class="mb-0 leading-normal text-base">Effortlessly import all leads, contacts, and customer details into a centralized CRM, saving time, maintaining accuracy, and ensuring your team can access and act on information instantly.</p>
                </div>
            </div>
            <div class="w-full md:w-1/2 xl:w-1/3 p-2" data-aos="fade-up">
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <img src="{{ asset('images/envelopes.png') }}" alt="envelopes"/>
                    <h4 class="mt-[24px] mb-[8px] font-bold text-[24px]">Simplified email marketing</h4>
                    <p class="mb-0 leading-normal text-base">Create, schedule, and send targeted email campaigns effortlessly, keeping leads engaged and boosting conversions without complicated setups or technical hassles.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start: Landing information -->
<div class="py-12 w-full overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap">
            <div class="w-full" data-aos="fade-down">
                <h3 class="text-3xl md:text-[2.5rem] leading-[1.2] font-[300] mb-8 text-center">With intelligent lead tracking, complete contact profiles, and built-in engagement tools, Owinace ensures no opportunity slips through and every deal progresses smoothly.</h3>
            </div>
        </div>
        <div class="flex flex-wrap">
            <div class="w-full lg:w-1/2 xl:w-1/3" data-aos="fade-up" data-aos-delay="200">
                <div class="lg:pr-4">
                    <span class="text-xl font-light">01.</span>
                    <div class="py-8 lg:py-12 border-t border-gray-900">
                        <p class="uppercase mb-1 text-[16px]">AI Personalization</p>
                        <h3 class="mb-0 text-[28px] font-[500] leading-[1.2]">Every Lead Tracked, Every Opportunity Captured</h3>
                    </div>
                    <p class="leading-normal">With intelligent lead tracking, complete contact profiles, and built-in engagement tools, Owinace ensures no opportunity slips through and every deal progresses smoothly.</p>
                    <a class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800" href="{{ url('/how-it-works') }}">
                        Learn more
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16" aria-hidden="true" fill="currentColor">
                            <path
                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="w-full lg:w-1/2 xl:w-1/3" data-aos="fade-up" data-aos-delay="400">
                <div class="lg:pr-4 xl:pt-12 xl:mt-12">
                    <span class="text-xl font-light">02.</span>
                    <div class="py-8 lg:py-12 border-t border-gray-900">
                        <p class="uppercase mb-1 text-[16px]">Job → Proposal</p>
                        <h3 class="mb-0 text-[28px] font-[500] leading-[1.2]">Drop in the job description—AI crafts a proposal that stands out.</h3>
                    </div>
                    <p class="leading-normal">AI reviews the requirements, identifies key skills, and generates a tailored proposal that highlights your strengths and fits the client's needs.</p>
                    <a class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800" href="{{ route('register') }}">
                        Try it now
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 16 16" aria-hidden="true" fill="currentColor">
                            <path
                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="w-full lg:w-full xl:w-1/3" data-aos="fade-up" data-aos-delay="600">
                <div class="xl:pl-4">
                    <span class="text-xl font-light">03.</span>
                    <div class="py-8 lg:py-12 border-t border-gray-900">
                        <p class="uppercase mb-1 text-[16px]">Team Collaboration</p>
                        <h3 class="mb-0 text-[28px] font-[500] leading-[1.2]">Collaborate Better, Close Faster</h3>
                    </div>
                    <p class="leading-normal">Invite teammates, manage responsibilities, and build proposals as one unified team. Perfect for organizations that want smoother workflows and better results.</p>
                    <div class="flex flex-wrap items-start mt-2 mb-4 gap-2">
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-indigo-100 text-indigo-700 hover:bg-indigo-200" href="#"
                            aria-label="AI Powered">AI-Powered Tools</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-yellow-100 text-yellow-700 hover:bg-yellow-200" href="#"
                            aria-label="Team Management">Team Workflows</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-blue-100 text-blue-700 hover:bg-blue-200" href="#"
                            aria-label="Portfolio Matching">Skill Matching</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-gray-900 text-white hover:bg-gray-800" href="#"
                            aria-label="Credit System">Usage Credits</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-green-100 text-green-700 hover:bg-green-200" href="#"
                            aria-label="Multiple Teams">Multiple Workspaces</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-red-100 text-red-700 hover:bg-red-200" href="#"
                            aria-label="Analytics">Performance Analytics</a>
                        <a class="text-sm rounded py-1 px-3 rounded-full font-normal bg-gray-200 text-gray-700 hover:bg-gray-300" href="#"
                            aria-label="Unlimited Proposals">Unlimited Proposals</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- how help section -->
<div class="py-16 w-full overflow-hidden how-help-section" >
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center items-center">
            <div class="w-full xl:w-1/2" data-aos="fade-right">
                <h2 class="mt-4 font-bold text-3xl">Why Lead Tracking Software Matters for Your Business?</h2>
                <p class="mt-4">Lead tracking software gives you complete visibility into your sales pipeline. It helps you understand how prospects move through each stage, what they're interested in, and when they're ready to convert—allowing your team to take smarter, faster actions.</p>
                <p class="mt-4">With a powerful lead tracking system you can:</p>
                <ul class="list-disc pl-5 space-y-2 mt-4">
                    <li><strong>Take action with clarity - </strong>Understand each lead's behavior, interests, and engagement history. This helps your team reach out with the right message at the right time, increasing the chances of conversion.</li>
                    <li><strong>Forecast pipeline performance - </strong>See where every lead stands and how your pipeline is shaping up. Get accurate projections that help you plan resources, targets, and follow-ups more efficiently.</li>
                    <li><strong>Strengthen marketing and sales alignment - </strong>Analyze which campaigns and channels bring the highest-quality leads. This allows you to refine your strategy, reduce wasted spend, and improve overall ROI.</li>
                    <li><strong>Eliminate guesswork and boost productivity - </strong>With automated status updates, reminders, and engagement tracking, your team stays organized and never loses track of an opportunity.</li>
                </ul>
            </div>
            <div class="w-full xl:w-1/2" data-aos="fade-left">
                <img src="{{ asset('images/kanban.png') }}" alt="track" class="max-w-full shadow-lg rounded-2xl w-full"/>
            </div>
        </div>
    </div>
</div>

<!-- Start: Dashboard Features -->
<div class="py-12 w-full overflow-hidden bg-gray-50" id="Features">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-between items-center gap-8">
            <div class="w-full xl:w-1/2 lg:w-7/12">
                <div class="flex flex-wrap gap-4 xl:gap-6">
                    <div class="w-full lg:w-11/12">
                        <img data-aos="fade-right" class="max-w-full shadow-lg rounded-2xl"
                            src="{{ asset('images/key-1.png') }}" alt="Owinace cover letter editor">
                    </div>
                    <div class="w-full lg:w-5/12 lg:ml-auto">
                        <img data-aos="fade-up" class="max-w-full shadow-lg rounded-2xl mt-6 lg:mt-12"
                            src="{{ asset('images/key-2.png') }}" alt="Owinace templates">
                    </div>
                    <div class="w-full lg:w-1/2">
                        <img data-aos="fade-left" class="max-w-full shadow-lg rounded-2xl mt-6 lg:mt-12"
                            src="{{ asset('images/key-3.png') }}" alt="Owinace analytics">
                    </div>
                </div>
            </div>
            <div class="w-full xl:w-5/12 lg:w-5/12">
                <div class="mt-8 lg:mt-0 lg:pl-4">
                    <p class="uppercase mb-2 text-sm" data-aos="fade-left" data-aos-delay="100">🚀 Key Features</p>
                    <div class="py-8 lg:py-12 border-t border-gray-900" data-aos="fade-left" data-aos-delay="150">
                        <p class="leading-normal mb-1">— everything you need to apply with confidence.</p>
                        <h3 class="mb-0 leading-normal text-2xl font-bold">AI-powered proposals, team collaboration, and portfolio management that helps you win more clients.</h3>
                    </div>
                    <ul class="pl-4 leading-relaxed space-y-2" data-aos="fade-left" data-aos-delay="200">
                        <li>🤖 AI proposal generation with portfolio keyword matching</li>
                        <li>👤 Multiple contacts per lead with primary contact support</li>
                        <li>📎 Attachments on leads + standalone attachment library</li>
                        <li>💬 Threaded comments and replies on lead pages</li>
                        <li>🏆 Team goals by member/type/period with duplicate protection</li>
                        <li>🕒 Owner activity log with filters and pagination</li>
                        <li>🏷️ Workspace tiers with order history and credit tracking</li>
                        <li>⚡ Generate proposals in under 2 minutes</li>
                        <li>🔄 Unlimited teams and proposals</li>
                    </ul>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors mt-6" data-aos="fade-left" data-aos-delay="250">
                        Start Free
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="ml-1" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 1 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- stratery Details -->
<div class="pt-24 w-full overflow-hidden stratery-div">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap mb-12">
            <div class="w-full">
                <h1 class="font-bold text-4xl">Lead Tracking Strategy</h1>
            </div>
        </div>
        <div class="flex flex-wrap mb-12 items-center ">
            <div class="w-full lg:w-1/2 pr-0 md:pr-8" data-aos="fade-right">
                <img src="{{ asset('images/analysis.png') }}" alt="strategy1" class="w-full max-w-full"/>
            </div>
            <div class="w-full lg:w-1/2" data-aos="fade-left">
                <h2 class="mb-4 font-bold text-3xl">Keep leads from slipping through the cracks</h2>
                <p class="mb-4">Without proper analytics, valuable leads often go unnoticed and opportunities are lost to competitors.</p>
                <p class="mb-4">Lead management analytics helps you track every interaction, measure lead quality and identify patterns that drive conversions. With real-time dashboards, automated insights and performance reports, you can spot bottlenecks early, improve lead scoring, optimize follow-ups and make data-driven decisions.</p>
                <p>Give your leads the attention they deserve with intelligent analytics that strengthen your sales pipeline and boost overall growth.</p>
            </div>
        </div>
        <div class="flex flex-wrap mb-12 items-center">
            <div class="w-full lg:w-1/2 order-2 lg:order-1" data-aos="fade-left">
                <h2 class="mb-4 font-bold text-3xl">Streamline your goals and performance with a unified workflow</h2>
                <p class="mb-4">Switching between multiple tools to manage goals, performance and costs creates unnecessary delays and confusion.</p>
                <p class="mb-4">A Kanban-based workflow helps you set clear objectives, track performance metrics, and maintain full visibility of project costs as tasks move through each stage. With organized boards, real-time status updates and automated insights, teams can prioritize work efficiently, reduce bottlenecks and stay aligned on targets.</p>
                <p>Simplify goal tracking, cost control and performance management with a cohesive, Kanban-driven system.</p>
            </div>
            <div class="w-full lg:w-1/2 order-1 lg:order-2 pl-0 md:pl-8" data-aos="fade-right">
                <img src="{{ asset('images/goal.png') }}" alt="goal" class="w-full max-w-full"/>
            </div>
        </div>
        <div class="flex flex-wrap mb-12 items-center">
            <div class="w-full lg:w-1/2 md:pr-8 pr-0" data-aos="fade-right">
                <img src="{{ asset('images/cover.png') }}" alt="cover" class="w-full max-w-full"/>
            </div>
            <div class="w-full lg:w-1/2" data-aos="fade-left">
                <h2 class="mb-4 font-bold text-3xl">Access the right insights at the right moment with AI</h2>
                <p class="mb-4">Accurate decision-making becomes difficult when teams rely on scattered, outdated or conflicting data.</p>
                <p class="mb-4">AI-driven data management centralizes your lead information, analyzes patterns across touchpoints and provides a reliable, unified view of every prospect. With automated insights and predictive reporting, you can quickly identify what's working, why it's working and where improvements are needed.</p>
                <p>Empower your team with real-time, AI-generated intelligence that supports smarter decisions and stronger outcomes.</p>
            </div>
        </div>
    </div>
</div>

<!-- Start: Numbers That Reflect Our Impact -->
<div class="py-12 w-full overflow-hidden" id="numbers">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-between items-center">
            <div class="w-full">
                <h3 class="py-8 lg:py-12 border-b mb-6 border-gray-900 text-3xl font-bold" data-aos="fade-right">Proven Results That Bring You More Clients</h3>
            </div>
            <div class="w-full">
                <div class="flex flex-wrap">
                    <div class="w-full">
                        <h3 class="text-3xl md:text-4xl mb-8" data-aos="fade-down">We deliver proposals that clearly demonstrate your value, fit client expectations, and help you close deals in record time.</h3>
                    </div>
                    <div class="w-full lg:w-1/2 mb-8" data-aos="fade-up">
                        <div class="text-7xl font-bold py-4 lg:py-8">95%</div>
                        <p class="text-gray-600 leading-normal">Users dramatically reduce proposal creation time while improving client engagement and win rates.</p>
                    </div>
                    <div class="w-full lg:w-1/2 mb-8" data-aos="fade-up">
                        <div class="text-7xl font-bold py-4 lg:py-8">10x</div>
                        <p class="text-gray-600 leading-normal">Proposal creation speed increases by 10x, helping teams and individuals respond faster and capture more opportunities.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start: AI-Powered Features -->
<div class="py-16 w-full overflow-hidden bg-gray-50" id="ai-features">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center text-center mb-12">
            <div class="w-full lg:w-2/3">
                <h2 class="text-4xl font-bold mb-6">Advanced AI That Helps You Win More Deals</h2>
                <p class="text-xl text-gray-600">Generate complete lead profiles in seconds and experience noticeably higher response and conversion rates.</p>
            </div>
        </div>
        <div class="flex flex-wrap justify-center">
            <div class="w-full lg:w-1/3 md:w-1/2 p-2" data-aos="fade-up">
                <div class="text-center p-6">
                    <div class="bg-indigo-100 rounded-full inline-flex items-center justify-center mb-6" style="width: 5rem; height: 5rem;">
                        <img src="{{ asset('images/performance.png') }}" alt="lead tracking" class="max-w-[3rem]"/>
                    </div>
                    <h5 class="font-bold mb-4 text-xl">Smart Lead Tracking</h5>
                    <p class="text-gray-600">AI automatically records lead details, tracks communication history, and keeps your contact data organized in one place.</p>
                </div>
            </div>

            <div class="w-full lg:w-1/3 md:w-1/2 p-2" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center p-6">
                    <div class="bg-green-100 rounded-full inline-flex items-center justify-center mb-6" style="width: 5rem; height: 5rem;">
                        <img src="{{ asset('images/web-analytics.png') }}" alt="lead status" class="max-w-[3rem]" />
                    </div>
                    <h5 class="font-bold mb-4 text-xl">Lead Status Monitoring</h5>
                    <p class="text-gray-600">Track each lead's progress — from initial contact to closed deal — with clear status updates and smart notifications.</p>
                </div>
            </div>

            <div class="w-full lg:w-1/3 md:w-1/2 p-2" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center p-6">
                    <div class="bg-yellow-100 rounded-full inline-flex items-center justify-center mb-6" style="width: 5rem; height: 5rem;">
                        <img src="{{ asset('images/exploratory-analysis.png') }}" alt="analytics" class="max-w-[3rem]" />
                    </div>
                    <h5 class="font-bold mb-4 text-xl">AI Insights & Analytics</h5>
                    <p class="text-gray-600">Get actionable insights into your lead's behavior, identify top opportunities, and make smarter decisions with AI analytics.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start: Team Collaboration -->
<div class="py-16 w-full overflow-hidden" id="team-collaboration">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center">
            <div class="w-full lg:w-1/2" data-aos="fade-right">
                <h2 class="text-4xl font-bold mb-6">Collaborate with Your Team</h2>
                <p class="text-xl text-gray-600 mb-6">Create teams, invite members, and work together on proposals. Perfect for agencies, freelancers, and growing businesses.</p>
                <div class="flex flex-wrap gap-4">
                    <div class="w-full md:w-1/2">
                        <div class="flex items-start gap-3">
                            <div class="bg-indigo-600 rounded-full flex items-center justify-center flex-shrink-0" style="min-width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/people.png') }}" alt="team" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-1">Unlimited Teams</h6>
                                <p class="text-gray-600 text-sm mb-0">Create as many teams as you need for different clients or projects.</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="flex items-start gap-3">
                            <div class="bg-green-600 rounded-full flex items-center justify-center flex-shrink-0" style="min-width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/flash.png') }}" alt="flash" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-1">Easy Invitations</h6>
                                <p class="text-gray-600 text-sm mb-0">Invite team members via email with role-based permissions.</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="flex items-start gap-3">
                            <div class="bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0" style="min-width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/portfolio (1).png') }}" alt="scale" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-1">Shared Portfolios</h6>
                                <p class="text-gray-600 text-sm mb-0">Team members can contribute to and use shared portfolio collections.</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="flex items-start gap-3">
                            <div class="bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0" style="min-width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/line-chart.png') }}" alt="track" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-1">Team Analytics</h6>
                                <p class="text-gray-600 text-sm mb-0">Track team performance and proposal success rates.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition-colors text-lg">
                        <i class="fas fa-rocket"></i>Start Free Trial
                    </a>
                </div>
            </div>
            <div class="w-full lg:w-1/2 mt-6" data-aos="fade-left">
                <div class="relative">
                    <div class="bg-indigo-50 rounded-2xl p-6 mb-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-indigo-600 rounded-full flex items-center justify-center mr-3" style="width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/expert.png') }}" alt="expert" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-0">Team Owner</h6>
                                <small class="text-gray-600">You</small>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-0">Create and manage teams, invite members, and oversee all proposal activities.</p>
                    </div>
                    <div class="bg-indigo-50 rounded-2xl p-6 mb-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-600 rounded-full flex items-center justify-center mr-3" style="width: 2.5rem; height: 2.5rem;">
                                <img src="{{ asset('images/group-users.png') }}" alt="team" class="w-5"/>
                            </div>
                            <div>
                                <h6 class="font-bold mb-0">Team Members</h6>
                                <small class="text-gray-600">Collaborators</small>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm mb-0">Team members can create proposals, manage portfolios, and contribute to team success.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start: Call to Action -->
<div class="py-16 w-full overflow-hidden bg-indigo-600 text-white" id="workwithus">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center text-center">
            <div class="w-full xl:w-3/4 lg:w-11/12 text-center" data-aos="fade">
                <p class="text-xl mb-4">Start growing faster with smarter lead management.</p>
                <h4 class="text-3xl md:text-4xl mb-8 font-bold">Turn more prospects into paying clients with AI-powered clarity, teamwork, and automation. Your success starts here.</h4>
            </div>
            <div class="w-full">
                <a class="inline-block px-8 py-4 text-2xl font-light bg-white text-indigo-600 shadow-lg hover:bg-gray-100 transition-colors rounded-lg mt-4 lg:mt-8" data-aos="fade-down"
                    href="{{ route('register') }}">Start Free</a>
            </div>
        </div>
    </div>
</div>
@endsection
