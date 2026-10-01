/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.twig',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        'warm-white': '#F8F5F0',
        'stone-light': '#EDEAE4',
        'wood': '#7C5C3E',
        'wood-dark': '#4E3728',
        'slate-green': '#4A6741',
        'sage': '#6B8F5E',
        'charcoal': '#2D2926',
        'muted': '#6B6460',
        'amber-craft': '#C98B2E',
        'brick': '#B94A48',
        'forest': '#3A6B35',
      },
      fontFamily: {
        'serif': ['"Playfair Display"', 'Georgia', 'serif'],
        'sans': ['"DM Sans"', 'system-ui', 'sans-serif'],
        'mono': ['"JetBrains Mono"', 'monospace'],
      },
      borderRadius: {
        'craft': '2px',
      },
      boxShadow: {
        'craft': '0 2px 8px 0 rgba(45, 41, 38, 0.08)',
        'craft-md': '0 4px 16px 0 rgba(45, 41, 38, 0.12)',
        'craft-lg': '0 8px 32px 0 rgba(45, 41, 38, 0.16)',
      },
      typography: (theme) => ({
        DEFAULT: {
          css: {
            color: theme('colors.charcoal'),
            a: { color: theme('colors.wood'), '&:hover': { color: theme('colors.wood-dark') } },
            h1: { fontFamily: theme('fontFamily.serif').join(', '), color: theme('colors.charcoal') },
            h2: { fontFamily: theme('fontFamily.serif').join(', '), color: theme('colors.charcoal') },
            h3: { fontFamily: theme('fontFamily.serif').join(', '), color: theme('colors.charcoal') },
          },
        },
      }),
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
};
