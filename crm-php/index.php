<?php
require __DIR__ . '/bootstrap.php';
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keshav Technosys CRM</title>
<style>
:root{--bg:#f5f6f8;--card:#fff;--text:#1c2330;--muted:#6b7587;--line:#e3e6ec;--brand:#2f5bea;--brand-d:#2347c0;--ok:#16a34a;--bad:#dc2626;--warn:#d97706}
@media (prefers-color-scheme:dark){:root{--bg:#0f131a;--card:#181e29;--text:#e7ebf3;--muted:#8d97ab;--line:#283042;--brand:#5b82ff;--brand-d:#7c9bff}}
*{box-sizing:border-box}
body{margin:0;font:14px/1.45 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
nav{width:210px;background:var(--card);border-right:1px solid var(--line);padding:16px 10px;display:flex;flex-direction:column;gap:2px;position:sticky;top:0;height:100vh}
nav h1{font-size:15px;margin:0 8px 14px;line-height:1.2}nav h1 small{display:block;color:var(--muted);font-weight:400;font-size:12px}
nav a{padding:9px 12px;border-radius:8px;color:var(--text);text-decoration:none;cursor:pointer}
nav a.on{background:var(--brand);color:#fff}nav a:not(.on):hover{background:var(--bg)}
nav .sp{flex:1}nav button{margin-top:4px}
main{flex:1;padding:22px 26px;min-width:0}
.top{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.top h2{margin:0;font-size:20px;flex:1}
input,select,textarea,button{font:inherit;color:inherit}
input,select,textarea{background:var(--card);border:1px solid var(--line);border-radius:8px;padding:8px 10px;width:100%}
input:focus,select:focus,textarea:focus{outline:2px solid var(--brand);outline-offset:-1px}
.top input[type=search]{width:240px}
button{border:1px solid var(--line);background:var(--card);border-radius:8px;padding:8px 14px;cursor:pointer}
button.p{background:var(--brand);border-color:var(--brand);color:#fff}button.p:hover{background:var(--brand-d)}
button.d{color:var(--bad)}button.s{padding:4px 9px;font-size:12px}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:18px}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 16px}
.card .k{color:var(--muted);font-size:12px}.card .v{font-size:24px;font-weight:650;margin-top:2px}
table{width:100%;border-collapse:collapse;background:var(--card);border:1px solid var(--line);border-radius:12px;overflow:hidden}
th,td{text-align:left;padding:10px 12px;border-bottom:1px solid var(--line);vertical-align:top}
th{font-size:12px;color:var(--muted);font-weight:600;background:var(--bg)}tr:last-child td{border-bottom:0}
td.act{white-space:nowrap;text-align:right}td.act button{margin-left:4px}
.wrap{overflow-x:auto}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:12px;background:var(--bg);border:1px solid var(--line)}
.empty{padding:30px;text-align:center;color:var(--muted);background:var(--card);border:1px dashed var(--line);border-radius:12px}
.kan{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(230px,1fr);gap:12px;overflow-x:auto;padding-bottom:8px}
.col{background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:10px;min-height:220px}
.col.over{outline:2px dashed var(--brand)}
.col h3{margin:2px 4px 10px;font-size:13px;display:flex;justify-content:space-between}.col h3 span{color:var(--muted);font-weight:400}
.deal{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:10px;margin-bottom:8px;cursor:grab}
.deal b{display:block}.deal small{color:var(--muted)}.deal .amt{margin-top:4px;font-weight:600}
.two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.list .row{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--line)}.list .row:last-child{border:0}
dialog{border:1px solid var(--line);border-radius:14px;background:var(--card);color:var(--text);padding:0;width:min(520px,94vw)}
dialog::backdrop{background:#0008}
dialog form{padding:20px;display:grid;gap:12px}dialog h3{margin:0}
dialog label{display:grid;gap:4px;font-size:12px;color:var(--muted)}
.btns{display:flex;justify-content:flex-end;gap:8px;margin-top:6px}
.done{text-decoration:line-through;color:var(--muted)}.over{color:var(--bad)}
@media(max-width:760px){body{flex-direction:column}nav{width:auto;height:auto;position:static;flex-direction:row;flex-wrap:wrap;border-right:0;border-bottom:1px solid var(--line)}nav h1,nav .sp{display:none}main{padding:16px}.two{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
  <h1>Keshav Technosys<small>CRM</small></h1>
  <a data-v="dashboard">Dashboard</a><a data-v="contacts">Contacts</a><a data-v="companies">Companies</a>
  <a data-v="deals">Deals</a><a data-v="activities">Activities</a>
  <div class="sp"></div>
  <a href="login.php?logout=1">Log out</a>
</nav>
<main id="main"></main>
<dialog id="dlg"><form method="dialog" id="frm"></form></dialog>

<script>
const STAGES=['New','Qualified','Proposal','Negotiation','Won','Lost'];
const CSRF=<?= json_encode(csrf()) ?>;
let db={companies:[],contacts:[],deals:[],activities:[]};
async function api(body){
  const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify(body)});
  const j=await r.json().catch(()=>({}));
  if(!r.ok){if(r.status===401)location='login.php';alert(j.error||'Request failed');return false}
  return true;
}
async function load(){
  const r=await fetch('api.php');
  if(r.status===401){location='login.php';return}
  db=await r.json();
}
const persist=(table,record)=>api({action:'save',table,record});
const uid=()=>Date.now().toString(36)+Math.random().toString(36).slice(2,7);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const $=s=>document.querySelector(s);
const inr=n=>'₹'+Number(n||0).toLocaleString('en-IN');
const byId=(c,id)=>db[c].find(x=>x.id===id);
const cname=id=>byId('companies',id)?.name||'';
const pname=id=>{const p=byId('contacts',id);return p?(p.first_name+' '+(p.last_name||'')).trim():''};
const opts=(c,label)=>[['','—']].concat(db[c].map(x=>[x.id,label(x)]));

// Entity definitions: fields drive both forms and tables
const E={
 contacts:{title:'Contact',fields:[
  {k:'first_name',l:'First name',req:1},{k:'last_name',l:'Last name'},{k:'email',l:'Email',t:'email'},{k:'phone',l:'Phone'},
  {k:'job_title',l:'Job title'},{k:'company_id',l:'Company',sel:()=>opts('companies',x=>x.name)},
  {k:'status',l:'Status',sel:()=>['lead','prospect','customer','churned','unqualified'].map(x=>[x,x]),def:'lead'},
  {k:'source',l:'Source',sel:()=>['LinkedIn','Website','Referral','Cold outreach','Event','Other'].map(x=>[x,x]),def:'LinkedIn'},
  {k:'notes',l:'Notes',area:1}],
  cols:[['Name',x=>`<b>${esc(pname(x.id))}</b><br><small>${esc(x.job_title)}</small>`],['Company',x=>esc(cname(x.company_id))],
   ['Email / Phone',x=>esc(x.email)+'<br><small>'+esc(x.phone)+'</small>'],['Status',x=>`<span class="badge">${esc(x.status)}</span>`],['Source',x=>esc(x.source)]]},
 companies:{title:'Company',fields:[
  {k:'name',l:'Name',req:1},{k:'website',l:'Website'},{k:'industry',l:'Industry'},{k:'phone',l:'Phone'},{k:'city',l:'City'},{k:'notes',l:'Notes',area:1}],
  cols:[['Name',x=>`<b>${esc(x.name)}</b>`],['Industry',x=>esc(x.industry)],['City',x=>esc(x.city)],['Phone',x=>esc(x.phone)],
   ['Contacts',x=>db.contacts.filter(c=>c.company_id===x.id).length]]},
 deals:{title:'Deal',fields:[
  {k:'title',l:'Title',req:1},{k:'company_id',l:'Company',sel:()=>opts('companies',x=>x.name)},{k:'contact_id',l:'Contact',sel:()=>opts('contacts',x=>pname(x.id))},
  {k:'stage',l:'Stage',sel:()=>STAGES.map(x=>[x,x]),def:'New'},{k:'amount',l:'Amount (₹)',t:'number'},{k:'close',l:'Expected close',t:'date'}]},
 activities:{title:'Activity',fields:[
  {k:'type',l:'Type',sel:()=>['call','email','meeting','note','task'].map(x=>[x,x]),def:'task'},{k:'subject',l:'Subject',req:1},
  {k:'contact_id',l:'Contact',sel:()=>opts('contacts',x=>pname(x.id))},{k:'deal_id',l:'Deal',sel:()=>opts('deals',x=>x.title)},
  {k:'due',l:'Due date',t:'date'},{k:'description',l:'Details',area:1}]}
};

function openForm(ent,id){
  const cfg=E[ent],rec=id?byId(ent,id):{};
  $('#frm').innerHTML=`<h3>${id?'Edit':'New'} ${cfg.title}</h3>`+cfg.fields.map(f=>{
    const v=rec[f.k]??f.def??'';
    const inner=f.sel?`<select name="${f.k}">${f.sel().map(([a,b])=>`<option value="${esc(a)}"${a===v?' selected':''}>${esc(b)}</option>`).join('')}</select>`
      :f.area?`<textarea name="${f.k}" rows="3">${esc(v)}</textarea>`
      :`<input name="${f.k}" type="${f.t||'text'}" value="${esc(v)}"${f.req?' required':''}${f.t==='number'?' min="0" step="any"':''}>`;
    return `<label>${f.l}${inner}</label>`}).join('')+
    `<div class="btns"><button type="button" id="cx">Cancel</button><button class="p" value="ok">Save</button></div>`;
  $('#cx').onclick=()=>$('#dlg').close();
  $('#frm').onsubmit=async e=>{
    e.preventDefault();
    const fd=new FormData($('#frm'));const o=id?{...rec}:{id:uid()};
    cfg.fields.forEach(f=>o[f.k]=f.t==='number'?Number(fd.get(f.k)||0):String(fd.get(f.k)||'').trim());
    if(await persist(ent,o)){$('#dlg').close();await load()}
    render();
  };
  $('#dlg').showModal();
}
async function del(ent,id){
  if(!confirm('Delete this '+E[ent].title.toLowerCase()+'? This cannot be undone.'))return;
  if(await api({action:'delete',table:ent,id}))await load();
  render();
}

let view='dashboard',q='';
function table(ent){
  const cfg=E[ent],qq=q.toLowerCase();
  const rows=db[ent].filter(x=>!qq||JSON.stringify(x).toLowerCase().includes(qq)||(ent==='contacts'&&cname(x.company_id).toLowerCase().includes(qq)));
  if(!rows.length)return `<div class="empty">${db[ent].length?'No matches.':`No ${ent} yet. Click “Add” to create the first one.`}</div>`;
  return `<div class="wrap"><table><tr>${cfg.cols.map(c=>`<th>${c[0]}</th>`).join('')}<th></th></tr>`+
   rows.map(x=>`<tr>${cfg.cols.map(c=>`<td>${c[1](x)}</td>`).join('')}<td class="act"><button class="s" data-e="${x.id}">Edit</button><button class="s d" data-d="${x.id}">Delete</button></td></tr>`).join('')+`</table></div>`;
}
function head(t,ent,extra=''){
  return `<div class="top"><h2>${t}</h2>${ent?`<input type="search" id="q" placeholder="Search…" value="${esc(q)}">`:''}${extra}${ent?`<button class="p" id="add">+ Add ${E[ent].title.toLowerCase()}</button>`:''}</div>`;
}
function dash(){
  const open=db.deals.filter(d=>!['Won','Lost'].includes(d.stage)),won=db.deals.filter(d=>d.stage==='Won');
  const sum=a=>a.reduce((s,d)=>s+(d.amount||0),0);
  const today=new Date().toISOString().slice(0,10);
  const todo=db.activities.filter(a=>!a.done).sort((a,b)=>(a.due||'9')>(b.due||'9')?1:-1).slice(0,6);
  const recent=[...db.contacts].reverse().slice(0,6);
  return head('Dashboard')+`<div class="cards">
   <div class="card"><div class="k">Contacts</div><div class="v">${db.contacts.length}</div></div>
   <div class="card"><div class="k">Companies</div><div class="v">${db.companies.length}</div></div>
   <div class="card"><div class="k">Open pipeline</div><div class="v">${inr(sum(open))}</div></div>
   <div class="card"><div class="k">Won revenue</div><div class="v">${inr(sum(won))}</div></div></div>
   <div class="two"><div class="card"><h3 style="margin-top:0">Upcoming tasks</h3><div class="list">${todo.map(a=>`<div class="row"><span>${esc(a.subject)} <span class="badge">${esc(a.type)}</span></span><span class="${a.due&&a.due<today?'over':''}">${esc(a.due||'no date')}</span></div>`).join('')||'<div class="empty" style="border:0">Nothing pending.</div>'}</div></div>
   <div class="card"><h3 style="margin-top:0">Recent contacts</h3><div class="list">${recent.map(c=>`<div class="row"><span>${esc(pname(c.id))}</span><span class="badge">${esc(c.status)}</span></div>`).join('')||'<div class="empty" style="border:0">No contacts yet.</div>'}</div></div></div>`;
}
function kanban(){
  return head('Deals','deals')+`<div class="kan">`+STAGES.map(s=>{
    const ds=db.deals.filter(d=>d.stage===s&&(!q||JSON.stringify(d).toLowerCase().includes(q.toLowerCase())));
    return `<div class="col" data-s="${s}"><h3>${s}<span>${ds.length} · ${inr(ds.reduce((a,d)=>a+(d.amount||0),0))}</span></h3>`+
     ds.map(d=>`<div class="deal" draggable="true" data-id="${d.id}"><b>${esc(d.title)}</b><small>${esc(cname(d.company_id))}${d.contact_id?' · '+esc(pname(d.contact_id)):''}</small><div class="amt">${inr(d.amount)}</div>
      <div style="margin-top:6px"><button class="s" data-e="${d.id}">Edit</button> <button class="s d" data-d="${d.id}">Delete</button></div></div>`).join('')+`</div>`}).join('')+`</div>`;
}
function acts(){
  const today=new Date().toISOString().slice(0,10);
  const rows=db.activities.filter(a=>!q||JSON.stringify(a).toLowerCase().includes(q.toLowerCase()));
  return head('Activities','activities')+(rows.length?`<div class="wrap"><table><tr><th></th><th>Subject</th><th>Type</th><th>Related</th><th>Due</th><th></th></tr>`+
   rows.map(a=>`<tr><td><input type="checkbox" data-t="${a.id}" ${a.done?'checked':''} style="width:auto"></td><td class="${a.done?'done':''}">${esc(a.subject)}</td><td><span class="badge">${esc(a.type)}</span></td>
   <td>${esc(pname(a.contact_id))} ${esc(byId('deals',a.deal_id)?.title||'')}</td><td class="${!a.done&&a.due&&a.due<today?'over':''}">${esc(a.due)}</td>
   <td class="act"><button class="s" data-e="${a.id}">Edit</button><button class="s d" data-d="${a.id}">Delete</button></td></tr>`).join('')+`</table></div>`:'<div class="empty">No activities yet.</div>');
}
function csv(){
  const cols=['first_name','last_name','email','phone','job_title','status','source'];
  const lines=[cols.concat('company').join(',')].concat(db.contacts.map(c=>cols.map(k=>c[k]).concat(cname(c.company_id)).map(v=>'"'+String(v??'').replace(/"/g,'""')+'"').join(',')));
  dl('contacts.csv','text/csv',lines.join('\n'));
}
function dl(name,type,text){const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([text],{type}));a.download=name;a.click();URL.revokeObjectURL(a.href)}

function render(){
  document.querySelectorAll('nav a').forEach(a=>a.classList.toggle('on',a.dataset.v===view));
  const m=$('#main');
  m.innerHTML=view==='dashboard'?dash():view==='deals'?kanban():view==='activities'?acts()
   :head(view[0].toUpperCase()+view.slice(1),view,view==='contacts'?'<button id="csv">Export CSV</button>':'')+table(view);
  const ent=view;
  const s=$('#q');if(s)s.oninput=e=>{q=e.target.value;const p=e.target.selectionStart;render();const n=$('#q');n.focus();n.setSelectionRange(p,p)};
  const add=$('#add');if(add)add.onclick=()=>openForm(ent);
  const c=$('#csv');if(c)c.onclick=csv;
  m.querySelectorAll('[data-e]').forEach(b=>b.onclick=()=>openForm(ent,b.dataset.e));
  m.querySelectorAll('[data-d]').forEach(b=>b.onclick=()=>del(ent,b.dataset.d));
  m.querySelectorAll('[data-t]').forEach(b=>b.onchange=async()=>{const a=byId('activities',b.dataset.t);a.done=b.checked;await persist('activities',a);await load();render()});
  m.querySelectorAll('.deal').forEach(d=>d.ondragstart=e=>e.dataTransfer.setData('text/plain',d.dataset.id));
  m.querySelectorAll('.col').forEach(col=>{
    col.ondragover=e=>{e.preventDefault();col.classList.add('over')};col.ondragleave=()=>col.classList.remove('over');
    col.ondrop=async e=>{e.preventDefault();const d=byId('deals',e.dataTransfer.getData('text/plain'));if(d){d.stage=col.dataset.s;await persist('deals',d);await load()}render()};
  });
}
document.querySelectorAll('nav a').forEach(a=>a.onclick=()=>{view=a.dataset.v;q='';render()});
load().then(render);
</script>
</body>
</html>
