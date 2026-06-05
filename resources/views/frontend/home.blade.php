@extends('frontend.layout')

@section('title', 'Hosanna Enterprise - Welcome')

@section('content')
    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Image with Dark Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1548625361-195fe576b6bc?q=80&w=2000&auto=format&fit=crop" alt="Church Interior" class="w-full h-full object-cover object-center scale-105 animate-[pulse_20s_ease-in-out_infinite_alternate]" />
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/60 to-slate-900/95"></div>
        </div>

        <!-- Hero Content -->
        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto mt-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/10 text-fuchsia-300 font-medium tracking-[0.2em] uppercase mb-6 reveal text-sm">Welcome to CAC Hosanna Chapel</span>
            
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-6 leading-[1.1] reveal delay-100 text-glow tracking-tight">
                Experience God's <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 via-purple-400 to-indigo-400">Unending Love</span>
            </h1>
            
            <p class="text-lg md:text-2xl text-slate-300 mb-10 max-w-2xl mx-auto reveal delay-200 font-light leading-relaxed">
                A vibrant community of faith, hope, and transformation. Join us this Sunday and find your place to belong.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 reveal delay-300">
                <a href="#gatherings" class="px-8 py-4 rounded-full bg-gradient-to-r from-indigo-600 to-fuchsia-600 text-white font-semibold text-lg shadow-lg hover:shadow-[0_0_30px_rgba(168,85,247,0.5)] hover:-translate-y-1 transition-all duration-300 w-full sm:w-auto">
                    Plan Your Visit
                </a>
                <a href="{{ route('events') }}" class="group px-8 py-4 rounded-full bg-white/5 backdrop-blur-md border border-white/20 text-white font-semibold text-lg hover:bg-white hover:text-slate-900 transition-all duration-300 w-full sm:w-auto flex items-center justify-center">
                    View Events
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce">
            <a href="#mission" class="text-white/50 hover:text-white transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </a>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section id="mission" class="py-24 bg-slate-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal-left">
                    <h2 class="text-indigo-600 font-bold tracking-widest uppercase text-sm mb-4">Our Purpose</h2>
                    <h3 class="text-4xl md:text-5xl font-bold text-slate-900 font-serif mb-6">Loving God, Loving People, Serving the World.</h3>
                    <p class="text-lg text-slate-600 leading-relaxed mb-8">
                        At Hosanna, we believe that faith is more than a Sunday activity—it's a journey of transformation. Our mission is to create a space where everyone, regardless of their background, can encounter the living God and find a community that feels like home.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">The Word</h4>
                                <p class="text-slate-500 text-sm">Grounded in the timeless truth of Scripture.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 bg-fuchsia-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">The Spirit</h4>
                                <p class="text-slate-500 text-sm">Led by the presence and power of God.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative reveal-right">
                    <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=1000&auto=format&fit=crop" alt="Church Community" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" />
                    </div>
                    <!-- Decorative element -->
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-gradient-to-br from-indigo-500 to-fuchsia-500 rounded-2xl -z-10 animate-pulse"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gatherings Section (Multi-tenancy Showcase) -->
    <section id="gatherings" class="py-24 bg-white relative overflow-hidden">
        <!-- Decorative Gradients -->
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-fuchsia-100 rounded-full blur-[120px] opacity-60"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-indigo-100 rounded-full blur-[120px] opacity-60"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16 reveal">
                <h2 class="text-indigo-600 font-bold tracking-widest uppercase text-sm">Our Gatherings</h2>
                <h3 class="mt-3 text-4xl md:text-5xl font-bold text-slate-900 font-serif">A Place For Everyone</h3>
                <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto">Discover a specialized worship experience tailored to every stage of life.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Adult Church -->
                <div class="group relative rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 reveal-left delay-100 bg-white">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/95 to-slate-900/95 opacity-90 group-hover:opacity-100 transition-opacity z-10"></div>
                    <img src="https://images.unsplash.com/photo-1544427920-c49ccbc8e29e?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay z-0 group-hover:scale-110 transition-transform duration-700" alt="Adult Church" />
                    <div class="relative z-20 p-8 h-full flex flex-col justify-end min-h-[420px]">
                        <div class="w-14 h-14 rounded-2xl bg-blue-500/20 backdrop-blur-md flex items-center justify-center mb-6 border border-blue-400/20">
                            <svg class="w-7 h-7 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h4 class="text-3xl font-bold text-white mb-3 font-serif">Adult Church</h4>
                        <p class="text-blue-100/80 mb-8 font-light text-lg leading-relaxed">Deep teachings, classic worship, and a mature community of believers growing together.</p>
                        <div class="inline-flex items-center text-white font-medium group-hover:text-blue-300 transition-colors">
                            Sundays 9:00 AM <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Youth Church -->
                <div class="group relative rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 zoom-in delay-200 bg-white">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/95 to-purple-900/95 opacity-90 group-hover:opacity-100 transition-opacity z-10"></div>
                    <img src="https://images.unsplash.com/photo-1526976663112-004cb68019a3?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay z-0 group-hover:scale-110 transition-transform duration-700" alt="Youth Church" />
                    <div class="relative z-20 p-8 h-full flex flex-col justify-end min-h-[420px]">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 backdrop-blur-md flex items-center justify-center mb-6 border border-indigo-400/20">
                            <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h4 class="text-3xl font-bold text-white mb-3 font-serif">Youth Church</h4>
                        <p class="text-indigo-100/80 mb-8 font-light text-lg leading-relaxed">Vibrant worship, relevant word, and an energetic atmosphere for the next generation.</p>
                        <div class="inline-flex items-center text-white font-medium group-hover:text-indigo-300 transition-colors">
                            Sundays 11:30 AM <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Children Church -->
                <div class="group relative rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 reveal-right delay-300 bg-white">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-900/95 to-emerald-900/95 opacity-90 group-hover:opacity-100 transition-opacity z-10"></div>
                    <img src="https://images.unsplash.com/photo-1511895426328-dc8714191300?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay z-0 group-hover:scale-110 transition-transform duration-700" alt="Children Church" />
                    <div class="relative z-20 p-8 h-full flex flex-col justify-end min-h-[420px]">
                        <div class="w-14 h-14 rounded-2xl bg-teal-500/20 backdrop-blur-md flex items-center justify-center mb-6 border border-teal-400/20">
                            <svg class="w-7 h-7 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-3xl font-bold text-white mb-3 font-serif">Children Church</h4>
                        <p class="text-teal-100/80 mb-8 font-light text-lg leading-relaxed">A safe, fun, and highly engaging environment where kids learn about God's immense love.</p>
                        <div class="inline-flex items-center text-white font-medium group-hover:text-teal-300 transition-colors">
                            Sundays 9:00 AM <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Plan Your Visit Section -->
    <section class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <img src="https://images.unsplash.com/photo-1510076857177-7470076d4098?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Background" />
            <div class="absolute inset-0 bg-slate-900"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal-left">
                    <h2 class="text-fuchsia-400 font-bold tracking-widest uppercase text-sm mb-4">First Time?</h2>
                    <h3 class="text-4xl md:text-5xl font-bold text-white font-serif mb-8">We Can't Wait to <br/>Welcome You</h3>
                    <p class="text-lg text-slate-300 mb-10 leading-relaxed">
                        Visiting a new church can be intimidating. We want to make it as easy as possible for you to find your way and feel at home from the moment you arrive.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-center text-slate-200">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center mr-6 border border-white/10">
                                <svg class="w-6 h-6 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold">Location</h4>
                                <p class="text-sm text-slate-400">123 Faith Avenue, Grace City</p>
                            </div>
                        </div>
                        <div class="flex items-center text-slate-200">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center mr-6 border border-white/10">
                                <svg class="w-6 h-6 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold">Service Times</h4>
                                <p class="text-sm text-slate-400">Sundays: 9:00 AM & 11:30 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-8 sm:p-10 rounded-3xl reveal-right">
                    <h4 class="text-2xl font-bold text-white mb-6">Plan Your Visit</h4>
                    <form action="#" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input type="text" placeholder="First Name" class="w-full px-5 py-4 bg-white/10 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 transition-all" />
                            <input type="text" placeholder="Last Name" class="w-full px-5 py-4 bg-white/10 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 transition-all" />
                        </div>
                        <input type="email" placeholder="Email Address" class="w-full px-5 py-4 bg-white/10 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 transition-all" />
                        <select class="w-full px-5 py-4 bg-white/10 border border-white/10 rounded-xl text-slate-400 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 transition-all appearance-none">
                            <option value="">Which service will you attend?</option>
                            <option value="9am">9:00 AM (Adult/Children)</option>
                            <option value="1130am">11:30 AM (Youth)</option>
                        </select>
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-fuchsia-500/20 transition-all transform hover:-translate-y-1">
                            Notify Us of Your Visit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Sermon Section -->
    <section class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-indigo-600 font-bold tracking-widest uppercase text-sm">Latest Message</h2>
                <h3 class="mt-3 text-4xl md:text-5xl font-bold text-slate-900 font-serif">Feed Your Soul</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-center">
                <div class="lg:col-span-2 reveal-left">
                    <div class="relative aspect-video rounded-3xl overflow-hidden shadow-2xl group cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1515162305285-0293e4767cc2?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="Sermon Thumbnail" />
                        <div class="absolute inset-0 bg-slate-900/40 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                            <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/30 group-hover:scale-110 transition-transform">
                                <svg class="w-10 h-10 text-white fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-slate-900 to-transparent">
                            <span class="px-3 py-1 bg-fuchsia-500 text-white text-xs font-bold rounded-full mb-3 inline-block">Latest Sunday</span>
                            <h4 class="text-2xl font-bold text-white">The Power of Purposeful Living</h4>
                        </div>
                    </div>
                </div>
                
                <div class="reveal-right">
                    <div class="space-y-8">
                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-shadow">
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Watch Online</h4>
                            <p class="text-slate-500 mb-4 text-sm">Join our live stream every Sunday at 9:00 AM & 11:30 AM.</p>
                            <a href="#" class="text-indigo-600 font-semibold flex items-center hover:translate-x-1 transition-transform">
                                Visit YouTube Channel <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                        
                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-shadow">
                            <h4 class="text-xl font-bold text-slate-900 mb-2">Podcast</h4>
                            <p class="text-slate-500 mb-4 text-sm">Listen to our messages on the go via Spotify or Apple Podcasts.</p>
                            <a href="#" class="text-indigo-600 font-semibold flex items-center hover:translate-x-1 transition-transform">
                                Listen Now <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>

                        <a href="#" class="block w-full py-4 text-center border-2 border-indigo-600 text-indigo-600 font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                            Browse Sermon Archive
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 bg-fuchsia-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-fuchsia-600 font-bold tracking-widest uppercase text-sm">Stories of Grace</h2>
                <h3 class="mt-3 text-4xl md:text-5xl font-bold text-slate-900 font-serif">Life Transformation</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-white shadow-xl reveal-left delay-100">
                    <div class="flex text-fuchsia-400 mb-4">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <p class="text-slate-600 mb-8 italic leading-relaxed">"Joining Hosanna was the best decision for my family. The community here is truly authentic and the youth ministry has been life-changing for my kids."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-slate-200 mr-4"></div>
                        <div>
                            <h5 class="font-bold text-slate-900">Sarah Johnson</h5>
                            <p class="text-xs text-slate-500">Member since 2022</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-white shadow-xl zoom-in delay-200">
                    <div class="flex text-fuchsia-400 mb-4">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <p class="text-slate-600 mb-8 italic leading-relaxed">"I found hope again at Hosanna. The teachings are practical and the people are so welcoming. It's more than a church; it's a family."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-slate-200 mr-4"></div>
                        <div>
                            <h5 class="font-bold text-slate-900">David Chen</h5>
                            <p class="text-xs text-slate-500">Member since 2021</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-white shadow-xl reveal-right delay-300">
                    <div class="flex text-fuchsia-400 mb-4">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                    <p class="text-slate-600 mb-8 italic leading-relaxed">"The worship here is incredible. It's so easy to connect with God in such a vibrant atmosphere. I've grown so much spiritually."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-slate-200 mr-4"></div>
                        <div>
                            <h5 class="font-bold text-slate-900">Michael Williams</h5>
                            <p class="text-xs text-slate-500">Member since 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Giving Section -->
