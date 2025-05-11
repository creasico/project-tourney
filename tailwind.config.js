import defaultTheme from 'tailwindcss/defaultTheme'
import preset from './vendor/filament/support/tailwind.config.preset'

/** @type {import('tailwindcss').Config} */
export default {
  presets: [preset],

  content: [
    './app/Filament/**/*.php',
    './resources/views/**/*.blade.php',
    // './storage/framework/views/*.php',
    // './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    // './vendor/fillament/**/*.blade.php',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter Variable', ...defaultTheme.fontFamily.sans],
      },
    },
  },
}
