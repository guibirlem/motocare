import axios from 'axios'

// A API deve estar na mesma origem (proxy no Vite; reverse proxy em produção).
export const api = axios.create({ baseURL: '/api', timeout: 20000, headers: { Accept: 'application/json' } })
export const offlineMessage = 'Você está sem conexão. Conecte-se à internet para consultar ou salvar seus dados.'
api.interceptors.request.use(config => {
  if (globalThis.navigator?.onLine === false) {
    throw new axios.AxiosError(offlineMessage, 'MOTOCARE_OFFLINE', config)
  }
  return config
})
export async function prepareSession(isCurrent = () => true) {
  const { data } = await api.get('/csrf')
  if (isCurrent()) api.defaults.headers.common['X-CSRF-TOKEN'] = data.token
}
export function retryAfterSeconds(error, now = Date.now()) {
  const value = error.response?.headers?.['retry-after']
  if (value !== undefined && value !== null && String(value).trim() !== '') {
    const seconds = Number(value)
    if (Number.isFinite(seconds) && seconds >= 0) return Math.max(1, Math.ceil(seconds))
    const date = Date.parse(value)
    if (Number.isFinite(date)) return Math.max(1, Math.ceil((date - now) / 1000))
  }
  return 60
}
export function errorMessage(error, online = globalThis.navigator?.onLine !== false) {
  if (!error.response && (!online || error.code === 'MOTOCARE_OFFLINE')) return offlineMessage
  if (error.response?.status === 429) return `Muitas tentativas. Aguarde ${retryAfterSeconds(error)} segundos antes de tentar novamente.`
  if (error.response?.status === 401) return 'Entre na sua conta para continuar.'
  if (error.response?.status === 419) return 'Sua sessão expirou. Atualize a página e entre novamente.'
  if (error.response?.status >= 500) return 'O serviço está indisponível. Tente novamente em instantes.'
  if (!error.response && (error.request || error.code === 'ERR_NETWORK' || error.code === 'ECONNABORTED')) return 'Não foi possível conectar. Verifique sua conexão com a internet e tente novamente.'
  if (!error.response && !error.request && error.message) return error.message
  return Object.values(error.response?.data?.errors || {}).flat().join(' ') || error.response?.data?.message || 'Não foi possível conectar. Verifique sua conexão com a internet e tente novamente.'
}
