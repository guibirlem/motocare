// Rasteriza o pictograma de moto do projeto sem dependências adicionais.
import { deflateSync } from 'node:zlib'
import { mkdir, writeFile } from 'node:fs/promises'
function crc32(bytes) { let crc=0xffffffff;for(const byte of bytes){crc^=byte;for(let i=0;i<8;i++)crc=(crc>>>1)^((crc&1)?0xedb88320:0)}return (crc^0xffffffff)>>>0 }
function chunk(type,bytes){const name=Buffer.from(type);const size=Buffer.alloc(4);size.writeUInt32BE(bytes.length);const crc=Buffer.alloc(4);crc.writeUInt32BE(crc32(Buffer.concat([name,bytes])));return Buffer.concat([size,name,bytes,crc])}
await mkdir('public/icons',{recursive:true})
for(const size of [192,512]){
 const rows=Buffer.alloc((size*4+1)*size)
 for(let y=0;y<size;y++)for(let x=0;x<size;x++){const i=y*(size*4+1)+1+x*4;rows[i]=170;rows[i+1]=223;rows[i+2]=0;rows[i+3]=255}
 const dot=(x,y)=>{const scale=size/160;x=Math.round((x+20)*scale);y=Math.round((y+45)*scale);const radius=Math.max(1,Math.round(1.5*scale));for(let dy=-radius;dy<=radius;dy++)for(let dx=-radius;dx<=radius;dx++){const xx=x+dx,yy=y+dy;if(xx>=0&&yy>=0&&xx<size&&yy<size&&dx*dx+dy*dy<=radius*radius){const i=yy*(size*4+1)+1+xx*4;rows[i]=15;rows[i+1]=15;rows[i+2]=15}}}
 const line=(a,b)=>{for(let i=0;i<=400;i++)dot(a[0]+(b[0]-a[0])*i/400,a[1]+(b[1]-a[1])*i/400)}
 for(const cx of [22,96])for(const r of [13,5])for(let i=0;i<500;i++)dot(cx+r*Math.cos(i*2*Math.PI/500),44+r*Math.sin(i*2*Math.PI/500))
 for(const path of [[[35,44],[42,22],[58,18],[68,24],[80,28],[96,31]],[[42,22],[38,44]],[[55,18],[63,14],[72,16]],[[80,28],[86,18],[96,16],[98,22]],[[44,24],[64,24],[64,38],[44,38],[44,24]],[[44,34],[30,40],[26,44]],[[80,28],[88,44]]])for(let i=1;i<path.length;i++)line(path[i-1],path[i])
 const header=Buffer.alloc(13);header.writeUInt32BE(size);header.writeUInt32BE(size,4);header[8]=8;header[9]=6
 const png=Buffer.concat([Buffer.from([137,80,78,71,13,10,26,10]),chunk('IHDR',header),chunk('IDAT',deflateSync(rows)),chunk('IEND',Buffer.alloc(0))])
 await writeFile('public/icons/icon-'+size+'.png',png)
}
