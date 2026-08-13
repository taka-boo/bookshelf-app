<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('お気に入り一覧') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($books->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($books as $book)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow relative"onclick="if(!event.target.closest('.fav-form'))window.location='{{ route('books.show', $book) }}'">
                                            <a href="{{ route('books.show', $book) }}" class="block" onclick="event.preventDefault();">
                                                @if($book->image_url)
                                                    <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-48 object-cover rounded mb-4">
                                                @else
                                                    <div class="w-full h-48 bg-gray-200 rounded mb-4 flex items-center justify-center">
                                                        <span class="text-gray-400">No Image</span>
                                                    </div>
                                                @endif
                                                <h3 class="font-bold text-lg mb-2 text-blue-600 hover:text-blue-800">
                                                    {{ $book->title }}
                                                </h3>
                                                <p class="text-gray-600 mb-2">{{ $book->author }}</p>
                                                <span class="text-sm text-gray-500">ISBN: {{ $book->isbn ?? '未登録' }}</span>
                                            </a>
                                            <div class="fav-form absolute bottom-4 right-4">
                                                <button type="button"
                                                    class="text-red-500 hover:text-red-700 bg-white rounded-full p-1 shadow"
                                                    id="fav-btn-{{ $book->id }}"
                                                    onclick="toggleFavorite({{ $book->id }}, '{{ route('favorites.toggle', $book) }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                            @endforeach
                            </div>
                            <div class="mt-6">
                                {{ $books->links() }}
                            </div>
                    @else
                        <p class="text-gray-500">お気に入りに登録された書籍はありません。</p>
                        <a href="{{ route('books.index') }}" class="mt-4 inline-block text-blue-600 hover:underline">
                            書籍一覧を見る
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleFavorite(bookId, url) {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var btn = document.getElementById('fav-btn-' + bookId);
                    if (data.action === 'removed') {
                        btn.classList.remove('text-red-500');
                        btn.classList.add('text-gray-300');
                    } else {
                        btn.classList.remove('text-gray-300');
                        btn.classList.add('text-red-500');
                    }

                    // フラッシュメッセージ表示
                    var existing = document.getElementById('flash-message');
                    if (existing) existing.remove();

                    var div = document.createElement('div');
                    div.id = 'flash-message';
                    div.className = 'fixed top-20 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded shadow-lg transition-opacity duration-700 '
                        + (data.action === 'removed'
                            ? 'bg-red-100 border border-red-400 text-red-700'
                            : 'bg-blue-100 border border-blue-400 text-blue-700');
                    div.textContent = data.message;
                    document.body.appendChild(div);

                    setTimeout(function () {
                        div.classList.add('opacity-0');
                        setTimeout(function () { div.remove(); }, 700);
                    }, 2500);
                });
            }
        </script>
    @endpush
</x-app-layout>