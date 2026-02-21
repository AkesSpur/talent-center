<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') — {{ config('app.name', 'Талант-центр') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Inter', sans-serif;
                background-color: #FAF8F5;
                color: #2C2416;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23D4AF37' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
            .error-card {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                border: 1px solid rgba(212, 175, 55, 0.2);
                padding: 3rem 2.5rem;
                max-width: 32rem;
                width: 100%;
                text-align: center;
            }
            .error-icon {
                width: 5rem;
                height: 5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
                font-size: 2rem;
            }
            .error-icon--warning {
                background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
                color: white;
            }
            .error-icon--danger {
                background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #8B4513 100%);
                color: white;
            }
            .error-code {
                font-family: 'Playfair Display', serif;
                font-size: 4.5rem;
                font-weight: 700;
                color: #8B4513;
                line-height: 1;
                margin-bottom: 0.5rem;
            }
            .error-title {
                font-family: 'Playfair Display', serif;
                font-size: 1.5rem;
                font-weight: 600;
                color: #2C2416;
                margin-bottom: 1rem;
            }
            .error-message {
                color: #9A8B7A;
                font-size: 0.95rem;
                line-height: 1.6;
                margin-bottom: 2rem;
            }
            .error-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.75rem;
                background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
                color: white;
                font-weight: 600;
                font-size: 0.9rem;
                border-radius: 0.5rem;
                text-decoration: none;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
            }
            .error-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
            }
            .error-btn--secondary {
                background: transparent;
                color: #8B4513;
                border: 2px solid #8B4513;
                box-shadow: none;
                margin-left: 0.75rem;
            }
            .error-btn--secondary:hover {
                background: #8B4513;
                color: white;
                box-shadow: 0 8px 20px rgba(139, 69, 19, 0.2);
            }
            .logo {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                margin-bottom: 2rem;
                text-decoration: none;
            }
            .logo-icon {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 8px rgba(212, 175, 55, 0.3);
            }
            .logo-icon i { color: white; font-size: 1.25rem; }
            .logo-text { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.125rem; color: #8B4513; }
            .logo-sub { font-size: 0.7rem; color: #9A8B7A; }
            .divider {
                width: 4rem;
                height: 3px;
                background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
                border-radius: 2px;
                margin: 0 auto 1.5rem;
            }
            .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.75rem; }
            @media (max-width: 480px) {
                .error-card { padding: 2rem 1.5rem; }
                .error-code { font-size: 3.5rem; }
                .error-title { font-size: 1.25rem; }
                .actions { flex-direction: column; }
                .error-btn--secondary { margin-left: 0; }
            }
        </style>
    </head>
    <body>
        <a href="/" class="logo">
            <div class="logo-icon">
                <i class="fas fa-award"></i>
            </div>
            <div>
                <div class="logo-text">Талант-центр</div>
                <div class="logo-sub">Всероссийский центр талантов</div>
            </div>
        </a>

        <div class="error-card">
            @yield('content')
        </div>
    </body>
</html>
