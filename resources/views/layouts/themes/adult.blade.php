<style>
    body {
        font-family: 'Inter', serif; /* Use serif for headers if possible, sans for body */
        background-color: #f8fafc;
    }
    .sidebar-gradient {
        background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); /* Deep rich blue */
    }
    .glass-effect {
        backdrop-filter: blur(12px);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .nav-link {
        transition: all 0.4s ease;
        color: #cbd5e1;
        border-radius: 4px;
    }
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
    }
    .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        border-left: 4px solid #bfdbfe; /* Subtle gold/blue accent */
        border-right: none;
        color: #ffffff;
    }
    .card-hover {
        transition: all 0.4s ease;
        border-radius: 8px;
    }
    .card-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }
    .fade-in {
        animation: adultFadeIn 0.8s ease-out;
    }
    @keyframes adultFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    'sans': ['Inter', 'sans-serif'],
                },
                colors: {
                    primary: {
                        50: '#f0f9ff',
                        500: '#3b82f6',
                        600: '#2563eb',
                        700: '#1d4ed8',
                    },
                    secondary: {
                        50: '#f8fafc',
                        100: '#f1f5f9',
                        200: '#e2e8f0',
                        500: '#64748b',
                        600: '#475569',
                        700: '#334155',
                        800: '#1e293b',
                        900: '#0f172a',
                    }
                }
            }
        }
    }
</script>
