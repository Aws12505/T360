import{d as ke,C as J,f as ie,a as m,o as i,b as n,g as A,e as l,h as N,w as o,i as r,v as R,u as s,F as w,r as U,t as g,j as je,k as S,c as Se,l as D,m as we,n as q,W as Q}from"./app-B__ptHTF.js";import{_ as C}from"./Button.vue_vue_type_script_setup_true_lang-5rTN_W9z.js";import{_ as Z,a as h,b as ee,c as te,d as le,e as de}from"./DialogTitle.vue_vue_type_script_setup_true_lang-uSHG0f7o.js";import{_ as x,a as z}from"./Label.vue_vue_type_script_setup_true_lang-DeTGmGb3.js";import{_ as $e}from"./Checkbox.vue_vue_type_script_setup_true_lang-z-rXE9GA.js";import{_ as Ae}from"./AppLayout.vue_vue_type_script_setup_true_lang-BsTG-YEC.js";import{_ as E}from"./Icon.vue_vue_type_script_setup_true_lang-CUAESyc2.js";import{_ as Te,a as Fe,b as ne,c as oe,d as Ne,e as G}from"./TableRow.vue_vue_type_script_setup_true_lang-DGbUhPA4.js";import{_ as se}from"./Card.vue_vue_type_script_setup_true_lang-p8XobHwK.js";import{_ as Re,a as Ve}from"./CardTitle.vue_vue_type_script_setup_true_lang-RYz03wRI.js";import{_ as ae}from"./CardContent.vue_vue_type_script_setup_true_lang-C_o1U9fc.js";import{_ as De,a as Ue,b as Me}from"./AlertDescription.vue_vue_type_script_setup_true_lang-mZCLLzKb.js";import"./index-qfrlGLqJ.js";import"./check-gLnbaKCt.js";import"./createLucideIcon-Ce1UDWWH.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-DU55FZBO.js";import"./shield-DQVFrbX_.js";import"./loader-circle-BYMtkHnq.js";import"./sun-Bc9qbBBx.js";const Pe={class:"grid grid-cols-2 gap-4"},ze={key:0},Be=["value"],Le=["value"],Ee={class:"flex items-center space-x-2"},Ie={class:"mt-6 flex justify-end"},Ye=ke({__name:"RejectionForm",props:{rejection:{type:Object,default:null},reasons:{type:Array,default:()=>[]},tenants:{type:Array,default:()=>[]},isSuperAdmin:{type:Boolean,default:!1},tenantSlug:{type:String,default:null}},emits:["close"],setup(c,{emit:y}){var T,F,B,V,j,$,M;const _=c,I=y,f=J({id:(T=_.rejection)==null?void 0:T.id,tenant_id:_.isSuperAdmin?(F=_.rejection)==null?void 0:F.tenant_id:null,date:((B=_.rejection)==null?void 0:B.date)||"",driver_name:((V=_.rejection)==null?void 0:V.driver_name)||"",rejection_type:((j=_.rejection)==null?void 0:j.rejection_type)||"block",rejection_category:(($=_.rejection)==null?void 0:$.rejection_category)||"more_than_6",reason_code_id:((M=_.rejection)==null?void 0:M.reason_code_id)||null,disputed:_.rejection&&_.rejection.disputed!==null?parseInt(_.rejection.disputed)===1:!1,driver_controllable:_.rejection&&_.rejection.driver_controllable!==null?parseInt(_.rejection.driver_controllable)===1:null});ie(()=>_.rejection,b=>{b?(f.id=b.id,f.tenant_id=b.tenant_id,f.date=b.date,f.driver_name=b.driver_name,f.rejection_type=b.rejection_type,f.rejection_category=b.rejection_category,f.reason_code_id=b.reason_code_id,f.disputed=b.disputed!==null?parseInt(b.disputed)===1:!1,f.driver_controllable=b.driver_controllable!==null?parseInt(b.driver_controllable)===1:null):f.reset()},{immediate:!0});const Y=()=>{const b=!!f.id,d=_.isSuperAdmin?b?"acceptance.update.admin":"acceptance.store.admin":b?"acceptance.update":"acceptance.store",v=_.isSuperAdmin?b?{rejection:f.id}:{}:b?{tenantSlug:_.tenantSlug,rejection:f.id}:{tenantSlug:_.tenantSlug};f[b?"put":"post"](route(d,v),{onSuccess:()=>I("close")})};return(b,d)=>(i(),m("form",{onSubmit:je(Y,["prevent"])},[n("div",Pe,[c.isSuperAdmin?(i(),m("div",ze,[l(x,null,{default:o(()=>d[8]||(d[8]=[r("Company Name")])),_:1}),N(n("select",{"onUpdate:modelValue":d[0]||(d[0]=v=>s(f).tenant_id=v),class:"w-full border rounded px-2 py-1"},[d[9]||(d[9]=n("option",{value:null,disabled:""},"Select Company",-1)),(i(!0),m(w,null,U(c.tenants,v=>(i(),m("option",{key:v.id,value:v.id},g(v.name),9,Be))),128))],512),[[R,s(f).tenant_id]])])):A("",!0),n("div",null,[l(x,null,{default:o(()=>d[10]||(d[10]=[r("Date")])),_:1}),l(z,{type:"date",modelValue:s(f).date,"onUpdate:modelValue":d[1]||(d[1]=v=>s(f).date=v),class:"w-full"},null,8,["modelValue"])]),n("div",null,[l(x,null,{default:o(()=>d[11]||(d[11]=[r("Driver Name")])),_:1}),l(z,{modelValue:s(f).driver_name,"onUpdate:modelValue":d[2]||(d[2]=v=>s(f).driver_name=v),class:"w-full"},null,8,["modelValue"])]),n("div",null,[l(x,null,{default:o(()=>d[12]||(d[12]=[r("Rejection Type")])),_:1}),N(n("select",{"onUpdate:modelValue":d[3]||(d[3]=v=>s(f).rejection_type=v),class:"w-full border rounded px-2 py-1"},d[13]||(d[13]=[n("option",{value:"block"},"Block",-1),n("option",{value:"load"},"Load",-1)]),512),[[R,s(f).rejection_type]])]),n("div",null,[l(x,null,{default:o(()=>d[14]||(d[14]=[r("Rejection Category")])),_:1}),N(n("select",{"onUpdate:modelValue":d[4]||(d[4]=v=>s(f).rejection_category=v),class:"w-full border rounded px-2 py-1"},d[15]||(d[15]=[n("option",{value:"more_than_6"},"More than 6 hrs",-1),n("option",{value:"within_6"},"Within 6 hrs",-1),n("option",{value:"after_start"},"After start",-1)]),512),[[R,s(f).rejection_category]])]),n("div",null,[l(x,null,{default:o(()=>d[16]||(d[16]=[r("Reason Code")])),_:1}),N(n("select",{"onUpdate:modelValue":d[5]||(d[5]=v=>s(f).reason_code_id=v),class:"w-full border rounded px-2 py-1"},[(i(!0),m(w,null,U(c.reasons,v=>(i(),m("option",{key:v.id,value:v.id},g(v.reason_code),9,Le))),128))],512),[[R,s(f).reason_code_id]])]),n("div",Ee,[l($e,{checked:s(f).disputed,"onUpdate:checked":d[6]||(d[6]=v=>s(f).disputed=v)},null,8,["checked"]),l(x,null,{default:o(()=>d[17]||(d[17]=[r("Disputed")])),_:1})]),n("div",null,[l(x,null,{default:o(()=>d[18]||(d[18]=[r("Driver Controllable")])),_:1}),N(n("select",{"onUpdate:modelValue":d[7]||(d[7]=v=>s(f).driver_controllable=v),class:"w-full border rounded px-2 py-1"},d[19]||(d[19]=[n("option",{value:null},"N/A",-1),n("option",{value:!0},"Yes",-1),n("option",{value:!1},"No",-1)]),512),[[R,s(f).driver_controllable]])])]),n("div",Ie,[l(C,{type:"submit",disabled:s(f).processing},{default:o(()=>[r(g(s(f).id?"Update":"Create"),1)]),_:1},8,["disabled"])])],32))}}),We={class:"max-w-7xl mx-auto p-6 space-y-8"},qe={class:"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"},Oe={class:"flex flex-wrap gap-3"},He={class:"flex flex-col gap-2"},Qe={class:"flex flex-wrap gap-2"},Ge={key:0,class:"text-sm text-muted-foreground"},Je={key:0},Ke={key:1},Xe={class:"flex flex-col gap-4"},Ze={class:"grid grid-cols-1 sm:grid-cols-3 gap-4 w-full"},he={class:"grid grid-cols-1 sm:grid-cols-3 gap-4 w-full"},et=["value"],tt={class:"grid grid-cols-1 sm:grid-cols-2 gap-4 w-full"},lt={class:"flex justify-end"},nt={class:"overflow-x-auto"},ot={class:"flex items-center"},st={key:0,class:"ml-2"},at={key:0,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},rt={key:1,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},dt={key:1,class:"ml-2 opacity-50"},it={key:1,class:"capitalize"},ut={class:"flex space-x-2"},ft={key:0,class:"bg-muted/20 px-4 py-3 border-t"},pt={class:"flex justify-between items-center"},mt={class:"text-sm text-muted-foreground flex items-center gap-4"},vt={class:"flex items-center gap-2"},ct=["value"],yt={class:"flex"},gt=["innerHTML"],bt={class:"mt-4 space-y-4"},_t={class:"flex items-center justify-between"},Ct={class:"max-h-[400px] overflow-y-auto"},xt={key:0,class:"text-center py-8 text-muted-foreground border rounded-md"},kt={key:1,class:"space-y-2"},jt=["onClick"],St={class:"font-medium"},wt={key:0,class:"text-sm text-muted-foreground mt-1"},$t={class:"opacity-0 group-hover:opacity-100 transition-opacity"},At={key:0,class:"border rounded-md p-4 space-y-4"},Tt={class:"text-sm font-medium"},Ft={class:"space-y-3"},Nt={class:"flex justify-end space-x-2"},Kt={__name:"Index",props:{rejections:{type:Object,default:()=>({data:[],links:[]})},tenantSlug:{type:String,default:null},rejection_reason_codes:Array,tenants:{type:Array,default:()=>[]},isSuperAdmin:{type:Boolean,default:!1},dateFilter:{type:String,default:"full"},dateRange:{type:Object,default:()=>({label:"All Time"})},perPage:{type:Number,default:10}},setup(c){const y=c,_=[{title:y.tenantSlug?"Dashboard":"Admin Dashboard",href:y.tenantSlug?route("dashboard",{tenantSlug:y.tenantSlug}):route("admin.dashboard")},{title:"Acceptance",href:y.tenantSlug?route("acceptance",{tenantSlug:y.tenantSlug}):route("admin.acceptance")}],I=S(!1),f=S(!1),Y=S(null),T=S(""),F=S(y.dateFilter||"full"),B=S(y.perPage),V=S(!1),j=S(null),$=S({reason_code:"",description:""}),M=S(!1),b=S(null),d=["date","rejection_type","driver_name","penalty","reason_code","disputed","driver_controllable"],v=S("date"),L=S("desc"),p=S({search:"",dateFrom:"",dateTo:"",rejectionType:"",reasonCode:"",penalty:"",disputed:"",driverControllable:""}),K=Se(()=>{let a=[...y.rejections.data];if(p.value.search){const e=p.value.search.toLowerCase();a=a.filter(t=>{var u,k,O,H;return((u=t.driver_name)==null?void 0:u.toLowerCase().includes(e))||((k=t.rejection_type)==null?void 0:k.toLowerCase().includes(e))||((H=(O=t.reason_code)==null?void 0:O.reason_code)==null?void 0:H.toLowerCase().includes(e))})}if(p.value.dateFrom&&(a=a.filter(e=>e.date&&e.date>=p.value.dateFrom)),p.value.dateTo&&(a=a.filter(e=>e.date&&e.date<=p.value.dateTo)),p.value.rejectionType&&(a=a.filter(e=>e.rejection_type===p.value.rejectionType)),p.value.reasonCode&&(a=a.filter(e=>e.reason_code&&e.reason_code.id===parseInt(p.value.reasonCode))),p.value.penalty){const e=p.value.penalty.toLowerCase();a=a.filter(t=>t.penalty&&t.penalty.toString().toLowerCase().includes(e))}if(p.value.disputed!==""){const e=p.value.disputed==="true";a=a.filter(t=>t.disputed===e)}if(p.value.driverControllable!=="")if(p.value.driverControllable==="null")a=a.filter(e=>e.driver_controllable===null);else{const e=p.value.driverControllable==="true";a=a.filter(t=>t.driver_controllable===e)}return a.sort((e,t)=>{var O,H;let u=e[v.value],k=t[v.value];return v.value==="reason_code"&&(u=((O=e.reason_code)==null?void 0:O.reason_code)||"",k=((H=t.reason_code)==null?void 0:H.reason_code)||""),u===null?1:k===null?-1:(typeof u=="string"&&(u=u.toLowerCase(),k=k.toLowerCase()),u<k?L.value==="asc"?-1:1:u>k?L.value==="asc"?1:-1:0)}),a});function ue(a){v.value===a?L.value=L.value==="asc"?"desc":"asc":(v.value=a,L.value="asc")}function P(){}function fe(){p.value={search:"",dateFrom:"",dateTo:"",rejectionType:"",reasonCode:"",penalty:"",disputed:"",driverControllable:""},W("full")}const re=(a=null)=>{Y.value=a,I.value=!0},pe=()=>{f.value=!0,V.value=!1,j.value=null},me=()=>{$.value={reason_code:"",description:""},j.value=null,V.value=!0},ve=a=>{$.value={reason_code:a.reason_code,description:a.description||""},j.value=a.id,V.value=!0},ce=()=>{V.value=!1,j.value=null},ye=a=>{b.value=a,M.value=!0},ge=()=>{const a=J({reason_code:$.value.reason_code,description:$.value.description}),e=j.value?y.isSuperAdmin?"rejection-reason-codes.update.admin":"rejection-reason-codes.update":y.isSuperAdmin?"rejection-reason-codes.store.admin":"rejection-reason-codes.store",t=j.value?y.isSuperAdmin?{code:j.value}:{tenantSlug:y.tenantSlug,code:j.value}:y.isSuperAdmin?{}:{tenantSlug:y.tenantSlug};(j.value?a.put:a.post).call(a,route(e,t),{onSuccess:()=>{T.value=j.value?"Reason code updated successfully.":"Reason code created successfully.",V.value=!1,j.value=null,Q.reload({only:["rejection_reason_codes"]})},onError:k=>{console.error(k)}})},be=a=>{const e=J({}),t=y.isSuperAdmin?"rejection-reason-codes.destroy.admin":"rejection-reason-codes.destroy",u=y.isSuperAdmin?{code:a}:{tenantSlug:y.tenantSlug,code:a};e.delete(route(t,u),{onSuccess:()=>{T.value="Reason code deleted successfully.",M.value=!1,Q.reload({only:["rejection_reason_codes"]})}})},_e=a=>{if(!confirm("Are you sure you want to delete this rejection?"))return;const e=J({}),t=y.isSuperAdmin?"acceptance.destroy.admin":"acceptance.destroy",u=y.isSuperAdmin?{rejection:a}:{tenantSlug:y.tenantSlug,rejection:a};e.delete(route(t,u),{preserveScroll:!0,onSuccess:()=>{T.value="Rejection deleted successfully."}})},Ce=a=>{a&&Q.get(a,{},{only:["rejections"]})};function W(a){F.value=a;const e=y.tenantSlug?route("acceptance.index",{tenantSlug:y.tenantSlug}):route("acceptance.index.admin");Q.get(e,{dateFilter:a,perPage:B.value},{preserveState:!0})}function xe(){const a=y.tenantSlug?route("acceptance.index",{tenantSlug:y.tenantSlug}):route("acceptance.index.admin");Q.get(a,{dateFilter:F.value,perPage:B.value},{preserveState:!0})}function X(a){if(!a)return"";const e=a.split("-");if(e.length!==3)return a;const[t,u,k]=e;return`${Number(u)}/${Number(k)}/${t}`}return ie(T,a=>{a&&setTimeout(()=>{T.value=""},5e3)}),(a,e)=>(i(),D(Ae,{breadcrumbs:_,tenantSlug:c.tenantSlug},{default:o(()=>[l(s(we),{title:"Acceptance"}),n("div",We,[T.value?(i(),D(s(De),{key:0,variant:"success"},{default:o(()=>[l(s(Ue),null,{default:o(()=>e[25]||(e[25]=[r("Success")])),_:1}),l(s(Me),null,{default:o(()=>[r(g(T.value),1)]),_:1})]),_:1})):A("",!0),n("div",qe,[e[28]||(e[28]=n("h1",{class:"text-2xl font-bold text-gray-800 dark:text-gray-200"},"Acceptance",-1)),n("div",Oe,[l(C,{onClick:e[0]||(e[0]=t=>re()),variant:"default"},{default:o(()=>[l(E,{name:"plus",class:"mr-2 h-4 w-4"}),e[26]||(e[26]=r(" Add Rejection "))]),_:1}),c.isSuperAdmin?(i(),D(C,{key:0,onClick:e[1]||(e[1]=t=>pe()),variant:"outline"},{default:o(()=>[l(E,{name:"settings",class:"mr-2 h-4 w-4"}),e[27]||(e[27]=r(" Manage Reason Codes "))]),_:1})):A("",!0)])]),l(s(se),null,{default:o(()=>[l(s(ae),{class:"p-4"},{default:o(()=>[n("div",He,[n("div",Qe,[l(C,{onClick:e[2]||(e[2]=t=>W("yesterday")),variant:"outline",size:"sm",class:q({"bg-primary/10 text-primary border-primary":F.value==="yesterday"})},{default:o(()=>e[29]||(e[29]=[r(" Yesterday ")])),_:1},8,["class"]),l(C,{onClick:e[3]||(e[3]=t=>W("current-week")),variant:"outline",size:"sm",class:q({"bg-primary/10 text-primary border-primary":F.value==="current-week"})},{default:o(()=>e[30]||(e[30]=[r(" Current Week ")])),_:1},8,["class"]),l(C,{onClick:e[4]||(e[4]=t=>W("6w")),variant:"outline",size:"sm",class:q({"bg-primary/10 text-primary border-primary":F.value==="6w"})},{default:o(()=>e[31]||(e[31]=[r(" 6 Weeks ")])),_:1},8,["class"]),l(C,{onClick:e[5]||(e[5]=t=>W("quarterly")),variant:"outline",size:"sm",class:q({"bg-primary/10 text-primary border-primary":F.value==="quarterly"})},{default:o(()=>e[32]||(e[32]=[r(" Quarterly ")])),_:1},8,["class"]),l(C,{onClick:e[6]||(e[6]=t=>W("full")),variant:"outline",size:"sm",class:q({"bg-primary/10 text-primary border-primary":F.value==="full"})},{default:o(()=>e[33]||(e[33]=[r(" Full ")])),_:1},8,["class"])]),c.dateRange?(i(),m("div",Ge,[c.dateRange.start&&c.dateRange.end?(i(),m("span",Je," Showing data from "+g(X(c.dateRange.start))+" to "+g(X(c.dateRange.end)),1)):(i(),m("span",Ke,g(c.dateRange.label),1))])):A("",!0)])]),_:1})]),_:1}),l(s(se),null,{default:o(()=>[l(s(Re),null,{default:o(()=>[l(s(Ve),null,{default:o(()=>e[34]||(e[34]=[r("Filters")])),_:1})]),_:1}),l(s(ae),null,{default:o(()=>[n("div",Xe,[n("div",Ze,[n("div",null,[l(s(x),{for:"search"},{default:o(()=>e[35]||(e[35]=[r("Search")])),_:1}),l(s(z),{id:"search",modelValue:p.value.search,"onUpdate:modelValue":e[7]||(e[7]=t=>p.value.search=t),type:"text",placeholder:"Search by driver name...",onInput:P},null,8,["modelValue"])]),n("div",null,[l(s(x),{for:"dateFrom"},{default:o(()=>e[36]||(e[36]=[r("Date From")])),_:1}),l(s(z),{id:"dateFrom",modelValue:p.value.dateFrom,"onUpdate:modelValue":e[8]||(e[8]=t=>p.value.dateFrom=t),type:"date",onChange:P},null,8,["modelValue"])]),n("div",null,[l(s(x),{for:"dateTo"},{default:o(()=>e[37]||(e[37]=[r("Date To")])),_:1}),l(s(z),{id:"dateTo",modelValue:p.value.dateTo,"onUpdate:modelValue":e[9]||(e[9]=t=>p.value.dateTo=t),type:"date",onChange:P},null,8,["modelValue"])])]),n("div",he,[n("div",null,[l(s(x),{for:"rejectionType"},{default:o(()=>e[38]||(e[38]=[r("Rejection Type")])),_:1}),N(n("select",{id:"rejectionType","onUpdate:modelValue":e[10]||(e[10]=t=>p.value.rejectionType=t),onChange:P,class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},e[39]||(e[39]=[n("option",{value:""},"All Types",-1),n("option",{value:"pickup"},"Pickup",-1),n("option",{value:"delivery"},"Delivery",-1)]),544),[[R,p.value.rejectionType]])]),n("div",null,[l(s(x),{for:"reasonCode"},{default:o(()=>e[40]||(e[40]=[r("Reason Code")])),_:1}),N(n("select",{id:"reasonCode","onUpdate:modelValue":e[11]||(e[11]=t=>p.value.reasonCode=t),onChange:P,class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},[e[41]||(e[41]=n("option",{value:""},"All Reason Codes",-1)),(i(!0),m(w,null,U(c.rejection_reason_codes,t=>(i(),m("option",{key:t.id,value:t.id},g(t.reason_code),9,et))),128))],544),[[R,p.value.reasonCode]])]),n("div",null,[l(s(x),{for:"penalty"},{default:o(()=>e[42]||(e[42]=[r("Penalty")])),_:1}),l(s(z),{id:"penalty",modelValue:p.value.penalty,"onUpdate:modelValue":e[12]||(e[12]=t=>p.value.penalty=t),type:"text",placeholder:"Filter by penalty...",onInput:P},null,8,["modelValue"])])]),n("div",tt,[n("div",null,[l(s(x),{for:"disputed"},{default:o(()=>e[43]||(e[43]=[r("Disputed")])),_:1}),N(n("select",{id:"disputed","onUpdate:modelValue":e[13]||(e[13]=t=>p.value.disputed=t),onChange:P,class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},e[44]||(e[44]=[n("option",{value:""},"All",-1),n("option",{value:"true"},"Yes",-1),n("option",{value:"false"},"No",-1)]),544),[[R,p.value.disputed]])]),n("div",null,[l(s(x),{for:"driverControllable"},{default:o(()=>e[45]||(e[45]=[r("Driver Controllable")])),_:1}),N(n("select",{id:"driverControllable","onUpdate:modelValue":e[14]||(e[14]=t=>p.value.driverControllable=t),onChange:P,class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},e[46]||(e[46]=[n("option",{value:""},"All",-1),n("option",{value:"true"},"Yes",-1),n("option",{value:"false"},"No",-1),n("option",{value:"null"},"N/A",-1)]),544),[[R,p.value.driverControllable]])])]),n("div",lt,[l(C,{onClick:fe,variant:"ghost",size:"sm"},{default:o(()=>[l(E,{name:"rotate-ccw",class:"mr-2 h-4 w-4"}),e[47]||(e[47]=r(" Reset Filters "))]),_:1})])])]),_:1})]),_:1}),l(s(se),null,{default:o(()=>[l(s(ae),{class:"p-0"},{default:o(()=>[n("div",nt,[l(s(Te),null,{default:o(()=>[l(s(Fe),null,{default:o(()=>[l(s(ne),null,{default:o(()=>[c.isSuperAdmin?(i(),D(s(oe),{key:0},{default:o(()=>e[48]||(e[48]=[r("Company Name")])),_:1})):A("",!0),(i(),m(w,null,U(d,t=>l(s(oe),{key:t,class:"cursor-pointer",onClick:u=>ue(t)},{default:o(()=>[n("div",ot,[r(g(t.replace(/_/g," ").replace(/^./,u=>u.toUpperCase()))+" ",1),v.value===t?(i(),m("div",st,[L.value==="asc"?(i(),m("svg",at,e[49]||(e[49]=[n("path",{d:"M8 15l4-4 4 4"},null,-1)]))):(i(),m("svg",rt,e[50]||(e[50]=[n("path",{d:"M16 9l-4 4-4-4"},null,-1)])))])):(i(),m("div",dt,e[51]||(e[51]=[n("svg",{class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},[n("path",{d:"M8 10l4-4 4 4"}),n("path",{d:"M16 14l-4 4-4-4"})],-1)])))])]),_:2},1032,["onClick"])),64)),l(s(oe),null,{default:o(()=>e[52]||(e[52]=[r("Actions")])),_:1})]),_:1})]),_:1}),l(s(Ne),null,{default:o(()=>[K.value.length===0?(i(),D(s(ne),{key:0},{default:o(()=>[l(s(G),{colspan:c.isSuperAdmin?d.length+2:d.length+1,class:"text-center py-8"},{default:o(()=>e[53]||(e[53]=[r(" No rejections found matching your criteria ")])),_:1},8,["colspan"])]),_:1})):A("",!0),(i(!0),m(w,null,U(K.value,t=>(i(),D(s(ne),{key:t.id,class:"hover:bg-muted/50"},{default:o(()=>[c.isSuperAdmin?(i(),D(s(G),{key:0},{default:o(()=>{var u;return[r(g(((u=t.tenant)==null?void 0:u.name)||"—"),1)]}),_:2},1024)):A("",!0),(i(),m(w,null,U(d,u=>l(s(G),{key:u,class:"whitespace-nowrap"},{default:o(()=>{var k;return[u==="date"?(i(),m(w,{key:0},[r(g(X(t[u])),1)],64)):u==="rejection_type"?(i(),m("span",it,g(t[u]),1)):u==="reason_code"?(i(),m(w,{key:2},[r(g(((k=t.reason_code)==null?void 0:k.reason_code)||"—"),1)],64)):u==="disputed"?(i(),m(w,{key:3},[r(g(t[u]?"Yes":"No"),1)],64)):u==="driver_controllable"?(i(),m(w,{key:4},[r(g(t[u]===null?"N/A":t[u]?"Yes":"No"),1)],64)):(i(),m(w,{key:5},[r(g(t[u]),1)],64))]}),_:2},1024)),64)),l(s(G),null,{default:o(()=>[n("div",ut,[l(C,{size:"sm",onClick:u=>re(t),variant:"warning"},{default:o(()=>[l(E,{name:"pencil",class:"mr-1 h-4 w-4"}),e[54]||(e[54]=r(" Edit "))]),_:2},1032,["onClick"]),l(C,{size:"sm",variant:"destructive",onClick:u=>_e(t.id)},{default:o(()=>[l(E,{name:"trash",class:"mr-1 h-4 w-4"}),e[55]||(e[55]=r(" Delete "))]),_:2},1032,["onClick"])])]),_:2},1024)]),_:2},1024))),128))]),_:1})]),_:1})]),c.rejections.links?(i(),m("div",ft,[n("div",pt,[n("div",mt,[n("span",null,"Showing "+g(K.value.length)+" of "+g(c.rejections.data.length)+" entries",1),n("div",vt,[e[56]||(e[56]=n("span",{class:"text-sm"},"Show:",-1)),N(n("select",{"onUpdate:modelValue":e[15]||(e[15]=t=>B.value=t),onChange:xe,class:"h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"},[(i(),m(w,null,U([10,25,50,100],t=>n("option",{key:t,value:t},g(t),9,ct)),64))],544),[[R,B.value]])])]),n("div",yt,[(i(!0),m(w,null,U(c.rejections.links,t=>(i(),D(C,{key:t.label,onClick:u=>Ce(t.url),disabled:!t.url,variant:"ghost",size:"sm",class:q(["mx-1",{"bg-primary/10 text-primary border-primary":t.active}])},{default:o(()=>[n("span",{innerHTML:t.label},null,8,gt)]),_:2},1032,["onClick","disabled","class"]))),128))])])])):A("",!0)]),_:1})]),_:1}),l(s(Z),{open:I.value,"onUpdate:open":e[17]||(e[17]=t=>I.value=t)},{default:o(()=>[l(s(h),{class:"sm:max-w-2xl"},{default:o(()=>[l(s(ee),null,{default:o(()=>[l(s(te),null,{default:o(()=>[r(g(Y.value?"Edit":"Add")+" Rejection",1)]),_:1}),l(s(le),null,{default:o(()=>[r(" Fill in the details to "+g(Y.value?"update":"add")+" a rejection. ",1)]),_:1})]),_:1}),l(Ye,{rejection:Y.value,reasons:c.rejection_reason_codes,tenants:c.tenants,"is-super-admin":c.isSuperAdmin,"tenant-slug":c.tenantSlug,onClose:e[16]||(e[16]=t=>I.value=!1)},null,8,["rejection","reasons","tenants","is-super-admin","tenant-slug"])]),_:1})]),_:1},8,["open"]),c.isSuperAdmin?(i(),D(s(Z),{key:1,open:f.value,"onUpdate:open":e[21]||(e[21]=t=>f.value=t)},{default:o(()=>[l(s(h),{class:"sm:max-w-lg"},{default:o(()=>[l(s(ee),null,{default:o(()=>[l(s(te),null,{default:o(()=>e[57]||(e[57]=[r("Manage Reason Codes")])),_:1}),l(s(le),null,{default:o(()=>e[58]||(e[58]=[r(" Create and manage reason codes for rejections. ")])),_:1})]),_:1}),n("div",bt,[n("div",_t,[e[60]||(e[60]=n("h3",{class:"text-sm font-medium"},"Current Reason Codes",-1)),l(C,{onClick:me,size:"sm",variant:"outline"},{default:o(()=>[l(E,{name:"plus",class:"mr-2 h-4 w-4"}),e[59]||(e[59]=r(" Add New Code "))]),_:1})]),n("div",Ct,[!c.rejection_reason_codes||c.rejection_reason_codes.length===0?(i(),m("div",xt," No reason codes found ")):(i(),m("div",kt,[(i(!0),m(w,null,U(c.rejection_reason_codes,t=>(i(),m("div",{key:t.id,class:"flex items-center justify-between p-3 border rounded-md hover:bg-muted/50 group"},[n("div",{class:"flex-1 cursor-pointer",onClick:u=>ve(t)},[n("div",St,g(t.reason_code),1),t.description?(i(),m("div",wt,g(t.description),1)):A("",!0)],8,jt),n("div",$t,[l(C,{onClick:u=>ye(t.id),size:"sm",variant:"ghost",class:"h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-destructive/10"},{default:o(()=>[l(E,{name:"trash",class:"h-4 w-4"}),e[61]||(e[61]=n("span",{class:"sr-only"},"Delete",-1))]),_:2},1032,["onClick"])])]))),128))]))]),V.value?(i(),m("div",At,[n("h3",Tt,g(j.value?"Edit":"Add")+" Reason Code",1),n("div",Ft,[n("div",null,[l(s(x),{for:"reason_code"},{default:o(()=>e[62]||(e[62]=[r("Code")])),_:1}),l(s(z),{id:"reason_code",modelValue:$.value.reason_code,"onUpdate:modelValue":e[18]||(e[18]=t=>$.value.reason_code=t),placeholder:"Enter reason code"},null,8,["modelValue"])]),n("div",null,[l(s(x),{for:"description"},{default:o(()=>e[63]||(e[63]=[r("Description")])),_:1}),l(s(z),{id:"description",modelValue:$.value.description,"onUpdate:modelValue":e[19]||(e[19]=t=>$.value.description=t),placeholder:"Enter description"},null,8,["modelValue"])]),n("div",Nt,[l(C,{onClick:ce,variant:"ghost",size:"sm"},{default:o(()=>e[64]||(e[64]=[r("Cancel")])),_:1}),l(C,{onClick:ge,variant:"default",size:"sm"},{default:o(()=>e[65]||(e[65]=[r("Save")])),_:1})])])])):A("",!0)]),l(s(de),{class:"mt-6"},{default:o(()=>[l(C,{onClick:e[20]||(e[20]=t=>f.value=!1),variant:"outline"},{default:o(()=>e[66]||(e[66]=[r("Close")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"])):A("",!0),l(s(Z),{open:M.value,"onUpdate:open":e[24]||(e[24]=t=>M.value=t)},{default:o(()=>[l(s(h),null,{default:o(()=>[l(s(ee),null,{default:o(()=>[l(s(te),null,{default:o(()=>e[67]||(e[67]=[r("Confirm Deletion")])),_:1}),l(s(le),null,{default:o(()=>e[68]||(e[68]=[r(" Are you sure you want to delete this reason code? This action cannot be undone. ")])),_:1})]),_:1}),l(s(de),{class:"mt-4"},{default:o(()=>[l(C,{type:"button",onClick:e[22]||(e[22]=t=>M.value=!1),variant:"outline"},{default:o(()=>e[69]||(e[69]=[r(" Cancel ")])),_:1}),l(C,{type:"button",onClick:e[23]||(e[23]=t=>be(b.value)),variant:"destructive"},{default:o(()=>e[70]||(e[70]=[r(" Delete ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"])])]),_:1},8,["tenantSlug"]))}};export{Kt as default};
