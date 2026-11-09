{{--
    Confirmation window for deleting a report.

    The page must provide an Alpine state called `deleteTarget` (null, or
    { url, summary }) on an ancestor element. A "Delete" button opens it with:
        @click="deleteTarget = { url: '...', summary: 'Personal citation by John Doe' }"
--}}
<div x-show="deleteTarget" x-cloak
     x-effect="if (deleteTarget) $nextTick(() => $refs.cancelDelete.focus())"
     @keydown.escape.window="deleteTarget = null"
     class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-4"
     @click.self="deleteTarget = null">

    <form method="POST" :action="deleteTarget ? deleteTarget.url : '#'"
          role="alertdialog" aria-modal="true" aria-labelledby="delete-title" aria-describedby="delete-text"
          class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        @csrf
        <input type="hidden" name="_method" value="DELETE">

        <div class="flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600" aria-hidden="true">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2m2 0l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 5v6m4-6v6" /></svg>
            </span>
            <div>
                <h3 id="delete-title" class="text-lg font-bold">Delete this submission?</h3>
                <p class="mt-1 font-semibold text-slate-700" x-text="deleteTarget ? deleteTarget.summary : ''"></p>
                <p id="delete-text" class="mt-2 text-sm leading-relaxed text-slate-500">
                    This permanently deletes the submission and any files uploaded with it. This cannot be undone.
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2 text-sm font-semibold">
            <button type="button" x-ref="cancelDelete" @click="deleteTarget = null"
                    class="rounded-xl px-4 py-2.5 text-slate-600 hover:bg-paper">Cancel</button>
            <button type="submit"
                    class="rounded-xl bg-red-600 px-5 py-2.5 text-white hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2">Delete</button>
        </div>
    </form>
</div>
