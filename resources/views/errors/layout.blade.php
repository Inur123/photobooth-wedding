<!DOCTYPE html>
<html lang="id" translate="no" class="notranslate" style="color-scheme: light;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta name="color-scheme" content="light">
    <title>@yield('title') | titip.moment</title>

    <link rel="icon" type="image/png" href="/images/titip-momen-logo-web.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: light !important;
            --font-display: 'Playfair Display', Georgia, serif;
            --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            --color-coral: #ff5d6c;
            --color-coral-deep: #d92c3f;
            --color-peach: #ffdad8;
            --color-cream: #faf8f5;
            --color-surface: #ffffff;
            --color-ink: #1d1c29;
            --color-muted: #777586;
            --color-border: #e6dad7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--color-cream);
            color: var(--color-ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Ambient subtle glow */
        .ambient-glow-1 {
            position: absolute;
            top: -100px;
            left: -100px;
            width: 350px;
            height: 350px;
            background: rgba(255, 218, 216, 0.45);
            filter: blur(80px);
            border-radius: 50%;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            top: 50%;
            right: -100px;
            width: 350px;
            height: 350px;
            background: rgba(255, 198, 164, 0.35);
            filter: blur(80px);
            border-radius: 50%;
            pointer-events: none;
        }

        header {
            width: 100%;
            max-width: 1024px;
            margin: 0 auto;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
        }

        .logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-img {
            height: 28px;
            width: auto;
            object-fit: contain;
        }

        .home-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .home-link:hover {
            color: var(--color-ink);
        }

        main {
            width: 100%;
            max-width: 580px;
            margin: 0 auto;
            padding: 24px 20px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .card {
            width: 100%;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            box-shadow: 0 10px 30px -5px rgba(29, 28, 41, 0.05);
            padding: 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-accent-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffdad8, #ff5d6c, #fd9e78);
        }

        .icon-box {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: #f8f6f2;
            border: 1px solid var(--color-border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            color: var(--color-coral);
        }

        .icon-box svg {
            width: 32px;
            height: 32px;
        }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: var(--color-peach);
            color: var(--color-coral-deep);
            margin-bottom: 8px;
        }

        .code-display {
            font-family: var(--font-display);
            font-size: 54px;
            font-weight: 800;
            color: rgba(29, 28, 41, 0.12);
            line-height: 1;
            user-select: none;
            margin-top: 4px;
        }

        h1 {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 700;
            color: var(--color-ink);
            margin-top: 8px;
            line-height: 1.25;
        }

        p.desc {
            font-size: 15px;
            color: var(--color-muted);
            margin: 12px auto 0;
            line-height: 1.6;
            max-width: 440px;
        }

        .actions {
            margin-top: 32px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--color-coral);
            color: #ffffff;
            border: 1px solid transparent;
            box-shadow: 0 2px 6px rgba(255, 93, 108, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--color-coral-deep);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--color-ink);
            border: 1px solid var(--color-border);
        }

        .btn-secondary:hover {
            background-color: #f6f3ee;
        }

        footer {
            width: 100%;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--color-muted);
            position: relative;
            z-index: 10;
        }

        @media (max-width: 640px) {
            .card {
                padding: 32px 20px;
                border-radius: 20px;
            }
            .code-display {
                font-size: 42px;
            }
            h1 {
                font-size: 22px;
            }
            .actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <header>
        <a href="/" class="logo-link" aria-label="Beranda titip.moment">
            <img src="/images/titip-momen-logo-web.png" alt="titip.moment" class="logo-img">
        </a>
        <a href="/" class="home-link">← Beranda</a>
    </header>

    <main>
        <div class="card">
            <div class="card-accent-line"></div>
            
            <div class="icon-box">
                @yield('icon')
            </div>

            <div class="badge">@yield('badge')</div>
            <div class="code-display">@yield('code')</div>
            <h1>@yield('title')</h1>
            <p class="desc">@yield('description')</p>

            <div class="actions">
                @yield('actions')
            </div>
        </div>
    </main>

    <footer>
        <p>© titip.moment · Mengabadikan setiap tawa dan doa di hari bahagia.</p>
    </footer>
</body>
</html>
