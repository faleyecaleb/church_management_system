@extends('frontend.layout')

@section('title', 'About Us - Hosanna Enterprise')

@section('content')
    <!-- Inner Header -->
    <section class="inner-header pt-32 pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white font-serif mb-4 reveal">About Us</h1>
            <p class="text-xl text-slate-300 max-w-2xl mx-auto reveal delay-100">Discover our history, mission, and the core values that drive our community.</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-24 bg-slate-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Image Collage -->
                <div class="relative reveal-left">
                    <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden relative z-10 shadow-2xl border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1529070538774-1843cb3265df?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Church Community" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                    </div>
                    <!-- Decorative Element -->
                    <div class="absolute -bottom-10 -right-10 w-72 h-72 bg-gradient-to-br from-indigo-500 to-fuchsia-500 rounded-full blur-[80px] opacity-40 z-0"></div>
                </div>
                
                <!-- Text Content -->
                <div class="reveal-right lg:pl-10">
                    <h2 class="text-fuchsia-600 font-bold tracking-widest uppercase text-sm mb-3">Our Story</h2>
                    <h3 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6 font-serif leading-[1.2]">More than just a church, we are family.</h3>
                    <p class="text-lg text-slate-600 mb-6 font-light leading-relaxed">
                        At Hosanna Enterprise, we believe in creating an atmosphere where everyone can encounter God's love, discover their God-given purpose, and build lasting, impactful relationships. 
                    </p>
                    <p class="text-lg text-slate-600 mb-8 font-light leading-relaxed">
                        Founded on the principles of love, faith, and community service, our mission is to reach the unreached and build up believers to live victorious lives through the teachings of Jesus Christ.
                    </p>
                    
                    <h4 class="text-2xl font-bold text-slate-900 mb-4 font-serif">Our Core Values</h4>
                    <ul class="space-y-4 mb-10">
                        <li class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                            <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-4">1</span>
                            <span class="text-slate-800 font-medium">Uncompromising Biblical Truth</span>
                        </li>
                        <li class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                            <span class="w-8 h-8 rounded-full bg-fuchsia-100 text-fuchsia-600 flex items-center justify-center font-bold mr-4">2</span>
                            <span class="text-slate-800 font-medium">Authentic Worship & Prayer</span>
                        </li>
                        <li class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                            <span class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center font-bold mr-4">3</span>
                            <span class="text-slate-800 font-medium">Compassionate Community Service</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection