const base = 'http://127.0.0.1:9400';
const response = await fetch(base);
if (!response.ok) throw new Error('Homepage failed');
const html = await response.text();
const urls = [...new Set([...html.matchAll(/(?:src|href)="([^"]+)"/g)].map(m=>m[1].replaceAll('&amp;','&')).filter(u=>u.startsWith(base+'/wp-content/')))];
for (const url of urls) {
  const asset=await fetch(url);
  if (!asset.ok || !(await asset.arrayBuffer()).byteLength) throw new Error('Missing or empty asset: '+url);
}
const robots=[...html.matchAll(/<meta name=['"]robots['"][^>]+>/g)];
if(robots.length!==1 || !robots[0][0].includes('noindex')) throw new Error('Development indexing guard failed');
for(const match of html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) JSON.parse(match[1]);
const denied=await fetch(base+'/wp-admin/admin-post.php',{method:'POST',body:new URLSearchParams({action:'rssa_save','content[text_5]':'Unauthorized test'})});
if(denied.status!==400 && denied.status!==403) throw new Error('Anonymous content write not rejected');
console.log('Homepage, '+urls.length+' local assets, JSON-LD, indexing guard, and anonymous-save rejection passed.');
