@if (count($fields))
    <div class="flex flex-wrap gap-1">
        @foreach ($fields as $field)
            <button
                type="button"
                x-on:click="
                    const el = $el.closest('.fi-doc-studio-merge-tag-group').querySelector('textarea');
                    el.setRangeText('{{ $field['tag'] }}', el.selectionStart, el.selectionEnd, 'end');
                    el.dispatchEvent(new Event('input'));
                    el.focus();
                "
                class="fi-badge fi-color-gray inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:ring-gray-600 dark:hover:bg-gray-700"
            >
                {{ $field['label'] }}
            </button>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">
        Set the template's model to see the merge fields it makes available.
    </p>
@endif
