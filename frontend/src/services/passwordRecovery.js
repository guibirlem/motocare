import { api, prepareSession } from './api.js'

export function readResetLink(hash) {
  const values = new URLSearchParams(hash.replace(/^#/, ''))
  const token = values.get('token') || '', email = values.get('email') || ''
  if (!/^[a-f0-9]{64}$/.test(token) || email.length > 255 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return null
  return { token, email }
}

export async function requestPasswordReset(email) {
  await prepareSession()
  const { data } = await api.post('/forgot-password', { email: email.trim() })
  return data.message
}

export async function resetPassword(credentials) {
  await prepareSession()
  const { data } = await api.post('/reset-password', credentials)
  return data.message
}
