{{-- Shared <head> contents for the admin pages. Expects $title. --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} - CELZ5 Citation</title>
<link rel="icon" type="image/png" href="{{ asset('images/logo2.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { theme: { extend: {
        fontFamily: { sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'] },
        colors: { ink: '#0B1633', paper: '#F5F7FB', teal: { DEFAULT: '#143A94', soft: '#E3EBFA' }, gold: { DEFAULT: '#E5A913', soft: '#FBF1CF', dark: '#8A6200' } }
    } } }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<style>[x-cloak]{display:none!important}</style>
