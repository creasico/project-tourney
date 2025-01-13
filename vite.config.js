import laravel, { refreshPaths } from 'laravel-vite-plugin'
import { defineConfig, loadEnv } from 'vite'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, '.', ['APP', 'SENTRY'])
  const isDev = ['local', 'testing'].includes(env.APP_ENV)

  /** @type {import('vite').UserConfigFnObject} */
  return {
    build: {
      sourcemap: isDev || 'SENTRY_AUTH_TOKEN' in env,
      reportCompressedSize: false,
      chunkSizeWarningLimit: 2000,

      /**
       * @see https://rollupjs.org/configuration-options/#output-manualchunks
       */
      rollupOptions: {
        output: {
          manualChunks: (id) => {
            if (id.includes('node_modules'))
              return 'vendor'
          },
        },
      },
    },

    define: {
      'import.meta.env.APP_NAME': JSON.stringify(env.APP_NAME),
      'import.meta.env.APP_LOCALE': JSON.stringify(env.APP_LOCALE),
      'import.meta.env.APP_URL': JSON.stringify(env.APP_URL),
      'import.meta.env.APP_ENV': JSON.stringify(env.APP_ENV),
      'import.meta.env.SENTRY_DSN': JSON.stringify(env.SENTRY_DSN),
      'import.meta.env.SENTRY_PROFILING_ENABLE': Boolean(env.SENTRY_PROFILING_ENABLE ?? 0),
    },

    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: [
          ...refreshPaths,
          'app/Livewire/**',
        ],
      }),
    ],
  }
})
