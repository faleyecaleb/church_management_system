@extends('frontend.layout')

@section('title', 'Upcoming Events - Hosanna Enterprise')

@section('content')
    <!-- Inner Header -->
    <section class="inner-header pt-32 pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white font-serif mb-4 reveal">Upcoming Events</h1>
            <p class="text-xl text-slate-300 max-w-2xl mx-auto reveal delay-100">Join us for times of fellowship, worship, and spiritual growth.</p>
        </div>
    </section>

    <!-- Events Listing -->
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Event Card 1 -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 reveal zoom-in">
                    <div class="relative h-56">
                        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Worship Night">
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur rounded-xl p-2 text-center min-w-[60px] shadow-sm">
                            <span class="block text-indigo-600 font-bold text-xl leading-none mb-1">15</span>
                            <span class="block text-slate-500 text-xs uppercase font-semibold">Aug</span>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center text-sm text-slate-500 mb-3">
                            <svg class="w-4 h-4 mr-2 text-fuchsia-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            6:00 PM - 9:00 PM
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Night of Worship & Healing</h3>
                        <p class="text-slate-600 font-light mb-6">An extended time of prophetic worship, prayer, and ministry. Come expectant for a mighty move of God.</p>
                        <a href="#" class="inline-flex items-center text-indigo-600 font-semibold hover:text-fuchsia-600 transition-colors group">
                            Event Details
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Event Card 2 -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 reveal zoom-in delay-100">
                    <div class="relative h-56">
                        <img src="https://images.unsplash.com/photo-1517457373958-b7bdd4587205?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Youth Conference">
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur rounded-xl p-2 text-center min-w-[60px] shadow-sm">
                            <span class="block text-indigo-600 font-bold text-xl leading-none mb-1">22</span>
                            <span class="block text-slate-500 text-xs uppercase font-semibold">Aug</span>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center text-sm text-slate-500 mb-3">
                            <svg class="w-4 h-4 mr-2 text-fuchsia-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            10:00 AM - 4:00 PM
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Youth Arise Conference</h3>
                        <p class="text-slate-600 font-light mb-6">A one-day intensive for teenagers and young adults focusing on purpose, career, and faith in the modern world.</p>
                        <a href="#" class="inline-flex items-center text-indigo-600 font-semibold hover:text-fuchsia-600 transition-colors group">
                            Register Now
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Event Card 3 -->
                <div class="bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 reveal zoom-in delay-200">
                    <div class="relative h-56">
                        <img src="https://images.unsplash.com/photo-1529390079861-591de354faf5?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Community Outreach">
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur rounded-xl p-2 text-center min-w-[60px] shadow-sm">
                            <span class="block text-indigo-600 font-bold text-xl leading-none mb-1">05</span>
                            <span class="block text-slate-500 text-xs uppercase font-semibold">Sep</span>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center text-sm text-slate-500 mb-3">
                            <svg class="w-4 h-4 mr-2 text-fuchsia-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Downtown City Center
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-3 font-serif">Community Outreach Drive</h3>
                        <p class="text-slate-600 font-light mb-6">Join us as we step out of the four walls of the church to distribute food, clothing, and the love of Christ.</p>
                        <a href="#" class="inline-flex items-center text-indigo-600 font-semibold hover:text-fuchsia-600 transition-colors group">
                            Volunteer
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection