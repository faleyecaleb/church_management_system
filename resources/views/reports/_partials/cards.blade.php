<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($cards as $card)
        <div class="bg-white p-4 rounded-xl border shadow-sm">
            <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
            <div class="text-2xl font-semibold">{{ $card['value'] }}</div>
        </div>
    @endforeach
</div>