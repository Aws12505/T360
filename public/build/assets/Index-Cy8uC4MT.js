import{J as Re,E as ce,a7 as v,N as u,an as o,a6 as N,a1 as t,O as n,ae as a,a4 as V,ao as M,I as s,a9 as D,aa as L,af as x,a8 as ge,B as w,A as de,L as F,aM as J,aN as Ie}from"./vendor-xy97mY0-.js";import{C as R,m as Oe,W as z}from"./vendor-vue-BRPOKWly.js";import{_ as Ye}from"./AppLayout.vue_vue_type_script_setup_true_lang-D6HeKE5A.js";import{_ as g}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as h,a as ee,b as te,c as le,d as oe,e as re}from"./DialogTitle.vue_vue_type_script_setup_true_lang-CL_BLXsN.js";import{_,a as I}from"./Label.vue_vue_type_script_setup_true_lang-BAsbsLwI.js";import{_ as We}from"./Checkbox.vue_vue_type_script_setup_true_lang-D9r_l7BE.js";import{_ as A}from"./Icon.vue_vue_type_script_setup_true_lang-ChfPiT8z.js";import{_ as qe,a as He,b as fe,c as ie,d as Ge,e as ne}from"./TableRow.vue_vue_type_script_setup_true_lang-B3yArJuB.js";import{_ as me}from"./Card.vue_vue_type_script_setup_true_lang-DYwS7A8-.js";import{_ as Je,a as Qe}from"./CardTitle.vue_vue_type_script_setup_true_lang-Wjlz8bZz.js";import{_ as ve}from"./CardContent.vue_vue_type_script_setup_true_lang-DuDvULMo.js";import{_ as Ke,a as Xe,b as Ze}from"./AlertDescription.vue_vue_type_script_setup_true_lang-D3Lka1oR.js";import"./vendor-d3-DpcuoaSC.js";import"./vendor-axios-t--hEgTQ.js";import"./vendor-lodash-DCUXXPMP.js";import"./utils-OMMm8ro2.js";import"./vendor-ui-CqtnUFeu.js";import"./app-COgRaFVd.js";import"./vendor-icons-CrF1H9yF.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-evYd1tA8.js";const he={class:"grid grid-cols-1 sm:grid-cols-2 gap-4"},et={key:0,class:"col-span-full"},tt={class:"relative"},lt=["value"],ot={class:"col-span-full"},nt={class:"relative"},st={class:"relative"},at={class:"relative"},dt=["value"],rt={class:"relative"},it={class:"flex items-center space-x-2"},ut={class:"flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-2 pt-4 border-t flex"},ft=Re({__name:"DelayForm",props:{delay:{type:Object,default:null},delayCodes:{type:Array,default:()=>[]},tenants:{type:Array,default:()=>[]},isSuperAdmin:{type:Boolean,default:!1},tenantSlug:{type:String,default:null}},emits:["close"],setup(c,{emit:m}){var $,T,Q,S,B,Y,U;const C=c,j=m,f=R({id:($=C.delay)==null?void 0:$.id,tenant_id:C.isSuperAdmin?(T=C.delay)==null?void 0:T.tenant_id:null,date:((Q=C.delay)==null?void 0:Q.date)||"",driver_name:((S=C.delay)==null?void 0:S.driver_name)||"",delay_type:((B=C.delay)==null?void 0:B.delay_type)||"origin",delay_category:((Y=C.delay)==null?void 0:Y.delay_category)||"1_120",delay_code_id:((U=C.delay)==null?void 0:U.delay_code_id)||null,disputed:C.delay&&C.delay.disputed!==null?parseInt(C.delay.disputed)===1:!1,driver_controllable:C.delay&&C.delay.driver_controllable!==null?parseInt(C.delay.driver_controllable)===1:null});ce(()=>C.delay,p=>{p?(f.id=p.id,f.tenant_id=p.tenant_id,f.date=p.date,f.driver_name=p.driver_name,f.delay_type=p.delay_type,f.delay_category=p.delay_category,f.delay_code_id=p.delay_code_id,f.disputed=p.disputed!==null?parseInt(p.disputed)===1:!1,f.driver_controllable=p.driver_controllable!==null?parseInt(p.driver_controllable)===1:null):f.reset()},{immediate:!0});const O=()=>{const p=!!f.id,r=C.isSuperAdmin?p?"ontime.update.admin":"ontime.store.admin":p?"ontime.update":"ontime.store",y=C.isSuperAdmin?p?{delay:f.id}:{}:p?{tenantSlug:C.tenantSlug,delay:f.id}:{tenantSlug:C.tenantSlug};f[p?"put":"post"](route(r,y),{onSuccess:()=>j("close")})};return(p,r)=>(u(),v("form",{onSubmit:ge(O,["prevent"]),class:"space-y-6"},[o("div",he,[c.isSuperAdmin?(u(),v("div",et,[t(_,null,{default:n(()=>r[9]||(r[9]=[a("Company Name")])),_:1}),o("div",tt,[V(o("select",{"onUpdate:modelValue":r[0]||(r[0]=y=>s(f).tenant_id=y),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},[r[10]||(r[10]=o("option",{value:null,disabled:""},"Select Company",-1)),(u(!0),v(D,null,L(c.tenants,y=>(u(),v("option",{key:y.id,value:y.id},x(y.name),9,lt))),128))],512),[[M,s(f).tenant_id]]),r[11]||(r[11]=o("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[o("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[o("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])])):N("",!0),o("div",ot,[t(_,null,{default:n(()=>r[12]||(r[12]=[a("Date")])),_:1}),t(I,{type:"date",modelValue:s(f).date,"onUpdate:modelValue":r[1]||(r[1]=y=>s(f).date=y),class:"w-full"},null,8,["modelValue"])]),o("div",null,[t(_,null,{default:n(()=>r[13]||(r[13]=[a("Driver Name")])),_:1}),t(I,{modelValue:s(f).driver_name,"onUpdate:modelValue":r[2]||(r[2]=y=>s(f).driver_name=y),class:"w-full"},null,8,["modelValue"])]),o("div",null,[t(_,null,{default:n(()=>r[14]||(r[14]=[a("Delay Type")])),_:1}),o("div",nt,[V(o("select",{"onUpdate:modelValue":r[3]||(r[3]=y=>s(f).delay_type=y),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},r[15]||(r[15]=[o("option",{value:"origin"},"Origin",-1),o("option",{value:"destination"},"Destination",-1)]),512),[[M,s(f).delay_type]]),r[16]||(r[16]=o("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[o("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[o("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),o("div",null,[t(_,null,{default:n(()=>r[17]||(r[17]=[a("Delay Category")])),_:1}),o("div",st,[V(o("select",{"onUpdate:modelValue":r[4]||(r[4]=y=>s(f).delay_category=y),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},r[18]||(r[18]=[o("option",{value:"1_120"},"1–120 min",-1),o("option",{value:"121_600"},"121–600 min",-1),o("option",{value:"601_plus"},"601+ min",-1)]),512),[[M,s(f).delay_category]]),r[19]||(r[19]=o("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[o("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[o("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),o("div",null,[t(_,null,{default:n(()=>r[20]||(r[20]=[a("Delay Code")])),_:1}),o("div",at,[V(o("select",{"onUpdate:modelValue":r[5]||(r[5]=y=>s(f).delay_code_id=y),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},[(u(!0),v(D,null,L(c.delayCodes,y=>(u(),v("option",{key:y.id,value:y.id},x(y.code),9,dt))),128))],512),[[M,s(f).delay_code_id]]),r[21]||(r[21]=o("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[o("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[o("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),o("div",null,[t(_,null,{default:n(()=>r[22]||(r[22]=[a("Driver Controllable")])),_:1}),o("div",rt,[V(o("select",{"onUpdate:modelValue":r[6]||(r[6]=y=>s(f).driver_controllable=y),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},r[23]||(r[23]=[o("option",{value:null},"N/A",-1),o("option",{value:!0},"Yes",-1),o("option",{value:!1},"No",-1)]),512),[[M,s(f).driver_controllable]]),r[24]||(r[24]=o("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[o("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[o("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),o("div",it,[t(We,{checked:s(f).disputed,"onUpdate:checked":r[7]||(r[7]=y=>s(f).disputed=y),id:"disputed"},null,8,["checked"]),t(_,{for:"disputed"},{default:n(()=>r[25]||(r[25]=[a("Disputed")])),_:1})])]),o("div",ut,[t(g,{type:"button",onClick:r[8]||(r[8]=y=>j("close")),variant:"outline",class:"w-full sm:w-auto"},{default:n(()=>r[26]||(r[26]=[a(" Cancel ")])),_:1}),t(g,{type:"submit",disabled:s(f).processing,class:"w-full sm:w-auto"},{default:n(()=>[a(x(s(f).id?"Update":"Create"),1)]),_:1},8,["disabled"])])],32))}}),mt={class:"max-w-7xl mx-auto p-6 space-y-8"},vt={class:"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"},pt={class:"flex flex-wrap gap-3"},yt={class:"cursor-pointer"},ct=["action"],gt={class:"flex flex-col gap-2"},bt={class:"flex flex-wrap gap-2"},xt={key:0,class:"text-sm text-muted-foreground"},Ct={key:0},kt={key:1},wt={key:2},_t={class:"flex flex-col justify-between gap-4"},St={class:"grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 w-full"},Dt=["value"],$t={class:"flex justify-end"},At={class:"overflow-x-auto"},Nt={class:"flex items-center justify-center"},Ft=["checked"],Vt={class:"flex items-center"},Ut={key:0,class:"ml-2"},zt={key:0,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},Mt={key:1,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},Tt={key:1,class:"ml-2 opacity-50"},Bt=["value"],Pt={key:0,class:"ml-1 text-xs text-red-500"},Et={class:"flex space-x-2"},Lt={key:0,class:"bg-muted/20 px-4 py-3 border-t"},jt={class:"flex justify-between items-center"},Rt={class:"text-sm text-muted-foreground"},It={class:"flex items-center gap-4"},Ot={class:"flex items-center gap-2"},Yt={class:"flex"},Wt=["innerHTML"],qt={class:"mt-4 space-y-4"},Ht={class:"flex items-center justify-between"},Gt={class:"max-h-[400px] overflow-y-auto"},Jt={key:0,class:"text-center py-8 text-muted-foreground border rounded-md"},Qt={key:1,class:"space-y-2"},Kt=["onClick"],Xt={class:"font-medium"},Zt={key:0,class:"ml-2 text-xs text-red-500"},ht={key:0,class:"text-sm text-muted-foreground mt-1"},el={class:"opacity-0 group-hover:opacity-100 transition-opacity"},tl={key:0,class:"border rounded-md p-4 space-y-4"},ll={class:"text-sm font-medium"},ol={class:"space-y-3"},nl={class:"flex justify-end space-x-2"},$l={__name:"Index",props:{delays:{type:Object,default:()=>({data:[],links:[]})},tenantSlug:{type:String,default:null},delay_codes:Array,tenants:{type:Array,default:()=>[]},isSuperAdmin:{type:Boolean,default:!1},dateRange:{type:Object,default:null},dateFilter:{type:String,default:"full"}},setup(c){const m=c,C=[{title:m.tenantSlug?"Dashboard":"Admin Dashboard",href:m.tenantSlug?route("dashboard",{tenantSlug:m.tenantSlug}):route("admin.dashboard")},{title:"On-Time",href:m.tenantSlug?route("ontime.index",{tenantSlug:m.tenantSlug}):route("ontime.index.admin")}],j=w(!1),f=w(!1),O=w(null),$=w(""),T=w(!1),Q=w(null),S=w([]),B=w(!1),Y=w(null),U=w(!1),p=w(null),r=w({code:"",description:""}),y=w(!1),ue=w(null),W=w("date"),q=w("desc"),b=w({search:"",dateFrom:"",dateTo:"",delayCode:"",disputed:"",controllable:""}),se=["date","delay_type","driver_name","penalty","delay_code","disputed","driver_controllable"],H=de(()=>{let d=[...m.delays.data];if(b.value.search){const e=b.value.search.toLowerCase();d=d.filter(l=>{var i,k,E,Z;return((i=l.driver_name)==null?void 0:i.toLowerCase().includes(e))||((k=l.delay_type)==null?void 0:k.toLowerCase().includes(e))||((Z=(E=l.delay_code)==null?void 0:E.code)==null?void 0:Z.toLowerCase().includes(e))})}if(b.value.dateFrom&&(d=d.filter(e=>e.date&&e.date>=b.value.dateFrom)),b.value.dateTo&&(d=d.filter(e=>e.date&&e.date<=b.value.dateTo)),b.value.delayCode&&(d=d.filter(e=>{var l;return((l=e.delay_code)==null?void 0:l.id)===parseInt(b.value.delayCode)})),b.value.disputed!==""){const e=b.value.disputed==="true";d=d.filter(l=>l.disputed===e)}if(b.value.controllable!=="")if(b.value.controllable==="null")d=d.filter(e=>e.driver_controllable===null);else{const e=b.value.controllable==="true";d=d.filter(l=>l.driver_controllable===e)}return d.sort((e,l)=>{var E,Z;let i,k;return W.value==="delay_code"?(i=((E=e.delay_code)==null?void 0:E.code)||"",k=((Z=l.delay_code)==null?void 0:Z.code)||""):(i=e[W.value],k=l[W.value]),i===null?1:k===null?-1:(typeof i=="string"&&(i=i.toLowerCase(),k=k.toLowerCase()),i<k?q.value==="asc"?-1:1:i>k?q.value==="asc"?1:-1:0)}),d}),be=de(()=>m.delay_codes.filter(d=>!d.deleted_at));function xe(d){W.value===d?q.value=q.value==="asc"?"desc":"asc":(W.value=d,q.value="asc")}function G(){}function Ce(){b.value={search:"",dateFrom:"",dateTo:"",delayCode:"",disputed:"",controllable:""}}const K=w(m.delays.per_page||10),P=w(m.dateFilter||"full");function ke(){const d=m.tenantSlug?route("ontime.index",{tenantSlug:m.tenantSlug}):route("ontime.index.admin");z.get(d,{dateFilter:P.value,perPage:K.value},{preserveState:!0})}function X(d){P.value=d;const e=m.tenantSlug?route("ontime.index",{tenantSlug:m.tenantSlug}):route("ontime.index.admin");z.get(e,{dateFilter:d,perPage:K.value},{preserveState:!0})}function we(d){if(d){const e=new URL(d);e.searchParams.set("perPage",K.value),z.get(e.href,{},{preserveScroll:!0,preserveState:!0,only:["delays"]})}}function ae(d){if(!d)return"";const e=d.split("-");if(e.length!==3)return d;const[l,i,k]=e;return`${Number(i)}/${Number(k)}/${l}`}const pe=(d=null)=>{O.value=d,j.value=!0},_e=d=>{Q.value=d,T.value=!0},Se=()=>{f.value=!0,U.value=!1,p.value=null},De=()=>{r.value={code:"",description:""},p.value=null,U.value=!0},$e=d=>{r.value={code:d.code,description:d.description||""},p.value=d.id,U.value=!0},Ae=()=>{U.value=!1,p.value=null},ye=d=>{ue.value=d,y.value=!0},Ne=d=>{const e=R({}),l=m.isSuperAdmin?"delay_codes.destroy.admin":"delay_codes.destroy",i=m.isSuperAdmin?{id:d}:{tenantSlug:m.tenantSlug,delay_code:d};e.delete(route(l,i),{onSuccess:()=>{$.value="Delay code deleted successfully.",y.value=!1,z.reload({only:["delay_codes"]})},onError:k=>{console.error(k)}})},Fe=()=>{const d=R({code:r.value.code,description:r.value.description}),e=p.value?m.isSuperAdmin?"delay_codes.update.admin":"delay_codes.update":m.isSuperAdmin?"delay_codes.store.admin":"delay_codes.store",l=p.value?m.isSuperAdmin?{id:p.value}:{tenantSlug:m.tenantSlug,id:p.value}:m.isSuperAdmin?{}:{tenantSlug:m.tenantSlug};(p.value?d.put:d.post).call(d,route(e,l),{onSuccess:()=>{$.value=p.value?"Delay code updated successfully.":"Delay code created successfully.",U.value=!1,p.value=null,z.reload({only:["delay_codes"]})},onError:k=>{console.error(k)}})},Ve=d=>{R({}).post(route(m.isSuperAdmin?"delay_codes.restore.admin":"delay_codes.restore",{id:d}),{onSuccess:()=>{$.value="Delay code restored successfully.",z.reload({only:["delay_codes"]})},onError:l=>{console.error(l)}})},Ue=d=>{R({}).delete(route(m.isSuperAdmin?"delay_codes.forceDelete.admin":"delay_codes.forceDelete",{id:d}),{onSuccess:()=>{$.value="Delay code permanently deleted successfully.",z.reload({only:["delay_codes"]})},onError:l=>{console.error(l)}})},ze=d=>{const e=R({}),l=m.isSuperAdmin?"ontime.destroy.admin":"ontime.destroy",i=m.isSuperAdmin?{delay:d}:{tenantSlug:m.tenantSlug,delay:d};e.delete(route(l,i),{preserveScroll:!0,onSuccess:()=>{$.value="Delay record deleted successfully.",T.value=!1}})};ce($,d=>{d&&setTimeout(()=>{$.value=""},5e3)});const Me=de(()=>H.value.length>0&&S.value.length===H.value.length);function Te(d){d.target.checked?S.value=H.value.map(e=>e.id):S.value=[]}function Be(){S.value.length>0&&(B.value=!0)}function Pe(){const d=R({ids:S.value}),e=m.isSuperAdmin?"ontime.destroyBulk.admin":"ontime.destroyBulk",l=m.isSuperAdmin?{}:{tenantSlug:m.tenantSlug};d.delete(route(e,l),{preserveScroll:!0,onSuccess:()=>{$.value=`${S.value.length} delay records deleted successfully.`,S.value=[],B.value=!1,z.reload()},onError:i=>{console.error(i)}})}function Ee(d){const e=d.target.files[0];if(!e)return;const l=new FormData;l.append("csv_file",e);const i=m.isSuperAdmin?route("ontime.import.admin"):route("ontime.import",{tenantSlug:m.tenantSlug});z.post(i,l,{onSuccess:()=>{$.value="Delays imported successfully",d.target.value=""},onError:k=>{console.error(k),d.target.value=""}})}function Le(){Y.value&&Y.value.submit()}const je=de(()=>m.tenantSlug?route("ontime.export",{tenantSlug:m.tenantSlug}):route("ontime.export.admin"));return(d,e)=>(u(),F(Ye,{breadcrumbs:C,tenantSlug:c.tenantSlug},{default:n(()=>[t(s(Oe),{title:"On-Time"}),o("div",mt,[$.value?(u(),F(s(Ke),{key:0,variant:"success"},{default:n(()=>[t(s(Xe),null,{default:n(()=>e[31]||(e[31]=[a("Success")])),_:1}),t(s(Ze),null,{default:n(()=>[a(x($.value),1)]),_:1})]),_:1})):N("",!0),o("div",vt,[e[36]||(e[36]=o("h1",{class:"text-2xl font-bold text-gray-800 dark:text-gray-200"},"On-Time Management",-1)),o("div",pt,[t(g,{onClick:e[0]||(e[0]=l=>pe()),variant:"default"},{default:n(()=>[t(A,{name:"plus",class:"mr-2 h-4 w-4"}),e[32]||(e[32]=a(" Add Delay "))]),_:1}),S.value.length>0?(u(),F(g,{key:0,onClick:e[1]||(e[1]=l=>Be()),variant:"destructive"},{default:n(()=>[t(A,{name:"trash",class:"mr-2 h-4 w-4"}),a(" Delete Selected ("+x(S.value.length)+") ",1)]),_:1})):N("",!0),o("label",yt,[t(g,{variant:"secondary",as:"span"},{default:n(()=>[t(A,{name:"upload",class:"mr-2 h-4 w-4"}),e[33]||(e[33]=a(" Upload CSV "))]),_:1}),o("input",{type:"file",class:"hidden",onChange:Ee,accept:".csv"},null,32)]),t(g,{onClick:ge(Le,["prevent"]),variant:"outline"},{default:n(()=>[t(A,{name:"download",class:"mr-2 h-4 w-4"}),e[34]||(e[34]=a(" Download CSV "))]),_:1}),c.isSuperAdmin?(u(),F(g,{key:1,onClick:e[2]||(e[2]=l=>Se()),variant:"outline"},{default:n(()=>[t(A,{name:"settings",class:"mr-2 h-4 w-4"}),e[35]||(e[35]=a(" Manage Delay Codes "))]),_:1})):N("",!0)])]),o("form",{ref_key:"exportForm",ref:Y,action:je.value,method:"GET",class:"hidden"},null,8,ct),t(s(me),null,{default:n(()=>[t(s(ve),{class:"p-4"},{default:n(()=>[o("div",gt,[o("div",bt,[t(g,{onClick:e[3]||(e[3]=l=>X("yesterday")),variant:"outline",size:"sm",class:J({"bg-primary/10 text-primary border-primary":P.value==="yesterday"})},{default:n(()=>e[37]||(e[37]=[a(" Yesterday ")])),_:1},8,["class"]),t(g,{onClick:e[4]||(e[4]=l=>X("current-week")),variant:"outline",size:"sm",class:J({"bg-primary/10 text-primary border-primary":P.value==="current-week"})},{default:n(()=>e[38]||(e[38]=[a(" Current Week ")])),_:1},8,["class"]),t(g,{onClick:e[5]||(e[5]=l=>X("6w")),variant:"outline",size:"sm",class:J({"bg-primary/10 text-primary border-primary":P.value==="6w"})},{default:n(()=>e[39]||(e[39]=[a(" 6 Weeks ")])),_:1},8,["class"]),t(g,{onClick:e[6]||(e[6]=l=>X("quarterly")),variant:"outline",size:"sm",class:J({"bg-primary/10 text-primary border-primary":P.value==="quarterly"})},{default:n(()=>e[40]||(e[40]=[a(" Quarterly ")])),_:1},8,["class"]),t(g,{onClick:e[7]||(e[7]=l=>X("full")),variant:"outline",size:"sm",class:J({"bg-primary/10 text-primary border-primary":P.value==="full"})},{default:n(()=>e[41]||(e[41]=[a(" Full ")])),_:1},8,["class"])]),c.dateRange?(u(),v("div",xt,[P.value==="yesterday"&&c.dateRange.start?(u(),v("span",Ct," Showing data from "+x(ae(c.dateRange.start)),1)):c.dateRange.start&&c.dateRange.end?(u(),v("span",kt," Showing data from "+x(ae(c.dateRange.start))+" to "+x(ae(c.dateRange.end)),1)):(u(),v("span",wt,x(c.dateRange.label),1))])):N("",!0)])]),_:1})]),_:1}),t(s(me),null,{default:n(()=>[t(s(Je),null,{default:n(()=>[t(s(Qe),null,{default:n(()=>e[42]||(e[42]=[a("Filters")])),_:1})]),_:1}),t(s(ve),null,{default:n(()=>[o("div",_t,[o("div",St,[o("div",null,[t(s(_),{for:"search"},{default:n(()=>e[43]||(e[43]=[a("Search")])),_:1}),t(s(I),{id:"search",modelValue:b.value.search,"onUpdate:modelValue":e[8]||(e[8]=l=>b.value.search=l),type:"text",placeholder:"Search by driver or type...",onInput:G},null,8,["modelValue"])]),o("div",null,[t(s(_),{for:"dateFrom"},{default:n(()=>e[44]||(e[44]=[a("Date From")])),_:1}),t(s(I),{id:"dateFrom",modelValue:b.value.dateFrom,"onUpdate:modelValue":e[9]||(e[9]=l=>b.value.dateFrom=l),type:"date",onChange:G},null,8,["modelValue"])]),o("div",null,[t(s(_),{for:"dateTo"},{default:n(()=>e[45]||(e[45]=[a("Date To")])),_:1}),t(s(I),{id:"dateTo",modelValue:b.value.dateTo,"onUpdate:modelValue":e[10]||(e[10]=l=>b.value.dateTo=l),type:"date",onChange:G},null,8,["modelValue"])]),o("div",null,[t(s(_),{for:"delayCode"},{default:n(()=>e[46]||(e[46]=[a("Delay Code")])),_:1}),V(o("select",{id:"delayCode","onUpdate:modelValue":e[11]||(e[11]=l=>b.value.delayCode=l),onChange:G,class:"flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"},[e[47]||(e[47]=o("option",{value:""},"All Codes",-1)),(u(!0),v(D,null,L(c.delay_codes,l=>(u(),v("option",{key:l.id,value:l.id},x(l.code),9,Dt))),128))],544),[[M,b.value.delayCode]])]),o("div",null,[t(s(_),{for:"disputed"},{default:n(()=>e[48]||(e[48]=[a("Disputed Status")])),_:1}),V(o("select",{id:"disputed","onUpdate:modelValue":e[12]||(e[12]=l=>b.value.disputed=l),onChange:G,class:"flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"},e[49]||(e[49]=[o("option",{value:""},"All",-1),o("option",{value:"true"},"Disputed",-1),o("option",{value:"false"},"Not Disputed",-1)]),544),[[M,b.value.disputed]])]),o("div",null,[t(s(_),{for:"controllable"},{default:n(()=>e[50]||(e[50]=[a("Driver Controllable")])),_:1}),V(o("select",{id:"controllable","onUpdate:modelValue":e[13]||(e[13]=l=>b.value.controllable=l),onChange:G,class:"flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"},e[51]||(e[51]=[o("option",{value:""},"All",-1),o("option",{value:"true"},"Yes",-1),o("option",{value:"false"},"No",-1),o("option",{value:"null"},"N/A",-1)]),544),[[M,b.value.controllable]])])]),o("div",$t,[t(g,{onClick:Ce,variant:"ghost",size:"sm"},{default:n(()=>[t(A,{name:"rotate_ccw",class:"mr-2 h-4 w-4"}),e[52]||(e[52]=a(" Reset Filters "))]),_:1})])])]),_:1})]),_:1}),t(s(me),null,{default:n(()=>[t(s(ve),{class:"p-0"},{default:n(()=>[o("div",At,[t(s(qe),{class:"relative h-[500px] overflow-auto"},{default:n(()=>[t(s(He),null,{default:n(()=>[t(s(fe),{class:"sticky top-0 bg-background border-b z-10 hover:bg-background"},{default:n(()=>[t(s(ie),{class:"w-[50px]"},{default:n(()=>[o("div",Nt,[o("input",{type:"checkbox",onChange:Te,checked:Me.value,class:"h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"},null,40,Ft)])]),_:1}),c.isSuperAdmin?(u(),F(s(ie),{key:0},{default:n(()=>e[53]||(e[53]=[a("Company Name")])),_:1})):N("",!0),(u(),v(D,null,L(se,l=>t(s(ie),{key:l,class:"cursor-pointer",onClick:i=>xe(l)},{default:n(()=>[o("div",Vt,[a(x(l.replace(/_/g," ").split(" ").map(i=>i.charAt(0).toUpperCase()+i.slice(1)).join(" "))+" ",1),W.value===l?(u(),v("div",Ut,[q.value==="asc"?(u(),v("svg",zt,e[54]||(e[54]=[o("path",{d:"M8 15l4-4 4 4"},null,-1)]))):(u(),v("svg",Mt,e[55]||(e[55]=[o("path",{d:"M16 9l-4 4-4-4"},null,-1)])))])):(u(),v("div",Tt,e[56]||(e[56]=[o("svg",{class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},[o("path",{d:"M8 10l4-4 4 4"}),o("path",{d:"M16 14l-4 4-4-4"})],-1)])))])]),_:2},1032,["onClick"])),64)),t(s(ie),null,{default:n(()=>e[57]||(e[57]=[a("Actions")])),_:1})]),_:1})]),_:1}),t(s(Ge),null,{default:n(()=>[H.value.length===0?(u(),F(s(fe),{key:0},{default:n(()=>[t(s(ne),{colspan:c.isSuperAdmin?se.length+2:se.length+1,class:"text-center py-8"},{default:n(()=>e[58]||(e[58]=[a(" No delays found matching your criteria ")])),_:1},8,["colspan"])]),_:1})):N("",!0),(u(!0),v(D,null,L(H.value,l=>(u(),F(s(fe),{key:l.id,class:"hover:bg-muted/50"},{default:n(()=>[t(s(ne),{class:"text-center"},{default:n(()=>[V(o("input",{type:"checkbox",value:l.id,"onUpdate:modelValue":e[14]||(e[14]=i=>S.value=i),class:"h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"},null,8,Bt),[[Ie,S.value]])]),_:2},1024),c.isSuperAdmin?(u(),F(s(ne),{key:0},{default:n(()=>{var i;return[a(x(((i=l.tenant)==null?void 0:i.name)||"—"),1)]}),_:2},1024)):N("",!0),(u(),v(D,null,L(se,i=>t(s(ne),{key:i,class:"whitespace-nowrap"},{default:n(()=>{var k,E;return[i==="date"?(u(),v(D,{key:0},[a(x(ae(l[i])),1)],64)):i==="disputed"?(u(),v(D,{key:1},[a(x(l[i]?"Yes":"No"),1)],64)):i==="driver_controllable"?(u(),v(D,{key:2},[a(x(l[i]===null?"N/A":l[i]?"Yes":"No"),1)],64)):i==="delay_code"?(u(),v(D,{key:3},[a(x(((k=l.delay_code)==null?void 0:k.code)||"—")+" ",1),(E=l.delay_code)!=null&&E.deleted_at?(u(),v("span",Pt,"(Deleted Code)")):N("",!0)],64)):(u(),v(D,{key:4},[a(x(l[i]),1)],64))]}),_:2},1024)),64)),t(s(ne),null,{default:n(()=>[o("div",Et,[t(g,{onClick:i=>pe(l),variant:"warning",size:"sm"},{default:n(()=>[t(A,{name:"pencil",class:"mr-1 h-4 w-4"}),e[59]||(e[59]=a(" Edit "))]),_:2},1032,["onClick"]),t(g,{onClick:i=>_e(l.id),variant:"destructive",size:"sm"},{default:n(()=>[t(A,{name:"trash",class:"mr-1 h-4 w-4"}),e[60]||(e[60]=a(" Delete "))]),_:2},1032,["onClick"])])]),_:2},1024)]),_:2},1024))),128))]),_:1})]),_:1})]),c.delays.links?(u(),v("div",Lt,[o("div",jt,[o("div",Rt," Showing "+x(H.value.length)+" of "+x(c.delays.data.length)+" entries ",1),o("div",It,[o("div",Ot,[t(s(_),{for:"perPage",class:"text-sm"},{default:n(()=>e[61]||(e[61]=[a("Per page:")])),_:1}),V(o("select",{id:"perPage","onUpdate:modelValue":e[15]||(e[15]=l=>K.value=l),onChange:ke,class:"h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"},e[62]||(e[62]=[o("option",{value:"10"},"10",-1),o("option",{value:"25"},"25",-1),o("option",{value:"50"},"50",-1),o("option",{value:"100"},"100",-1)]),544),[[M,K.value]])]),o("div",Yt,[(u(!0),v(D,null,L(c.delays.links,l=>(u(),F(g,{key:l.label,onClick:i=>we(l.url),disabled:!l.url,variant:"ghost",size:"sm",class:J(["mx-1",{"bg-primary/10 text-primary border-primary":l.active}])},{default:n(()=>[o("span",{innerHTML:l.label},null,8,Wt)]),_:2},1032,["onClick","disabled","class"]))),128))])])])])):N("",!0)]),_:1})]),_:1}),t(s(h),{open:j.value,"onUpdate:open":e[17]||(e[17]=l=>j.value=l)},{default:n(()=>[t(s(ee),{class:"sm:max-w-2xl"},{default:n(()=>[t(s(te),null,{default:n(()=>[t(s(le),null,{default:n(()=>[a(x(O.value?"Edit":"Add")+" Delay",1)]),_:1}),t(s(oe),null,{default:n(()=>[a(" Fill in the details to "+x(O.value?"update":"create")+" a delay record. ",1)]),_:1})]),_:1}),t(ft,{delay:O.value,"delay-codes":be.value,tenants:c.tenants,"is-super-admin":c.isSuperAdmin,"tenant-slug":c.tenantSlug,onClose:e[16]||(e[16]=l=>j.value=!1)},null,8,["delay","delay-codes","tenants","is-super-admin","tenant-slug"])]),_:1})]),_:1},8,["open"]),t(s(h),{open:f.value,"onUpdate:open":e[21]||(e[21]=l=>f.value=l)},{default:n(()=>[t(s(ee),{class:"sm:max-w-lg"},{default:n(()=>[t(s(te),null,{default:n(()=>[t(s(le),null,{default:n(()=>e[63]||(e[63]=[a("Manage Delay Codes")])),_:1}),t(s(oe),null,{default:n(()=>e[64]||(e[64]=[a(" Create and manage delay codes for your organization. ")])),_:1})]),_:1}),o("div",qt,[o("div",Ht,[e[66]||(e[66]=o("h3",{class:"text-sm font-medium"},"Current Delay Codes",-1)),t(g,{onClick:De,size:"sm",variant:"outline"},{default:n(()=>[t(A,{name:"plus",class:"mr-2 h-4 w-4"}),e[65]||(e[65]=a(" Add New Code "))]),_:1})]),o("div",Gt,[!c.delay_codes||c.delay_codes.length===0?(u(),v("div",Jt," No delay codes found ")):(u(),v("div",Qt,[(u(!0),v(D,null,L(c.delay_codes,l=>(u(),v("div",{key:l.id,class:"flex items-center justify-between p-3 border rounded-md hover:bg-muted/50 group"},[o("div",{class:"flex-1 cursor-pointer",onClick:i=>$e(l)},[o("div",Xt,[a(x(l.code)+" ",1),l.deleted_at?(u(),v("span",Zt,"(Deleted)")):N("",!0)]),l.description?(u(),v("div",ht,x(l.description),1)):N("",!0)],8,Kt),o("div",el,[c.isSuperAdmin?(u(),v(D,{key:0},[l.deleted_at?(u(),v(D,{key:0},[t(g,{onClick:i=>Ve(l.id),size:"sm",variant:"outline"},{default:n(()=>[t(A,{name:"refresh",class:"mr-2 h-4 w-4"}),e[67]||(e[67]=a(" Restore "))]),_:2},1032,["onClick"]),t(g,{onClick:i=>Ue(l.id),size:"sm",variant:"destructive"},{default:n(()=>[t(A,{name:"trash",class:"mr-2 h-4 w-4"}),e[68]||(e[68]=a(" Permanently Delete "))]),_:2},1032,["onClick"])],64)):(u(),F(g,{key:1,onClick:i=>ye(l.id),size:"sm",variant:"destructive"},{default:n(()=>[t(A,{name:"trash",class:"mr-2 h-4 w-4"}),e[69]||(e[69]=a(" Delete "))]),_:2},1032,["onClick"]))],64)):(u(),F(g,{key:1,onClick:i=>ye(l.id),size:"sm",variant:"destructive"},{default:n(()=>[t(A,{name:"trash",class:"mr-2 h-4 w-4"}),e[70]||(e[70]=a(" Delete "))]),_:2},1032,["onClick"]))])]))),128))]))]),U.value?(u(),v("div",tl,[o("h3",ll,x(p.value?"Edit":"Add")+" Delay Code",1),o("div",ol,[o("div",null,[t(s(_),{for:"code"},{default:n(()=>e[71]||(e[71]=[a("Code")])),_:1}),t(s(I),{id:"code",modelValue:r.value.code,"onUpdate:modelValue":e[18]||(e[18]=l=>r.value.code=l),placeholder:"Enter code"},null,8,["modelValue"])]),o("div",null,[t(s(_),{for:"description"},{default:n(()=>e[72]||(e[72]=[a("Description")])),_:1}),t(s(I),{id:"description",modelValue:r.value.description,"onUpdate:modelValue":e[19]||(e[19]=l=>r.value.description=l),placeholder:"Enter description"},null,8,["modelValue"])]),o("div",nl,[t(g,{onClick:Ae,variant:"ghost",size:"sm"},{default:n(()=>e[73]||(e[73]=[a("Cancel")])),_:1}),t(g,{onClick:Fe,variant:"default",size:"sm"},{default:n(()=>e[74]||(e[74]=[a("Save")])),_:1})])])])):N("",!0)]),t(s(re),{class:"mt-6"},{default:n(()=>[t(g,{onClick:e[20]||(e[20]=l=>f.value=!1),variant:"outline"},{default:n(()=>e[75]||(e[75]=[a("Close")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),t(s(h),{open:y.value,"onUpdate:open":e[24]||(e[24]=l=>y.value=l)},{default:n(()=>[t(s(ee),null,{default:n(()=>[t(s(te),null,{default:n(()=>[t(s(le),null,{default:n(()=>e[76]||(e[76]=[a("Confirm Deletion")])),_:1}),t(s(oe),null,{default:n(()=>e[77]||(e[77]=[a(" Are you sure you want to delete this delay code? This action cannot be undone. ")])),_:1})]),_:1}),t(s(re),{class:"mt-4"},{default:n(()=>[t(g,{type:"button",onClick:e[22]||(e[22]=l=>y.value=!1),variant:"outline"},{default:n(()=>e[78]||(e[78]=[a(" Cancel ")])),_:1}),t(g,{type:"button",onClick:e[23]||(e[23]=l=>Ne(ue.value)),variant:"destructive"},{default:n(()=>e[79]||(e[79]=[a(" Delete ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),t(s(h),{open:T.value,"onUpdate:open":e[27]||(e[27]=l=>T.value=l)},{default:n(()=>[t(s(ee),null,{default:n(()=>[t(s(te),null,{default:n(()=>[t(s(le),null,{default:n(()=>e[80]||(e[80]=[a("Confirm Deletion")])),_:1}),t(s(oe),null,{default:n(()=>e[81]||(e[81]=[a(" Are you sure you want to delete this delay record? This action cannot be undone. ")])),_:1})]),_:1}),t(s(re),{class:"mt-4"},{default:n(()=>[t(g,{type:"button",onClick:e[25]||(e[25]=l=>T.value=!1),variant:"outline"},{default:n(()=>e[82]||(e[82]=[a(" Cancel ")])),_:1}),t(g,{type:"button",onClick:e[26]||(e[26]=l=>ze(Q.value)),variant:"destructive"},{default:n(()=>e[83]||(e[83]=[a(" Delete ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),t(s(h),{open:B.value,"onUpdate:open":e[30]||(e[30]=l=>B.value=l)},{default:n(()=>[t(s(ee),null,{default:n(()=>[t(s(te),null,{default:n(()=>[t(s(le),null,{default:n(()=>e[84]||(e[84]=[a("Confirm Bulk Deletion")])),_:1}),t(s(oe),null,{default:n(()=>[a(" Are you sure you want to delete "+x(S.value.length)+" delay records? This action cannot be undone. ",1)]),_:1})]),_:1}),t(s(re),{class:"mt-4"},{default:n(()=>[t(g,{type:"button",onClick:e[28]||(e[28]=l=>B.value=!1),variant:"outline"},{default:n(()=>e[85]||(e[85]=[a(" Cancel ")])),_:1}),t(g,{type:"button",onClick:e[29]||(e[29]=l=>Pe()),variant:"destructive"},{default:n(()=>e[86]||(e[86]=[a(" Delete Selected ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"])])]),_:1},8,["tenantSlug"]))}};export{$l as default};
