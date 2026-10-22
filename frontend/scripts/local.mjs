import config from '../vite.config.js'
import { build, preview } from 'vite'
import { readdir, readFile, writeFile } from 'node:fs/promises'
import { createHash } from 'node:crypto'
const command=process.argv[2] || 'start'
if(command !== 'preview') {
  await build({...config,configFile:false})
  const assets=(await readdir('dist/assets')).filter(name=>/\.(js|css|woff2?|png|svg)$/.test(name)).map(name=>'/assets/'+name)
  const precache=['/index.html','/manifest.webmanifest','/icons/icon-192.png','/icons/icon-512.png',...assets]
  await writeFile('dist/precache.json',JSON.stringify(precache))
  const worker=await readFile('dist/sw.js','utf8')
  const digest=createHash('sha256').update(JSON.stringify(precache)).update(worker)
  for(const file of precache)digest.update(await readFile('dist'+file))
  const hash=digest.digest('hex').slice(0,12)
  await writeFile('dist/sw.js',worker.replace('motocare-shell-v3','motocare-shell-'+hash))
}
if(command !== 'build') {
  const server=await preview({...config,configFile:false,preview:{...config.preview,host:'127.0.0.1',port:5173,strictPort:true}})
  server.printUrls()
}
