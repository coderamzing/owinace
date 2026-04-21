<div class="fi-prose space-y-6 text-sm">
    <div>
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Cover letter</h3>
        <div
            class="mt-2 max-h-[45vh] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
            <pre class="whitespace-pre-wrap font-sans text-gray-800 dark:text-gray-100">{{ $coverLetter }}</pre>
        </div>
    </div>
    @if (filled($qa))
        <div>
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Question answers</h3>
            <div
                class="mt-2 max-h-[35vh] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                <pre class="whitespace-pre-wrap font-sans text-gray-800 dark:text-gray-100">{{ $qa }}</pre>
            </div>
        </div>
    @endif
</div>
