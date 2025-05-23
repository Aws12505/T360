@tailwind base;
@tailwind components;
@tailwind utilities;

body,
html {
    --font-sans:
        'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
}

@layer base {
    :root {
        --background: 0 0% 100%;
        --foreground: 0 0% 3.9%;
        --card: 0 0% 100%;
        --card-foreground: 0 0% 3.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 0 0% 3.9%;
        
        /* Updated primary color to #2e368f (deep blue) */
        --primary: 235 51% 37%;
        --primary-foreground: 0 0% 98%;
        
        /* Updated secondary color to #e93232 (vibrant red) */
        --secondary: 0 80% 56%;
        --secondary-foreground: 0 0% 98%;
        
        --muted: 0 0% 96.1%;
        --muted-foreground: 0 0% 45.1%;
        --accent: 235 51% 95%;
        --accent-foreground: 235 51% 30%;
        --destructive: 0 84.2% 60.2%;
        --destructive-foreground: 0 0% 98%;
        --border: 0 0% 92.8%;
        --input: 0 0% 89.8%;
        --ring: 235 51% 37%;
        --chart-1: 235 51% 37%;
        --chart-2: 0 80% 56%;
        --chart-3: 197 37% 24%;
        --chart-4: 43 74% 66%;
        --chart-5: 27 87% 67%;
        --radius: 0.5rem;
        --sidebar-background: 235 51% 97%;
        --sidebar-foreground: 235 51% 26.1%;
        --sidebar-primary: 235 51% 37%;
        --sidebar-primary-foreground: 0 0% 98%;
        --sidebar-accent: 235 51% 94%;
        --sidebar-accent-foreground: 235 51% 30%;
        --sidebar-border: 235 51% 91%;
        --sidebar-ring: 235 51% 37%;
    }

    .dark {
        /* Updated background color to #081027 (deep navy blue) instead of black */
        --background: 222 85% 9%;
        --foreground: 0 0% 98%;
        --card: 222 85% 9%;
        --card-foreground: 0 0% 98%;
        --popover: 222 85% 9%;
        --popover-foreground: 0 0% 98%;
        
        /* Updated primary color for dark mode */
        --primary: 235 51% 60%;
        --primary-foreground: 0 0% 9%;
        
        /* Updated secondary color for dark mode */
        --secondary: 0 80% 60%;
        --secondary-foreground: 0 0% 98%;
        
        --muted: 0 0% 6.9%;
        --muted-foreground: 0 0% 63.9%;
        --accent: 235 51% 20%;
        --accent-foreground: 0 0% 98%;
        --destructive: 0 84% 60%;
        --destructive-foreground: 0 0% 98%;
        --border: 0 0% 14.9%;
        --input: 0 0% 14.9%;
        --ring: 235 51% 60%;
        --chart-1: 235 51% 60%;
        --chart-2: 0 80% 60%;
        --chart-3: 30 80% 55%;
        --chart-4: 280 65% 60%;
        --chart-5: 340 75% 55%;
        --sidebar-background: 235 51% 10%;
        --sidebar-foreground: 235 51% 95.9%;
        --sidebar-primary: 235 51% 60%;
        --sidebar-primary-foreground: 0 0% 100%;
        --sidebar-accent: 235 51% 15.9%;
        --sidebar-accent-foreground: 235 51% 95.9%;
        --sidebar-border: 235 51% 15.9%;
        --sidebar-ring: 235 51% 60%;
    }
}

@layer base {
    * {
        @apply border-border;
    }

    body {
        @apply bg-background text-foreground;
    }
}

/* Custom scrollbar styling */
@layer utilities {
    /* Webkit browsers (Chrome, Safari, newer Edge) */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    ::-webkit-scrollbar-track {
        background: hsl(var(--muted));
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb {
        background: hsl(var(--muted-foreground));
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: hsl(var(--primary) / 0.7);
    }

    /* For Firefox */
    * {
        scrollbar-width: thin;
        scrollbar-color: hsl(var(--muted-foreground)) hsl(var(--muted));
    }

    /* Dark mode specific scrollbar styling */
    .dark ::-webkit-scrollbar-track {
        background: hsl(var(--muted));
    }

    .dark ::-webkit-scrollbar-thumb {
        background: hsl(var(--muted-foreground));
    }

    .dark ::-webkit-scrollbar-thumb:hover {
        background: hsl(var(--primary) / 0.8);
    }

    .dark * {
        scrollbar-color: hsl(var(--muted-foreground)) hsl(var(--muted));
    }
}
