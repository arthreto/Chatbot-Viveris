<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boulixien - Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --bg: #0a0e27;
            --bg-light: #1a1f3a;
            --text: #f8fafc;
            --text-muted: #cbd5e1;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg) 0%, var(--bg-light) 50%, #1e293b 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-x: hidden;
            position: relative;
        }

        /* Animations de fond */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            animation: rotate 25s linear infinite reverse;
            z-index: 0;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .container {
            max-width: 1200px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInDown 0.8s ease-out;
        }

        .logo {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .subtitle {
            font-size: 1.25rem;
            color: var(--text-muted);
            font-weight: 300;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .service-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .service-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .service-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .service-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--primary);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
        }

        .service-card:hover::before {
            opacity: 0.1;
        }

        .service-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            transition: transform 0.4s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.2) rotate(5deg);
        }

        .service-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .service-description {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .service-link {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
            height: 100%;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Responsive */
        @media (max-width: 768px) {
            .logo {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .service-card {
                padding: 2rem 1.5rem;
            }

            .service-icon {
                font-size: 3rem;
            }

            .service-title {
                font-size: 1.5rem;
            }
        }

        /* Effet de particules */
        .particle {
            position: fixed;
            width: 4px;
            height: 4px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float 15s infinite;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            25% {
                transform: translateY(-100px) translateX(50px);
            }
            50% {
                transform: translateY(-200px) translateX(-50px);
            }
            75% {
                transform: translateY(-100px) translateX(100px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">Boulixien</h1>
            <p class="subtitle">Découvrez nos services innovants</p>
        </div>

        <div class="services-grid">
            <a href="https://boulixien.velocitystudios.fr/chatbot" class="service-link">
                <div class="service-card">
                    <div class="service-icon">🤖</div>
                    <h2 class="service-title">Chatbot</h2>
                    <p class="service-description">
                        Interagissez avec notre intelligence artificielle conversationnelle. 
                        Posez vos questions et obtenez des réponses en temps réel.
                    </p>
                </div>
            </a>

            <a href="https://boulixien.velocitystudios.fr/ergonome" class="service-link">
                <div class="service-card">
                    <div class="service-icon">🎯</div>
                    <h2 class="service-title">Ergonome</h2>
                    <p class="service-description">
                        Optimisez l'ergonomie de vos espaces de travail. 
                        Solutions adaptées pour améliorer le confort et la productivité.
                    </p>
                </div>
            </a>

            <a href="https://boulixien.velocitystudios.fr/musique" class="service-link">
                <div class="service-card">
                    <div class="service-icon">🎵</div>
                    <h2 class="service-title">Musique</h2>
                    <p class="service-description">
                        Explorez notre collection musicale. 
                        Découvrez des sons, des playlists et des créations originales.
                    </p>
                </div>
            </a>
        </div>
    </div>

    <script>
        // Ajout de particules animées en arrière-plan
        function createParticles() {
            const particleCount = 20;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                document.body.appendChild(particle);
            }
        }

        document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>

