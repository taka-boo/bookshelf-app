<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('書籍一覧') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- 検索フォーム -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('books.index') }}" method="GET" class="space-y-4" novalidate>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">キーワード</label>
                                <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="タイトル・著者で検索">
                            </div>
                            <div>
                                <label for="genre" class="block text-sm font-medium text-gray-700 mb-1">ジャンル</label>
                                <select name="genre" id="genre" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">すべて</option>
                                    @foreach($genres ?? [] as $genre)
                                        <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                                            {{ $genre->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">並び順</label>
                                <select name="sort" id="sort" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>新しい順</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>古い順</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>評価順</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>タイトル順</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    検索
                                </button>
                                <a href="{{ route('books.index') }}" class="text-gray-600 hover:text-gray-900">
                                    リセット
                                </a>
                            </div>
                            <a href="{{ route('books.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                書籍を登録
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(request('keyword') || request('genre'))
                <div class="mb-4 text-gray-600">
                    検索結果: {{ $books->total() }}件
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($books->isEmpty())
                        <p class="text-gray-500">書籍が見つかりませんでした。</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($books as $book)
                                <div class="border rounded-lg p-4 shadow hover:shadow-lg transition cursor-pointer relative"
                                    onclick="if(!event.target.closest('form'))window.location='{{ route('books.show', $book) }}'">
                                    @if($book->image_url)
                                        <img src="{{ $book->image_url }}" alt="{{ $book->title }}" class="w-full h-48 object-cover mb-4 rounded">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center mb-4 rounded">
                                            <span class="text-gray-500">画像なし</span>
                                        </div>
                                    @endif
                                    <h3 class="font-bold text-lg mb-2 text-blue-600 hover:text-blue-800">
                                        {{ $book->title }}
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">{{ $book->author }}</p>
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @foreach($book->genres as $genre)
                                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">{{ $genre->name }}</span>
                                        @endforeach
                                    </div>
                                    @if($book->reviews_avg_rating)
                                        <div class="flex items-center">
                                            <span class="text-yellow-500">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= round($book->reviews_avg_rating))
                                                        ★
                                                    @else
                                                        ☆
                                                    @endif
                                                @endfor
                                            </span>
                                            <span class="text-sm text-gray-500 ml-2">
                                                ({{ number_format($book->reviews_avg_rating, 1) }})
                                            </span>
                                        </div>
                                    @endif
                                    @auth
                                        <form action="{{ route('favorites.toggle', $book) }}" method="POST" class="absolute bottom-4 right-4">
                                            @csrf
                                            <button type="submit"
                                                class="{{ in_array($book->id, $favoriteBookIds) ? 'text-red-500' : 'text-gray-300' }} hover:text-red-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endauth
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $books->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
