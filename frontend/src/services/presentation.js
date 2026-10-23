// A raiz autenticada e todos os acessos diretos ao sistema mantêm o comportamento anterior.
export function isPresentation(route, user) {
  return route.meta?.presentation === true || (!user && route.path === '/')
}

export function presentationAccess(user, register = false) {
  return { path: '/dashboard', ...(register && !user ? { query: { cadastro: '1' } } : {}) }
}
