const CACHE_NAME='motocare-shell-v3'
self.addEventListener('install',event=>{event.waitUntil((async()=>{
  const response=await fetch('/precache.json',{cache:'no-store'})
  if(!response.ok)throw new Error('Shell indisponível')
  const cache=await caches.open(CACHE_NAME);await cache.addAll(await response.json());await self.skipWaiting()
})())})
self.addEventListener('activate',event=>{event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>(k.startsWith('motocare-cache-')||k.startsWith('motocare-shell-'))&&k!==CACHE_NAME).map(k=>caches.delete(k)))).then(()=>self.clients.claim()))})
self.addEventListener('fetch',event=>{
  const url=new URL(event.request.url)
  if(event.request.method!=='GET'||url.origin!==self.location.origin||url.pathname==='/api'||url.pathname.startsWith('/api/'))return
  if(event.request.mode==='navigate'){event.respondWith(fetch(event.request).catch(()=>caches.match('/index.html')));return}
  if(url.pathname.startsWith('/assets/')||url.pathname.startsWith('/icons/')||url.pathname==='/manifest.webmanifest')event.respondWith(caches.match(event.request).then(r=>r||fetch(event.request)))
})
