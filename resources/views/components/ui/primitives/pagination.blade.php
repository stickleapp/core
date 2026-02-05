@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => 0,
    'perPage' => 10,
    'showInfo' => true,
    'maxVisiblePages' => 5,
])

@php
$startItem = ($currentPage - 1) * $perPage + 1;
$endItem = min($currentPage * $perPage, $totalItems);

// Calculate visible page numbers
$pages = [];
if ($totalPages <= $maxVisiblePages) {
    $pages = range(1, $totalPages);
} else {
    $half = floor($maxVisiblePages / 2);
    $start = max(1, $currentPage - $half);
    $end = min($totalPages, $start + $maxVisiblePages - 1);

    if ($end - $start + 1 < $maxVisiblePages) {
        $start = max(1, $end - $maxVisiblePages + 1);
    }

    $pages = range($start, $end);
}
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-between w-full py-3']) }}>
    @if($showInfo)
        <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $startItem }}</span>
                to
                <span class="font-medium">{{ $endItem }}</span>
                of
                <span class="font-medium">{{ $totalItems }}</span>
                results
            </p>
        </div>
    @endif

    <nav class="flex items-center {{ $showInfo ? '' : 'w-full justify-center' }}">
        <ul class="flex items-center text-sm leading-tight bg-white border divide-x rounded h-9 text-neutral-500 divide-neutral-200 border-neutral-200">
            {{-- Previous Button --}}
            <li class="h-full">
                @if($currentPage > 1)
                    <a
                        href="?page={{ $currentPage - 1 }}"
                        class="relative inline-flex items-center h-full px-3 rounded-l hover:text-neutral-900 hover:bg-neutral-50"
                    >
                        <svg class="w-5 h-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Previous</span>
                    </a>
                @else
                    <span class="relative inline-flex items-center h-full px-3 rounded-l text-neutral-300 cursor-not-allowed">
                        <svg class="w-5 h-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Previous</span>
                    </span>
                @endif
            </li>

            {{-- Page Numbers --}}
            @foreach($pages as $page)
                <li class="hidden h-full md:block">
                    @if($page === $currentPage)
                        <span class="relative inline-flex items-center h-full px-3 text-neutral-900 bg-gray-50 font-medium">
                            {{ $page }}
                            <span class="box-content absolute bottom-0 left-0 w-full h-px -mx-px translate-y-px bg-neutral-900"></span>
                        </span>
                    @else
                        <a
                            href="?page={{ $page }}"
                            class="relative inline-flex items-center h-full px-3 hover:text-neutral-900 hover:bg-neutral-50"
                        >
                            {{ $page }}
                        </a>
                    @endif
                </li>
            @endforeach

            {{-- Next Button --}}
            <li class="h-full">
                @if($currentPage < $totalPages)
                    <a
                        href="?page={{ $currentPage + 1 }}"
                        class="relative inline-flex items-center h-full px-3 rounded-r hover:text-neutral-900 hover:bg-neutral-50"
                    >
                        <span class="hidden sm:inline">Next</span>
                        <svg class="w-5 h-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="relative inline-flex items-center h-full px-3 rounded-r text-neutral-300 cursor-not-allowed">
                        <span class="hidden sm:inline">Next</span>
                        <svg class="w-5 h-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
</div>
