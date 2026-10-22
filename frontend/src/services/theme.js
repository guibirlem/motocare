import { readonly, ref } from 'vue'

const storageKey = 'motocare-theme'
const currentTheme = ref('light')
export const theme = readonly(currentTheme)

function applyTheme(value) {
  currentTheme.value = value
  document.documentElement.dataset.theme = value
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', value === 'dark' ? '#090d10' : '#0f0f0f')
}

export function initializeTheme() {
  let savedTheme
  try { savedTheme = localStorage.getItem(storageKey) } catch { /* A preferência continua funcionando sem armazenamento. */ }
  const preferredTheme = window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  applyTheme(savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : preferredTheme)
}

export function toggleTheme() {
  const nextTheme = currentTheme.value === 'dark' ? 'light' : 'dark'
  applyTheme(nextTheme)
  try { localStorage.setItem(storageKey, nextTheme) } catch { /* Mantém o tema nesta abertura se o armazenamento estiver bloqueado. */ }
}
