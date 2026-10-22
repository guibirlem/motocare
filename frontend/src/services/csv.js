export function buildCsv(rows) {
  const cell = value => {
    let text = String(value ?? '')
    if (/^\s*[=+@-]/.test(text)) text = "'" + text
    return '"' + text.replaceAll('"', '""') + '"'
  }
  return '\uFEFF' + rows.map(row => row.map(cell).join(';')).join('\r\n')
}
