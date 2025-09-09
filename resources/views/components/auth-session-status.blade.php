@props(['status'])

@if ($status)
    <div
        {{ $attributes->merge(['class' => 'px-4 py-3 bg-forest-100 dark:bg-forest-800 border border-forest-200 dark:border-forest-700 rounded-xl text-sm text-forest-700 dark:text-forest-200 font-medium']) }}>
        {{ $status }}
    </div>
@endif
