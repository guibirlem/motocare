import test from 'node:test'
import assert from 'node:assert/strict'
import { readFile } from 'node:fs/promises'
import vm from 'node:vm'
import { fuelTotal, today, alertThresholdKm, alertProgress } from '../src/services/format.js'
import { buildCsv } from '../src/services/csv.js'
import { api, errorMessage, offlineMessage, retryAfterSeconds } from '../src/services/api.js'

test('offline and unknown network failures give actionable connection messages',()=>{
 assert.equal(errorMessage({request:{},code:'ERR_NETWORK'},false),offlineMessage)
 assert.equal(errorMessage({code:'MOTOCARE_OFFLINE'},true),offlineMessage)
 assert.equal(errorMessage({request:{},code:'ERR_NETWORK'},true),'Não foi possível conectar. Verifique sua conexão com a internet e tente novamente.')
 assert.equal(errorMessage({response:{status:503}},false),'O serviço está indisponível. Tente novamente em instantes.')
 assert.equal(errorMessage({},true),'Não foi possível conectar. Verifique sua conexão com a internet e tente novamente.')
})

test('offline writes never reach the transport and are not replayed after reconnecting',async()=>{
 const previous=Object.getOwnPropertyDescriptor(globalThis,'navigator'), calls=[]
 const browser={onLine:false}
 Object.defineProperty(globalThis,'navigator',{value:browser,configurable:true})
 const adapter=async config=>{calls.push(config.method);return {status:200,statusText:'OK',headers:{},data:[],config}}
 try{
  await assert.rejects(api.post('/motocicletas/1/manutencoes',{valor:'10.00'},{adapter}),error=>error.code==='MOTOCARE_OFFLINE')
  assert.deepEqual(calls,[])
  browser.onLine=true
  await api.get('/motocicletas',{adapter})
  assert.deepEqual(calls,['get'])
 }finally{
  if(previous)Object.defineProperty(globalThis,'navigator',previous)
  else delete globalThis.navigator
 }
})

test('rate limits show the API wait time in Portuguese',()=>{
 const limited = {response:{status:429,headers:{'retry-after':'17'},data:{message:'Too Many Attempts.'}}}
 assert.equal(retryAfterSeconds(limited),17)
 assert.equal(errorMessage(limited),'Muitas tentativas. Aguarde 17 segundos antes de tentar novamente.')
 assert.equal(retryAfterSeconds({response:{headers:{'retry-after':'invalid'}}}),60)
 assert.equal(retryAfterSeconds({response:{headers:{'retry-after':'Thu, 22 Oct 2026 18:00:20 GMT'}}},Date.parse('2026-10-22T18:00:00Z')),20)
 assert.equal(retryAfterSeconds({response:{headers:{'retry-after':'0'}}}),1)
})

test('fuel totals round in integer cents',()=>{
 assert.equal(fuelTotal('12,50','7,00'),'87.50')
 assert.equal(fuelTotal('1.01','1.50'),'1.52')
 assert.equal(fuelTotal('0.10','0.20'),'0.02')
 assert.equal(fuelTotal('bad','7'),'0.00')
 assert.equal(fuelTotal('999999.99','99999999.99'),'99999998990000.00')
})
test('CSV escapes text and prevents spreadsheet formulas',()=>{
 const csv=buildCsv([['A;B','"quoted"','linha\nnova',null,'=1+1','\t@SUM(1)','-10']])
 assert.equal(csv,'\uFEFF"A;B";"""quoted""";"linha\nnova";"";"\'=1+1";"\'\t@SUM(1)";"\'-10"')
})
const workerSource=await readFile(new URL('../public/sw.js',import.meta.url),'utf8')
function worker({offline=false}={}){
 const handlers={},deleted=[],stored=[]
 const context={ URL, self:{location:{origin:'https://motocare.test'},skipWaiting:async()=>{},clients:{claim:async()=>{}},addEventListener:(name,fn)=>handlers[name]=fn},fetch:async request=>{if(offline)throw new Error('offline');return {ok:true,json:async()=>['/index.html','/assets/app.js'],source:'network'}},caches:{open:async()=>({addAll:async urls=>stored.push(...urls)}),keys:async()=>['motocare-cache-v2','other-application','motocare-shell-v3'],delete:async name=>deleted.push(name),match:async key=>({source:'cache',key})}}
 vm.runInNewContext(workerSource,context)
 return {handlers,deleted,stored}
}
test('service worker precaches only public shell and keeps other applications',async()=>{
 const w=worker();let pending
 w.handlers.install({waitUntil:p=>pending=p});await pending
 assert.deepEqual(w.stored,['/index.html','/assets/app.js'])
 w.handlers.activate({waitUntil:p=>pending=p});await pending
 assert.deepEqual(w.deleted,['motocare-cache-v2'])
})
test('service worker never intercepts API or mutations',()=>{
 const w=worker()
 for(const path of ['/api','/api/user','/api/motocicletas/1/despesas'])for(const method of ['GET','POST'])w.handlers.fetch({request:{url:'https://motocare.test'+path,method},respondWith:()=>assert.fail('API intercepted')})
 w.handlers.fetch({request:{url:'https://other.test/assets/x.js',method:'GET'},respondWith:()=>assert.fail('cross origin intercepted')})
})
test('offline navigation loads cached shell',async()=>{
 const w=worker({offline:true});let result
 w.handlers.fetch({request:{url:'https://motocare.test/gastos',method:'GET',mode:'navigate'},respondWith:p=>result=p})
 assert.deepEqual(await result,{source:'cache',key:'/index.html'})
})

