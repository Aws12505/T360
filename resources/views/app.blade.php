<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>
 <script>
    fetch('/refresh-csrf', {
        credentials: 'same-origin'
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Failed to refresh CSRF token');
        }
        return res.json();
    })
    .then(data => {
        console.log('New CSRF Token:', data.csrfToken);

        // Update meta tag
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrfToken);

        // Optional: If using Axios
        if (window.axios) {
            window.axios.defaults.headers.common['X-XSRF-TOKEN'] = data.csrfToken;
        }
    })
    .catch(err => {
        console.error('Error refreshing CSRF:', err);
    });
</script>


        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
