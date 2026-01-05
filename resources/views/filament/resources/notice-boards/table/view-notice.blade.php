<div class="space-y-4">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $record->title }}</h2>

    @if ($record->description)
        <div class="prose max-w-none text-gray-800 dark:prose-invert dark:text-gray-200">
            {!! $record->description !!}
        </div>
    @endif
</div>


