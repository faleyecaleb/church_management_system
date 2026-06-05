@extends('frontend.layout')

@section('title', 'Our Leadership - Hosanna Enterprise')

@section('content')
    <!-- Inner Header -->
    <section class="inner-header pt-32 pb-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white font-serif mb-4 reveal">Our Ministers</h1>
            <p class="text-xl text-slate-300 max-w-2xl mx-auto reveal delay-100">Meet the dedicated men and women called to serve and lead our congregation.</p>
        </div>
    </section>

    <!-- Ministers Section -->
    <section class="py-24 bg-white relative overflow-hidden">
        <!-- Decorative blobs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-fuchsia-50 rounded-full blur-[100px] opacity-60"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Lead Pastor -->
            <div class="mb-24 reveal">
                <div class="bg-slate-50 rounded-[3rem] p-8 md:p-12 flex flex-col md:flex-row items-center gap-12 shadow-xl border border-slate-100">
                    <div class="w-64 h-64 md:w-80 md:h-80 flex-shrink-0 relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-fuchsia-500 rounded-full transform translate-x-3 translate-y-3 opacity-20"></div>
                        <img src="https://images.unsplash.com/photo-1566492031773-4f4e44671857?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover rounded-full border-4 border-white shadow-lg relative z-10" alt="Lead Pastor">
                    </div>
                    <div>
                        <span class="text-indigo-600 font-bold tracking-widest uppercase text-sm mb-2 block">Lead Pastor</span>
                        <h3 class="text-4xl font-bold text-slate-900 mb-4 font-serif">Rev. David Emmanuel</h3>
                        <p class="text-lg text-slate-600 font-light leading-relaxed mb-6">
                            Rev. David is the visionary leader of Hosanna Enterprise. With over 20 years of ministry experience, his passion is to see lives transformed by the unadulterated word of God. He is a dynamic speaker, a compassionate counselor, and a spiritual father to many.
                        </p>
                        <div class="flex space-x-4">
                            <!-- Social icons placeholder -->
                            <a href="#" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departmental Ministers Grid -->
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl font-bold text-slate-900 font-serif">Departmental Ministers</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-indigo-500 to-fuchsia-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                
                <!-- Minister 1 -->
                <div class="group text-center reveal zoom-in delay-100">
                    <div class="w-48 h-48 mx-auto mb-6 relative overflow-hidden rounded-full shadow-lg border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Minister">
                    </div>
                    <h4 class="text-2xl font-bold text-slate-900 font-serif">Pst. Sarah Johnson</h4>
                    <p class="text-fuchsia-600 font-medium mb-3">Youth Church Pastor</p>
                    <p class="text-slate-500 font-light px-4">Passionate about raising a generation of young people deeply rooted in Christ and culturally relevant.</p>
                </div>

                <!-- Minister 2 -->
                <div class="group text-center reveal zoom-in delay-200">
                    <div class="w-48 h-48 mx-auto mb-6 relative overflow-hidden rounded-full shadow-lg border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Minister">
                    </div>
                    <h4 class="text-2xl font-bold text-slate-900 font-serif">Min. Michael Chen</h4>
                    <p class="text-indigo-600 font-medium mb-3">Music & Worship Director</p>
                    <p class="text-slate-500 font-light px-4">Leads the Hosanna Voices, creating an atmosphere of heaven on earth through prophetic worship.</p>
                </div>

                <!-- Minister 3 -->
                <div class="group text-center reveal zoom-in delay-300">
                    <div class="w-48 h-48 mx-auto mb-6 relative overflow-hidden rounded-full shadow-lg border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Minister">
                    </div>
                    <h4 class="text-2xl font-bold text-slate-900 font-serif">Pst. Grace Awolowo</h4>
                    <p class="text-teal-600 font-medium mb-3">Children's Ministry</p>
                    <p class="text-slate-500 font-light px-4">Dedicated to building a strong biblical foundation for kids in a fun, safe, and engaging environment.</p>
                </div>

            </div>
        </div>
    </section>
@endsection