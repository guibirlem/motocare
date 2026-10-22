export function waitForActiveWorker(registration, timeoutMs = 15000) {
  const worker = registration.installing || registration.waiting || registration.active
  if (!worker) return Promise.reject(new Error('Não foi possível iniciar as notificações.'))
  return new Promise((resolve, reject) => {
    let timer
    const cleanup = () => { clearTimeout(timer); worker.removeEventListener('statechange', check) }
    const check = () => {
      if (worker.state === 'activated') { cleanup(); resolve() }
      else if (worker.state === 'redundant') { cleanup(); reject(new Error('Não foi possível ativar as notificações. Tente novamente.')) }
    }
    worker.addEventListener('statechange', check)
    timer = setTimeout(() => { cleanup(); reject(new Error('Tempo esgotado ao ativar notificações.')) }, timeoutMs)
    // O estado pode ter mudado entre register() e a instalação do listener.
    check()
  })
}

export function applicationServerKey(value) {
  const decoded = atob(value.replace(/-/g, '+').replace(/_/g, '/'))
  const bytes = Uint8Array.from(decoded, c => c.charCodeAt(0))
  if (bytes.length !== 65 || bytes[0] !== 4) throw new Error('Chave de notificações inválida.')
  return bytes
}
