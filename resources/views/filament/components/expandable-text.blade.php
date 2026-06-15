<div
    x-data="{ expanded: false, hasMore: false }"
    x-init="$nextTick(() => { hasMore = $refs.text.scrollHeight > $refs.text.clientHeight })"
    class="text-sm text-gray-950 dark:text-white"
>
    <div
        x-ref="text"
        class="line-clamp-4 whitespace-pre-wrap break-words"
        :class="{ 'line-clamp-none': expanded }"
    >
        {{ $full }}
    </div>
    <button
        type="button"
        class="mt-1 font-medium text-primary-600 hover:underline dark:text-primary-400"
        x-show="hasMore && ! expanded"
        @click="expanded = true"
    >
        More
    </button>
    <button
        type="button"
        class="mt-1 font-medium text-primary-600 hover:underline dark:text-primary-400"
        x-show="hasMore && expanded"
        x-cloak
        @click="expanded = false"
    >
        Less
    </button>
</div>
