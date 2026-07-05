@php
    $turningAge = $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->diffInYears(now()) : null;
@endphp
<div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center gap-4">
        <!-- Profile Photo -->
        <div class="flex-shrink-0">
            <img class="w-12 h-12 rounded-full object-cover border-2 border-slate-100" 
                 src="{{ $member->profile_photo_url }}" 
                 alt="{{ $member->full_name }}">
        </div>
        
        <!-- Member Info -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-extrabold text-slate-800 truncate">{{ $member->full_name }}</h4>
            <div class="flex flex-wrap gap-1.5 mt-1">
                @if($turningAge)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $type === 'today' ? 'bg-fuchsia-100 text-fuchsia-800' : 'bg-slate-100 text-slate-700' }}">
                        Turning {{ $turningAge }}
                    </span>
                @endif
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                    {{ $member->phone ?? 'No Phone' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Action Channels -->
    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
        @if($member->phone)
            <div class="flex gap-2.5">
                <!-- Direct Call -->
                <a href="tel:{{ $member->phone }}" 
                   title="Call Member" 
                   class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </a>
                
                <!-- WhatsApp -->
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" 
                   target="_blank" 
                   title="Send WhatsApp Message" 
                   class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition-colors">
                    <!-- WhatsApp SVG Icon -->
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.753-1.458L0 24zm6.59-4.846c1.785 1.059 3.57 1.6 5.348 1.601 5.372 0 9.745-4.343 9.749-9.682.002-2.585-1.01-5.016-2.85-6.843-1.84-1.826-4.28-2.83-6.861-2.83-5.384 0-9.756 4.344-9.76 9.684-.001 1.944.512 3.84 1.492 5.509l-.973 3.55 3.654-.95z"/>
                    </svg>
                </a>
            </div>
        @else
            <span class="text-[10px] text-gray-400 italic">No phone contact</span>
        @endif

        @if($member->email)
            <!-- Mail -->
            <a href="mailto:{{ $member->email }}" 
               title="Send Email" 
               class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </a>
        @else
            <span class="text-[10px] text-gray-400 italic">No email</span>
        @endif
    </div>
</div>
