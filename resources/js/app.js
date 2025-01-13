import axios from 'axios'

import '@fontsource-variable/inter'

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
