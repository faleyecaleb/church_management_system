@extends('frontend.layout')

@section('title', 'Ministries - Hosanna Enterprise')

@section('content')
    <!-- Inner Header -->
    <section class="inner-header pt-32 pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white font-serif mb-4 reveal">Our Ministries</h1>
            <p class="text-xl text-slate-300 max-w-2xl mx-auto reveal delay-100">Find your place to serve, grow, and connect within the body of Christ.</p>
        </div>
    </section>

    <!-- Ministries Listing -->
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Ministry Card 1 -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Hosanna Voices (Choir)</h3>
                    <p class="text-slate-600 font-light mb-6">Lead the congregation in spirit-filled worship during our services. If you have a gift for singing or playing instruments, this is your home.</p>
                    <a href="#" class="text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">Join Ministry &rarr;</a>
                </div>

                <!-- Ministry Card 2 -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal delay-100">
                    <div class="w-16 h-16 rounded-2xl bg-fuchsia-50 flex items-center justify-center text-fuchsia-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Ushering & Protocol</h3>
                    <p class="text-slate-600 font-light mb-6">The first point of contact! Ensure services run smoothly, maintain order, and make every guest feel welcomed and comfortable.</p>
                    <a href="#" class="text-fuchsia-600 font-semibold hover:text-fuchsia-800 transition-colors">Join Ministry &rarr;</a>
                </div>

                <!-- Ministry Card 3 -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal delay-200">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Media & Technical</h3>
                    <p class="text-slate-600 font-light mb-6">Manage sound, lighting, live streaming, and visual presentations. Help broadcast the message of Christ to the world.</p>
                    <a href="#" class="text-teal-600 font-semibold hover:text-teal-800 transition-colors">Join Ministry &rarr;</a>
                </div>
                
                 <!-- Ministry Card 4 -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 reveal delay-300">
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Prayer Band</h3>
                    <p class="text-slate-600 font-light mb-6">The engine room of the church. Stand in the gap for the congregation, the leadership, and the community at large.</p>
                    <a href="#" class="text-rose-600 font-semibold hover:text-rose-800 transition-colors">Join Ministry &rarr;</a>
                </div>

            </div>
        </div>
    </section>
@endsection