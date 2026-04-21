<x-filament-panels::page>
    @php
        $workspace = $this->getWorkspace();
    @endphp

    <div class="max-w-3xl space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Workspace API token
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                This is your workspace API token. Use it to authenticate bots, browser extensions, and other integrations
                against the LeadCliq API. Treat it like a password: do not share it publicly or commit it to source control.
            </p>

            @if($workspace?->token)
                <div
                    class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center"
                    x-data="{ copied: false }"
                >
                    <code
                        id="workspace-api-token"
                        class="fi-input block w-full min-w-0 flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 font-mono text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                    >{{ $workspace->token }}</code>
                    <x-filament::button
                        type="button"
                        color="gray"
                        tag="button"
                        x-on:click="
                            navigator.clipboard.writeText(document.getElementById('workspace-api-token').textContent.trim());
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                    >
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Copied</span>
                    </x-filament::button>
                </div>
            @else
                <p class="mt-4 text-sm text-danger-600 dark:text-danger-400">
                    No token is set for this workspace. Use &ldquo;Reset token&rdquo; to generate one.
                </p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
