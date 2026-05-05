<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }
    .sidebar-gradient {
        background: linear-gradient(135deg, #4338ca 0%, #312e81 100%); /* Professional Deep Indigo */
    }
    .glass-effect {
        backdrop-filter: blur(12px);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .nav-link {
        transition: all 0.3s ease;
        color: #e0e7ff;
        border-radius: 8px;
    }
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        transform: translateX(3px);
    }
    .nav-link.active {
        background: rgba(255, 255, 255, 0.12);
        border-left: 4px solid #818cf8; /* Soft indigo accent */
        border-right: none;
        color: #ffffff;
    }
    .card-hover {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }
    .fade-in {
        animation: youthFadeIn 0.5s ease-out;
    }
    @keyframes youthFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>