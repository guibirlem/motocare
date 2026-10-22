import { ref } from 'vue'
import { errorMessage } from '../services/api'
export function useTask() {
  const busy = ref(false), error = ref(''), notice = ref('')
  async function run(action, message = '') {
    if (busy.value) return false
    busy.value = true; error.value = ''; notice.value = ''
    try { await action(); notice.value = message; return true }
    catch (e) { error.value = errorMessage(e); return false }
    finally { busy.value = false }
  }
  return { busy, error, notice, run }
}
