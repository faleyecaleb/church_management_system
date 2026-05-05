<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
    
    body {
        font-family: 'Nunito', sans-serif;
        background-color: #f8fafc;
    }
    .sidebar-gradient {
        background: linear-gradient(135deg, #0d9488 0%, #115e59 100%); /* Professional Deep Teal */
    }
    .glass-effect {
        backdrop-filter: blur(12px);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border-radius: 16px;
    }
    .nav-link {
        transition: all 0.3s ease;
        color: #ccfbf1;
        border-radius: 10px;
    }
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
    }
    .nav-link.active {
        background: rgba(255, 255, 255, 0.15);
        border-left: 4px solid #5eead4; /* Soft teal accent */
        border-right: none;
        color: #ffffff;
    }
    .card-hover {
        transition: all 0.3s ease;
        border-radius: 16px;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }
    .fade-in {
        animation: childrenFadeIn 0.6s ease-out;
    }
    @keyframes childrenFadeIn {
        from { opacity: 0; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }
</style>