const workspaceSource=(await readFile(new URL('../src/services/workspace.js',import.meta.url),'utf8')).replace(/^import .*$/gm,'').replaceAll('export ','')
function sessionWorkspace(get) {
 const handlers={}
 const context={reactive:value=>value,navigator:{onLine:true},window:{addEventListener(){}},localStorage:{removeItem(){}},prepareSession:async()=>{},resumePush:async()=>{},stopPushSession(){},detachPushBrowser:async()=>{},api:{get,defaults:{headers:{common:{}}},interceptors:{request:{use:fn=>handlers.request=fn},response:{use:(success,error)=>{handlers.success=success;handlers.error=error}}}}}
 vm.runInNewContext(workspaceSource+'\nglobalThis.session={workspace,refreshMotos,clearSession,startSession,syncMaintenanceMileage}',context)
 return {...context.session,handlers}
}
function deferred(){let resolve;const promise=new Promise(done=>resolve=done);return {promise,resolve}}

test('financial configuration is loaded before exposing the authenticated workspace',async()=>{
 const features=deferred()
 const s=sessionWorkspace(async path=>path==='/features'?features.promise:{data:path==='/user'?{id:1,nome:'Pilot'}:[]})
 const loading=s.startSession()
 await Promise.resolve();await Promise.resolve()
 assert.equal(s.workspace.user,null)
 assert.equal(s.workspace.features.financeiro,false)
 features.resolve({data:{financeiro:true}})
 await loading
 assert.equal(s.workspace.user.id,1)
 assert.equal(s.workspace.features.financeiro,true)
 s.clearSession()
 assert.equal(s.workspace.features.financeiro,false)
})

test('a late financial configuration cannot enable routes for a later account',async()=>{
 const features=deferred()
 const s=sessionWorkspace(async path=>path==='/features'?features.promise:{data:{id:1,nome:'Previous'}})
 const loading=s.startSession()
 await Promise.resolve();await Promise.resolve()
 s.clearSession();s.workspace.user={id:2}
 features.resolve({data:{financeiro:true}})
 await loading
 assert.equal(s.workspace.user.id,2)
 assert.equal(s.workspace.features.financeiro,false)
})

test('late private responses cannot restore a signed out session or its motorcycles',async()=>{
 const user=deferred(), s=sessionWorkspace(()=>user.promise)
 const loading=s.startSession();await Promise.resolve();await Promise.resolve()
 s.clearSession()
 user.resolve({data:{id:1,nome:'Previous account'}})
 await loading
 assert.equal(s.workspace.user,null);assert.equal(s.workspace.motos.length,0)
 const motos=deferred(), r=sessionWorkspace(()=>motos.promise)
 r.workspace.user={id:1};const refresh=r.refreshMotos();r.clearSession()
 motos.resolve({data:[{id:1,km_atual:1000}]});await refresh
 assert.equal(r.workspace.motos.length,0)
})

test('stale responses cannot overwrite or expire a later session',async()=>{
 const s=sessionWorkspace(async()=>({data:[]})), oldConfig=s.handlers.request({})
 s.clearSession();s.workspace.user={id:2}
 assert.throws(()=>s.handlers.success({config:oldConfig,data:{id:1}}),/sessão mudou/)
 await assert.rejects(s.handlers.error({config:oldConfig,response:{status:401}}))
 assert.equal(s.workspace.user.id,2)
 const currentConfig=s.handlers.request({})
 await assert.rejects(s.handlers.error({config:currentConfig,response:{status:419}}))
 assert.equal(s.workspace.user,null)
})

test('a slower motorcycle refresh cannot replace the newest odometer',async()=>{
 const first=deferred(),second=deferred();let calls=0
 const s=sessionWorkspace(()=>++calls===1?first.promise:second.promise)
 s.workspace.user={id:1}
 const older=s.refreshMotos(), newer=s.refreshMotos()
 second.resolve({data:[{id:1,km_atual:2000}]});await newer
 first.resolve({data:[{id:1,km_atual:1000}]});await older
 assert.equal(s.workspace.motos[0].km_atual,2000)
})

test('confirmed maintenance updates the selected workspace monotonically and only its motorcycle',()=>{
 const s=sessionWorkspace(async()=>({data:[]}))
 s.workspace.motos=[{id:1,km_atual:1000},{id:2,km_atual:5000}]
 s.syncMaintenanceMileage({motocicleta_id:1,status:'pendente',km_servico:3000})
 assert.equal(s.workspace.motos[0].km_atual,1000)
 s.syncMaintenanceMileage({motocicleta_id:1,status:'concluido',km_servico:2000})
 s.syncMaintenanceMileage({motocicleta_id:1,status:'concluido',km_servico:800})
 assert.equal(s.workspace.motos[0].km_atual,2000)
 assert.equal(s.workspace.motos[1].km_atual,5000)
})

test('business date follows Sao Paulo across the UTC midnight boundary',()=>{
 assert.equal(today(new Date('2026-10-23T02:59:59Z')),'2026-10-22')
 assert.equal(today(new Date('2026-10-23T03:00:00Z')),'2026-10-23')
})

test('alert threshold and progress use the service interval at high odometers',()=>{
 const alert={km_base:66779,km_limite:71779}
 assert.equal(alertThresholdKm(alert),71279)
 assert.equal(alertProgress(alert,66779),0)
 assert.equal(alertProgress(alert,71279),90)
 assert.equal(alertProgress(alert,71779),100)
 assert.equal(alertProgress(alert,90000),100)
 assert.equal(alertThresholdKm({km_base:1000,km_limite:1001}),1001)
 assert.equal(alertThresholdKm({km_base:0,km_limite:1000}),900)
})
