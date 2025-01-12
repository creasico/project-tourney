import type { AxiosStatic } from 'axios'

export {}

declare global {
  type AppLocale = 'id' | 'en'
  const axios: AxiosStatic

  interface Window {
    axios: AxiosStatic
  }
}
