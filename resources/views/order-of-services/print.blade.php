<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order of Service - {{ $service->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'serif': ['Playfair Display', 'serif'],
                        'sans': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'church-blue': '#1e40af',
                        'church-gold': '#f59e0b',
                        'church-purple': '#7c3aed',
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .print-container { box-shadow: none !important; margin: 0 !important; }
            .gradient-bg { background: white !important; }
            .text-shadow { text-shadow: none !important; }
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .text-shadow {
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .item-card {
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .order-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .duration-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .leader-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .timeline-line {
            background: linear-gradient(to bottom, #667eea, #764ba2, #667eea);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
    </style>
</head>
<body class="min-h-screen gradient-bg font-sans">
    <!-- Print Button -->
    <button onclick="window.print()" 
            class="no-print fixed top-6 right-6 z-50 print-btn text-white px-6 py-3 rounded-xl font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span>Print</span>
    </button>

    <!-- Back Button -->
    <a href="{{ route('services.order-of-services.index', $service->id) }}" 
       class="no-print fixed top-6 left-6 z-50 bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2 hover:bg-white/30 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back</span>
    </a>

    <div class="container mx-auto px-4 py-8 max-w-4xl print-container">
        <!-- Header Section -->
        <div class="glass-effect rounded-3xl p-8 mb-8 text-center animate-fade-in-up">
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                    <svg class="w-10 h-10 text-church-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-serif font-bold text-gray-800 mb-2 text-shadow">
                    {{ config('app.name', 'Church Management System') }}
                </h1>
                <div class="w-24 h-1 bg-gradient-to-r from-church-purple to-church-blue mx-auto mb-4"></div>
                <h2 class="text-2xl font-serif font-semibold text-gray-700 mb-2">Order of Service</h2>
                <div class="text-lg text-gray-600 space-y-1">
                    <p class="font-medium">{{ $service->name }}</p>
                    <p class="text-base">{{ $service->day_of_week_name }}s at {{ $service->start_time->format('h:i A') }}</p>
                    <p class="text-sm text-gray-500">{{ now()->format('F j, Y') }}</p>
                </div>
            </div>

            @if($totalDuration > 0)
                <div class="inline-flex items-center bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-full shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">Total Duration: {{ $totalDuration }} minutes</span>
                </div>
            @endif
        </div>

        @if($orderOfServices->count() > 0)
            <!-- Timeline Container -->
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute left-8 top-0 bottom-0 w-1 timeline-line rounded-full opacity-30"></div>
                
                <!-- Service Items -->
                <div class="space-y-6">
                    @foreach($orderOfServices as $index => $item)
                        <div class="relative animate-fade-in-up stagger-{{ ($index % 4) + 1 }}">
                            <!-- Timeline Dot -->
                            <div class="absolute left-6 top-6 w-5 h-5 bg-white border-4 border-church-purple rounded-full shadow-lg z-10"></div>
                            
                            <!-- Item Card -->
                            <div class="ml-20 item-card rounded-2xl p-6 border border-gray-100">
                                <!-- Item Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="order-number w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                            {{ $item->order }}
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-serif font-semibold text-gray-800 mb-1">
                                                {{ $item->program }}
                                            </h3>
                                            @if($item->leader)
                                                <div class="inline-flex items-center leader-badge text-white px-3 py-1 rounded-full text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    {{ $item->leader }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Time and Duration -->
                                    <div class="text-right space-y-2">
                                        @if($item->start_time && $item->end_time)
                                            <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium">
                                                {{ $item->time_range }}
                                            </div>
                                        @elseif($item->start_time)
                                            <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium">
                                                {{ $item->start_time->format('h:i A') }}
                                            </div>
                                        @endif
                                        
                                        @if($item->duration > 0)
                                            <div class="inline-flex items-center duration-badge text-white px-3 py-1 rounded-full text-sm font-medium">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $item->duration }} min
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                @if($item->description)
                                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                        <p class="text-gray-700 leading-relaxed">{{ $item->description }}</p>
                                    </div>
                                @endif
                                
                                <!-- Notes -->
                                @if($item->notes)
                                    <div class="border-l-4 border-yellow-400 bg-yellow-50 pl-4 py-3 rounded-r-lg">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-yellow-800 mb-1">Notes:</p>
                                                <p class="text-sm text-yellow-700">{{ $item->notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="glass-effect rounded-3xl p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-serif font-semibold text-gray-600 mb-2">No Program Items</h3>
                <p class="text-gray-500">The order of service has not been set up yet.</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-12 glass-effect rounded-3xl p-6 text-center">
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Generated on {{ now()->format('F j, Y \a\t g:i A') }}
                </div>
                <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                <div>{{ config('app.name', 'Church Management System') }}</div>
            </div>
        </div>
    </div>

    <script>
        // Add print styles and functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation delays
            const items = document.querySelectorAll('.animate-fade-in-up');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.opacity = '1';
                }, index * 100);
            });
        });

        // Enhanced print function
        function printPage() {
            // Hide all no-print elements
            const noPrintElements = document.querySelectorAll('.no-print');
            noPrintElements.forEach(el => el.style.display = 'none');
            
            // Print
            window.print();
            
            // Restore no-print elements
            setTimeout(() => {
                noPrintElements.forEach(el => el.style.display = '');
            }, 1000);
        }
    </script>
</body>
</html>