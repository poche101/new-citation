<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Annual reports and citations') - CELZ5</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400..700;1,6..72,400..600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Palette taken from the Lagos Zone 5 logo: royal blue banner and gold wordmark
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Newsreader', 'Georgia', 'serif'],
                        sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        royal: { 50: '#F3F6FC', 100: '#E3EBFA', 200: '#C4D4F2', 500: '#2557C7', 600: '#1B47A6', 700: '#143A94', 800: '#0E2C73', 900: '#0B2461', 950: '#081B4B' },
                        gold: { 100: '#FBF1CF', 300: '#F6D77E', 400: '#F0BC3A', 500: '#E5A913', 600: '#C48F0C', 700: '#8A6200' },
                        ink: '#0B1633',
                        paper: '#F5F7FB',
                    },
                },
            },
        };
    </script>

    <style>
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Instrument Sans', system-ui, sans-serif; color: #0B1633; }
        :focus-visible { outline: 3px solid #E5A913; outline-offset: 3px; border-radius: 6px; }
        ::selection { background: #F6D77E; color: #081B4B; }

        /* The one moment of motion: the sample citation settles onto the page */
        @keyframes settle {
            from { opacity: 0; transform: translateY(22px) rotate(-5deg); }
            to   { opacity: 1; transform: translateY(0) rotate(-1.6deg); }
        }
        .settle { animation: settle .9s cubic-bezier(.2, .7, .2, 1) .15s both; transform: rotate(-1.6deg); }
        @media (prefers-reduced-motion: reduce) {
            .settle { animation: none; }
            * { scroll-behavior: auto !important; transition: none !important; }
        }
    </style>
    @stack('head')
</head>

<body class="bg-paper antialiased">
    @yield('content')

    @include('partials.public-footer')

    @stack('scripts')
</body>

</html>