<section class="py-24 bg-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-indigo-900 rounded-[3rem] overflow-hidden relative shadow-2xl">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-white/10 to-transparent pointer-events-none"></div>

            <div class="relative z-10 px-8 py-16 sm:px-16 sm:py-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="reveal-left">
                    <h2 class="text-indigo-300 font-bold tracking-widest uppercase text-sm mb-4">Generosity</h2>
                    <h3 class="text-4xl md:text-5xl font-bold text-white font-serif mb-6">Invest in Eternal <br/>Impact</h3>
                    <p class="text-lg text-indigo-100/80 mb-10 leading-relaxed">
                        Your generosity enables us to reach more people with the message of hope, support those in need, and grow our community. Together, we can make a difference that lasts forever.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#" class="px-8 py-4 bg-white text-indigo-900 font-bold rounded-xl shadow-lg hover:shadow-white/20 transition-all text-center">
                            Give Online Now
                        </a>
                        <a href="#" class="px-8 py-4 bg-indigo-800 text-white font-bold rounded-xl border border-indigo-700 hover:bg-indigo-700 transition-all text-center">
                            Other Ways to Give
                        </a>
                    </div>
                </div>
                <div class="reveal-right">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center">
                            <div class="text-3xl font-bold text-white mb-1">100%</div>
                            <div class="text-xs text-indigo-300 uppercase tracking-wider">Transparency</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center">
                            <div class="text-3xl font-bold text-white mb-1">Secure</div>
                            <div class="text-xs text-indigo-300 uppercase tracking-wider">Giving Portal</div>
                        </div>
                        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center col-span-2">
                            <div class="text-indigo-200 text-sm">"Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver." <br/> <span class="font-bold">- 2 Corinthians 9:7</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-24 bg-slate-50 border-t border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="reveal">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-2xl mb-6">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-3xl font-bold text-slate-900 mb-4">Stay Connected</h3>
            <p class="text-slate-500 mb-10 text-lg">Receive weekly updates, encouragement, and event notifications directly in your inbox.</p>

            <form action="#" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="email" placeholder="Your email address" class="flex-grow px-6 py-4 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm" required />
                <button type="submit" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-1">
                    Subscribe
                </button>
            </form>
            <p class="mt-4 text-xs text-slate-400">We respect your privacy. Unsubscribe at any time.</p>
        </div>
    </div>
</section>

<!-- Quick Links / CTA -->
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-700 via-purple-700 to-fuchsia-700 z-0"></div>

    <div class="relative z-10 max-w-4xl mx-auto text-center px-4 reveal">
...
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-8 font-serif">Explore More</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('about') }}" class="p-6 rounded-2xl bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 transition-all text-white group">
                    <svg class="w-8 h-8 mx-auto mb-3 text-fuchsia-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">About Us</span>
                </a>
                <a href="{{ route('ministers') }}" class="p-6 rounded-2xl bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 transition-all text-white group">
                    <svg class="w-8 h-8 mx-auto mb-3 text-fuchsia-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="font-medium">Leadership</span>
                </a>
                <a href="{{ route('events') }}" class="p-6 rounded-2xl bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 transition-all text-white group">
                    <svg class="w-8 h-8 mx-auto mb-3 text-fuchsia-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="font-medium">Events</span>
                </a>
                <a href="{{ route('ministries') }}" class="p-6 rounded-2xl bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 transition-all text-white group">
                    <svg class="w-8 h-8 mx-auto mb-3 text-fuchsia-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="font-medium">Ministries</span>
                </a>
            </div>
        </div>
    </section>
@endsection