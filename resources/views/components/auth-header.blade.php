@props(['title', 'description'])

<div class="flex w-full flex-col text-center mb-6">
    <h2 class="text-2xl font-bold text-forest-800 dark:text-forest-100 mb-2">{{ $title }}</h2>
    <p class="text-sage-600 dark:text-sage-300 text-sm leading-relaxed">{{ $description }}</p>
</div>
