@php
    $skills = is_array($job?->skills) ? $job->skills : [];
    $questions = is_array($job?->questions) ? $job->questions : [];
    $coverLetter = $coverLetter ?? null;
    $qa = $qa ?? null;
    $analyzeResult = $analyzeResult ?? null;
    $analyzeMatched = $analyzeResult['is_matched'] ?? false;
    $analyzeReason = $analyzeResult['reason'] ?? '';

    $formatMoney = fn ($value): string => filled($value) ? '$' . number_format((float) $value, 2) : '—';
    $formatNumber = fn ($value): string => filled($value) ? number_format((int) $value) : '—';
    $formatPercent = fn ($value): string => filled($value) ? number_format((float) $value, 1) . '%' : '—';
@endphp

@if (! $job)
    <p class="text-sm text-gray-500 dark:text-gray-400">Job details are not available.</p>
@else
    <div class="space-y-6 text-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1 space-y-1">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $job->title }}</h2>
                <p class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $job->uid }}</p>
            </div>
            @if (filled($job->url))
                <a href="{{ $job->url }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">
                    View on Upwork
                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                </a>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($stat->is_matched)
                <span
                    class="inline-flex items-center rounded-md bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20">
                    Matched
                </span>
            @else
                <span
                    class="inline-flex items-center rounded-md bg-danger-50 px-2.5 py-1 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20">
                    Not matched
                </span>
            @endif
            @if ($stat->is_applied)
                <span
                    class="inline-flex items-center rounded-md bg-info-50 px-2.5 py-1 text-xs font-medium text-info-700 ring-1 ring-inset ring-info-600/20 dark:bg-info-400/10 dark:text-info-400 dark:ring-info-400/20">
                    Applied
                </span>
            @endif
            @if ($job->is_expired)
                <span
                    class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-300 dark:ring-gray-400/20">
                    Expired
                </span>
            @endif
            @if ($job->is_warm)
                <span
                    class="inline-flex items-center rounded-md bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20">
                    Warm
                </span>
            @endif
        </div>

        @if (filled($stat->note))
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Campaign note</h3>
                <p class="mt-1 leading-relaxed text-gray-700 dark:text-gray-200">{{ $stat->note }}</p>
            </div>
        @endif

        @if (filled($analyzeResult))
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-white/10 dark:bg-gray-800/50">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">AI job analysis</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Match evaluation using this campaign's portfolios, experience, and criteria.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Match:</span>
                    @if ($analyzeMatched)
                        <span
                            class="inline-flex items-center rounded-md bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20">
                            Yes — good fit
                        </span>
                    @else
                        <span
                            class="inline-flex items-center rounded-md bg-danger-50 px-2.5 py-1 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20">
                            No — weak or poor fit
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Reason</h4>
                    <p class="mt-1 leading-relaxed text-gray-700 dark:text-gray-200">{{ $analyzeReason ?: '—' }}</p>
                </div>
            </div>
        @endif

        @if (filled($coverLetter))
            <div class="rounded-xl border border-primary-200 bg-primary-50/50 p-4 dark:border-primary-500/30 dark:bg-primary-500/10">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Test cover letter</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Generated using this campaign's AI prompt and portfolios.</p>
                <div
                    class="mt-3 max-h-[40vh] overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                    <pre class="whitespace-pre-wrap font-sans text-sm text-gray-800 dark:text-gray-100">{{ $coverLetter }}</pre>
                </div>
                @if (filled($qa))
                    <h4 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">Question answers</h4>
                    <div
                        class="mt-2 max-h-[30vh] overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                        <pre class="whitespace-pre-wrap font-sans text-sm text-gray-800 dark:text-gray-100">{{ $qa }}</pre>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->type ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Posted</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">
                    {{ filled($job->posted_at) ? \Illuminate\Support\Carbon::parse($job->posted_at)->format('M j, Y g:i A') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Proposals</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->proposals) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Connects</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->connects) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Interviews</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->interviews) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Invites sent</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->invites_sent) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Location</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->location ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred location</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->preferred_location ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred talent</dt>
                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->preferred_talent ?: '—' }}</dd>
            </div>
        </div>

        @if ($skills !== [])
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Skills</h3>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($skills as $skill)
                        <span
                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ is_array($skill) ? ($skill['name'] ?? json_encode($skill)) : $skill }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if (filled($job->description))
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Description</h3>
                <div class="mt-2 max-h-64 overflow-y-auto whitespace-pre-wrap leading-relaxed text-gray-700 dark:text-gray-200">
                    {{ $job->description }}
                </div>
            </div>
        @endif

        <div>
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Client</h3>
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->client_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Organization</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->client_org ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Rating</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        {{ filled($job->client_rating) ? number_format((float) $job->client_rating, 1) . ' / 5' : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total spent</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatMoney($job->client_totalspent) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Hire rate</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatPercent($job->client_hirerate) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Hires</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->client_hires ?? $job->hires) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Jobs posted</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->client_jobposted) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Open jobs</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatNumber($job->client_openjob) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Avg. spent</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatMoney($job->client_avgspent) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Avg. hourly rate</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $formatMoney($job->client_avghourlyrate) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Member since</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                        {{ filled($job->client_since) ? \Illuminate\Support\Carbon::parse($job->client_since)->format('M j, Y') : '—' }}
                    </dd>
                </div>
                @if (filled($job->client_website))
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Website</dt>
                        <dd class="mt-1">
                            <a href="{{ $job->client_website }}" target="_blank" rel="noopener noreferrer"
                                class="text-primary-600 hover:underline dark:text-primary-400">
                                {{ $job->client_website }}
                            </a>
                        </dd>
                    </div>
                @endif
                @if (filled($job->client_project))
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Project</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $job->client_project }}</dd>
                    </div>
                @endif
            </div>
        </div>

        @if ($questions !== [])
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Screening questions</h3>
                <ol class="mt-2 list-decimal space-y-2 pl-5 text-gray-700 dark:text-gray-200">
                    @foreach ($questions as $question)
                        <li>{{ is_array($question) ? ($question['text'] ?? json_encode($question)) : $question }}</li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
@endif
