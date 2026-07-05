@extends('layouts.admin')

@section('title', 'Birthday Board')
@section('header', 'Birthday Board')

@section('content')
<style>
    /* Premium Birthday styling */
    .birthday-card-today {
        border: 2px solid rgba(240, 147, 251, 0.4);
        box-shadow: 0 0 25px rgba(240, 147, 251, 0.2);
        background: rgba(255, 255, 255, 0.15) !important;
    }
    .badge-today {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .badge-tomorrow {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .badge-yesterday {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>

<div class="space-y-8 fade-in">
    <!-- Header Banner -->
    <div class="glass-effect rounded-3xl p-8 text-white relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="relative z-10">
            <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 border border-white/10 text-fuchsia-300 font-semibold tracking-wider text-xs uppercase mb-3">Celebrations</span>
            <h2 class="text-3xl font-extrabold font-serif mb-2">Member Birthday Board</h2>
            <p class="text-white/80 max-w-xl">Stay connected with your members. Review birthdays from yesterday, today, and tomorrow to send timely blessings and remain actively engaged.</p>
        </div>
        <div class="relative z-10 flex gap-4">
            <a href="{{ route('members.index') }}" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white font-medium text-sm transition-all duration-300">
                View Directory
            </a>
        </div>
        <!-- Decorative glowing bubble -->
        <div class="absolute -right-20 -bottom-20 w-60 h-60 bg-fuchsia-500/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Birthday Board Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Yesterday -->
        <div class="glass-effect rounded-3xl p-6 flex flex-col h-full bg-white/40">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl badge-yesterday flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Yesterday</h3>
                        <p class="text-xs text-gray-500">{{ now()->subDay()->format('F d') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700">{{ $yesterdayBirthdays->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto flex-1 max-h-[600px] pr-2">
                @forelse($yesterdayBirthdays as $member)
                    @include('members._birthday_card', ['member' => $member, 'type' => 'yesterday'])
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <p class="text-sm">No birthdays yesterday</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Today -->
        <div class="glass-effect rounded-3xl p-6 flex flex-col h-full birthday-card-today relative bg-white/40">
            <!-- Confetti decor elements -->
            <div class="absolute top-2 right-2 text-fuchsia-400 opacity-60 animate-bounce">✨</div>
            <div class="absolute bottom-4 left-2 text-indigo-400 opacity-60 animate-bounce delay-150">🎈</div>

            <div class="flex justify-between items-center mb-6 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl badge-today flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5 animate-wiggle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Today</h3>
                        <p class="text-xs text-fuchsia-600 font-bold uppercase tracking-wider">{{ now()->format('F d') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold badge-today text-white shadow">{{ $todayBirthdays->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto flex-1 max-h-[600px] pr-2 relative z-10">
                @forelse($todayBirthdays as $member)
                    @include('members._birthday_card', ['member' => $member, 'type' => 'today'])
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <p class="text-sm">No birthdays today</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tomorrow -->
        <div class="glass-effect rounded-3xl p-6 flex flex-col h-full bg-white/40">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl badge-tomorrow flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Tomorrow</h3>
                        <p class="text-xs text-gray-500">{{ now()->addDay()->format('F d') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $tomorrowBirthdays->count() }}</span>
            </div>

            <div class="space-y-4 overflow-y-auto flex-1 max-h-[600px] pr-2">
                @forelse($tomorrowBirthdays as $member)
                    @include('members._birthday_card', ['member' => $member, 'type' => 'tomorrow'])
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm">No birthdays tomorrow</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
