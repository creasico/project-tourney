'use strict'

import antfu from '@antfu/eslint-config'

export default antfu({
  formatters: {
    css: true,
  },
  ignores: [
    'public/**',
    'storage/**',
  ],
  typescript: {},
})
