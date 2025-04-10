import{k as x,C as O,c as ge,f as ye,l as w,o as d,w as o,e as s,b as n,u as t,m as xe,g as V,i,t as c,j as H,h as b,v as C,a as p,F as k,r as L,n as I,O as ke,A as K,W as be}from"./app-B__ptHTF.js";import{_ as _e}from"./AppLayout.vue_vue_type_script_setup_true_lang-BsTG-YEC.js";import{_ as $}from"./Icon.vue_vue_type_script_setup_true_lang-CUAESyc2.js";import{_ as we,a as Ce,b as R,c as W,d as Se,e as F}from"./TableRow.vue_vue_type_script_setup_true_lang-DGbUhPA4.js";import{_ as g}from"./Button.vue_vue_type_script_setup_true_lang-5rTN_W9z.js";import{_ as Y}from"./Card.vue_vue_type_script_setup_true_lang-p8XobHwK.js";import{_ as Ve,a as $e}from"./CardTitle.vue_vue_type_script_setup_true_lang-RYz03wRI.js";import{_ as J}from"./CardContent.vue_vue_type_script_setup_true_lang-C_o1U9fc.js";import{_ as Q,a as X,b as Z,c as ee,d as te,e as ne}from"./DialogTitle.vue_vue_type_script_setup_true_lang-uSHG0f7o.js";import{_ as m,a as E}from"./Label.vue_vue_type_script_setup_true_lang-DeTGmGb3.js";import{_ as Te,a as Ae,b as Me}from"./AlertDescription.vue_vue_type_script_setup_true_lang-mZCLLzKb.js";import"./index-qfrlGLqJ.js";import"./createLucideIcon-Ce1UDWWH.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-DU55FZBO.js";import"./shield-DQVFrbX_.js";import"./check-gLnbaKCt.js";import"./loader-circle-BYMtkHnq.js";import"./sun-Bc9qbBBx.js";const De={class:"max-w-7xl mx-auto p-6 space-y-8"},Le={class:"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"},Ee={class:"flex flex-wrap gap-3"},Be={class:"cursor-pointer"},Ue={class:"flex flex-col sm:flex-row justify-between items-end gap-4"},Ie={class:"grid grid-cols-1 sm:grid-cols-3 gap-4 w-full"},Fe={class:"overflow-x-auto"},Ne={class:"flex items-center"},he={key:3,class:"ml-2"},ze={key:0,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},qe={key:1,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},je={key:4,class:"ml-2 opacity-50"},Ge={class:"flex space-x-2"},Oe={key:0,class:"bg-muted/20 px-4 py-3 border-t"},Re={class:"flex justify-between items-center"},We={class:"text-sm text-muted-foreground"},Pe={class:"flex"},He=["innerHTML"],Ke={key:0,class:"col-span-2"},Ye={class:"relative"},Je=["value"],Qe={class:"relative"},Xe={class:"relative"},Ze={class:"relative"},et={class:"relative"},tt={class:"sm:col-span-2"},nt={class:"flex items-center space-x-2"},lt={class:"sm:col-span-2"},st={class:"flex items-center space-x-2"},ot={key:0,class:"ml-2 text-xs text-red-600 bg-red-100 px-2 py-0.5 rounded-full"},rt={key:1,class:"ml-2 text-xs text-green-600 bg-green-100 px-2 py-0.5 rounded-full"},Vt={__name:"Truck",props:{entries:Object,tenantSlug:String,SuperAdmin:Boolean,tenants:Array},setup(y){const f=y,_=x(""),T=x(!1),A=x(!1),N=x("Create Entry"),B=x("Create"),h=x(null),z=x(null),M=x("truckid"),S=x("asc"),v=x({search:"",type:"",make:""}),le=[{title:f.tenantSlug?"Dashboard":"Admin Dashboard",href:f.tenantSlug?route("dashboard",{tenantSlug:f.tenantSlug}):route("admin.dashboard")},{title:"Trucks",href:"#"}],U=["truckid","type","make","fuel","license","vin","inspection_status","inspection_expiry_date","is_active","is_returned"],r=O({id:null,truckid:null,type:"daycab",make:"international",fuel:"diesel",license:null,vin:"",tenant_id:f.SuperAdmin?"":null,is_active:!0,is_returned:!1,inspection_status:"good",inspection_expiry_date:new Date().toISOString().split("T")[0]}),P=O({csv_file:null}),se=O({}),q=ge(()=>{let a=[...f.entries.data];if(v.value.search){const e=v.value.search.toLowerCase();a=a.filter(l=>{var u;return String(l.truckid).includes(e)||((u=l.vin)==null?void 0:u.toLowerCase().includes(e))})}return v.value.type&&(a=a.filter(e=>{var l;return((l=e.type)==null?void 0:l.toLowerCase())===v.value.type.toLowerCase()})),v.value.make&&(a=a.filter(e=>{var l;return((l=e.make)==null?void 0:l.toLowerCase())===v.value.make.toLowerCase()})),a.sort((e,l)=>{let u=e[M.value],D=l[M.value];return u===null?1:D===null?-1:(typeof u=="string"&&(u=u.toLowerCase(),D=D.toLowerCase()),u<D?S.value==="asc"?-1:1:u>D?S.value==="asc"?1:-1:0)}),a});function oe(a){M.value===a?S.value=S.value==="asc"?"desc":"asc":(M.value=a,S.value="asc")}function j(){}function re(){v.value={search:"",type:"",make:""}}function ae(){r.reset(),r.is_active=!0,r.tenant_id=null,N.value="Create Truck",B.value="Create",T.value=!0}function ie(a){r.id=a.id,r.truckid=a.truckid,r.type=a.type?a.type.toLowerCase():"",r.make=a.make?a.make.toLowerCase():"",r.fuel=a.fuel,r.license=a.license,r.vin=a.vin,r.is_active=!!a.is_active,r.is_returned=!!a.is_returned,r.tenant_id=a.tenant_id,r.inspection_status=a.inspection_status||"good",r.inspection_expiry_date=a.inspection_expiry_date||new Date().toISOString().split("T")[0],N.value="Edit Truck",B.value="Update",T.value=!0}function G(){T.value=!1}function ue(){const a={truckid:Number(r.truckid),type:r.type,make:r.make,fuel:r.fuel,license:Number(r.license),vin:r.vin,is_active:r.is_active?1:0,is_returned:r.is_returned?1:0,tenant_id:r.tenant_id,inspection_status:r.inspection_status,inspection_expiry_date:r.inspection_expiry_date};r.id?r.put(f.SuperAdmin?route("truck.update.admin",[r.id]):route("truck.update",[f.tenantSlug,r.id]),{data:a,onSuccess:()=>{_.value="Truck updated successfully.",G()},onError:()=>alert("Error updating truck")}):r.post(f.SuperAdmin?route("truck.store.admin"):route("truck.store",f.tenantSlug),{data:a,onSuccess:()=>{_.value="Truck created successfully.",G()},onError:()=>alert("Error creating truck")})}function de(a){z.value=a,A.value=!0}function pe(){se.delete(f.SuperAdmin?route("truck.destroy.admin",[z.value]):route("truck.destroy",[f.tenantSlug,z.value]),{onSuccess:()=>{_.value="Truck deleted successfully.",A.value=!1}})}function fe(a){var l;const e=(l=a.target.files)==null?void 0:l[0];e&&(P.csv_file=e,P.post(f.SuperAdmin?route("truck.import.admin"):route("truck.import",f.tenantSlug),{forceFormData:!0,onSuccess:()=>_.value="Data imported successfully.",onError:()=>alert("Import failed")}))}function ce(){var e,l;const a=f.SuperAdmin?route("truck.export.admin"):route("truck.export",f.tenantSlug);(e=h.value)==null||e.setAttribute("action",a),(l=h.value)==null||l.submit()}function me(a){a&&be.get(a,{},{only:["entries"]})}function ve(a){return a?new Date(a+"T00:00:00").toLocaleDateString():"—"}return ye(_,a=>{a&&setTimeout(()=>{_.value=""},5e3)}),(a,e)=>(d(),w(_e,{breadcrumbs:le,tenantSlug:y.tenantSlug},{default:o(()=>[s(t(xe),{title:"Trucks"}),n("div",De,[_.value?(d(),w(t(Te),{key:0,variant:"success"},{default:o(()=>[s(t(Ae),null,{default:o(()=>e[17]||(e[17]=[i("Success")])),_:1}),s(t(Me),null,{default:o(()=>[i(c(_.value),1)]),_:1})]),_:1})):V("",!0),n("div",Le,[e[21]||(e[21]=n("h1",{class:"text-2xl font-bold text-gray-800 dark:text-gray-200"},"Truck Management",-1)),n("div",Ee,[s(t(g),{onClick:ae,variant:"default"},{default:o(()=>[s($,{name:"plus",class:"mr-2 h-4 w-4"}),e[18]||(e[18]=i(" Create New Truck "))]),_:1}),n("label",Be,[s(t(g),{variant:"secondary",as:"span"},{default:o(()=>[s($,{name:"upload",class:"mr-2 h-4 w-4"}),e[19]||(e[19]=i(" Import CSV "))]),_:1}),n("input",{type:"file",class:"hidden",onChange:fe,accept:".csv, .txt"},null,32)]),s(t(g),{onClick:H(ce,["prevent"]),variant:"outline"},{default:o(()=>[s($,{name:"download",class:"mr-2 h-4 w-4"}),e[20]||(e[20]=i(" Export CSV "))]),_:1})])]),s(t(Y),null,{default:o(()=>[s(t(Ve),null,{default:o(()=>[s(t($e),null,{default:o(()=>e[22]||(e[22]=[i("Filters")])),_:1})]),_:1}),s(t(J),null,{default:o(()=>[n("div",Ue,[n("div",Ie,[n("div",null,[s(t(m),{for:"search"},{default:o(()=>e[23]||(e[23]=[i("Search")])),_:1}),s(t(E),{id:"search",modelValue:v.value.search,"onUpdate:modelValue":e[0]||(e[0]=l=>v.value.search=l),type:"text",placeholder:"Search by truck ID or VIN...",onInput:j},null,8,["modelValue"])]),n("div",null,[s(t(m),{for:"type"},{default:o(()=>e[24]||(e[24]=[i("Type")])),_:1}),b(n("select",{id:"type","onUpdate:modelValue":e[1]||(e[1]=l=>v.value.type=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",onChange:j},e[25]||(e[25]=[n("option",{value:""},"All Types",-1),n("option",{value:"daycab"},"Daycab",-1),n("option",{value:"sleepercab"},"Sleepercab",-1)]),544),[[C,v.value.type]])]),n("div",null,[s(t(m),{for:"make"},{default:o(()=>e[26]||(e[26]=[i("Make")])),_:1}),b(n("select",{id:"make","onUpdate:modelValue":e[2]||(e[2]=l=>v.value.make=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",onChange:j},e[27]||(e[27]=[n("option",{value:""},"All Makes",-1),n("option",{value:"international"},"International",-1),n("option",{value:"kenworth"},"Kenworth",-1),n("option",{value:"peterbilt"},"Peterbilt",-1),n("option",{value:"volvo"},"Volvo",-1),n("option",{value:"freightliner"},"Freightliner",-1)]),544),[[C,v.value.make]])])]),s(t(g),{onClick:re,variant:"ghost",size:"sm"},{default:o(()=>[s($,{name:"rotate-ccw",class:"mr-2 h-4 w-4"}),e[28]||(e[28]=i(" Reset "))]),_:1})])]),_:1})]),_:1}),s(t(Y),null,{default:o(()=>[s(t(J),{class:"p-0"},{default:o(()=>[n("div",Fe,[s(t(we),null,{default:o(()=>[s(t(Ce),null,{default:o(()=>[s(t(R),null,{default:o(()=>[y.SuperAdmin?(d(),w(t(W),{key:0},{default:o(()=>e[29]||(e[29]=[i("Company Name")])),_:1})):V("",!0),(d(),p(k,null,L(U,l=>s(t(W),{key:l,class:"cursor-pointer",onClick:u=>oe(l)},{default:o(()=>[n("div",Ne,[l==="inspection_status"?(d(),p(k,{key:0},[i(" Annual Inspection Status ")],64)):l==="inspection_expiry_date"?(d(),p(k,{key:1},[i(" Annual Inspection Expiration Date ")],64)):(d(),p(k,{key:2},[i(c(l.replace(/_/g," ")),1)],64)),M.value===l?(d(),p("div",he,[S.value==="asc"?(d(),p("svg",ze,e[30]||(e[30]=[n("path",{d:"M8 15l4-4 4 4"},null,-1)]))):(d(),p("svg",qe,e[31]||(e[31]=[n("path",{d:"M16 9l-4 4-4-4"},null,-1)])))])):(d(),p("div",je,e[32]||(e[32]=[n("svg",{class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},[n("path",{d:"M8 10l4-4 4 4"}),n("path",{d:"M16 14l-4 4-4-4"})],-1)])))])]),_:2},1032,["onClick"])),64)),s(t(W),null,{default:o(()=>e[33]||(e[33]=[i("Actions")])),_:1})]),_:1})]),_:1}),s(t(Se),null,{default:o(()=>[q.value.length===0?(d(),w(t(R),{key:0},{default:o(()=>[s(t(F),{colspan:y.SuperAdmin?U.length+2:U.length+1,class:"text-center py-8"},{default:o(()=>e[34]||(e[34]=[i(" No trucks found matching your criteria ")])),_:1},8,["colspan"])]),_:1})):V("",!0),(d(!0),p(k,null,L(q.value,l=>(d(),w(t(R),{key:l.id,class:"hover:bg-muted/50"},{default:o(()=>[y.SuperAdmin?(d(),w(t(F),{key:0},{default:o(()=>{var u;return[i(c(((u=l.tenant)==null?void 0:u.name)??"—"),1)]}),_:2},1024)):V("",!0),(d(),p(k,null,L(U,u=>s(t(F),{key:u,class:"whitespace-nowrap"},{default:o(()=>[u==="is_active"?(d(),p("span",{key:0,class:I(l[u]?"text-green-600":"text-red-600")},c(l[u]?"Yes":"No"),3)):u==="is_returned"?(d(),p("span",{key:1,class:I([l[u]?"text-red-600":"text-green-600","px-2 py-1 rounded-full text-xs font-medium"]),style:ke({backgroundColor:l[u]?"rgba(239, 68, 68, 0.1)":"rgba(22, 163, 74, 0.1)"})},c(l[u]?"Returned":"With Company"),7)):u==="inspection_status"?(d(),p("span",{key:2,class:I(l[u]==="good"?"text-green-600":"text-red-600")},c(l[u]==="good"?"Good":"Expired"),3)):u==="inspection_expiry_date"?(d(),p(k,{key:3},[i(c(ve(l[u])),1)],64)):(d(),p(k,{key:4},[i(c(l[u]),1)],64))]),_:2},1024)),64)),s(t(F),null,{default:o(()=>[n("div",Ge,[s(t(g),{onClick:u=>ie(l),variant:"warning",size:"sm"},{default:o(()=>[s($,{name:"pencil",class:"mr-1 h-4 w-4"}),e[35]||(e[35]=i(" Edit "))]),_:2},1032,["onClick"]),s(t(g),{onClick:u=>de(l.id),variant:"destructive",size:"sm"},{default:o(()=>[s($,{name:"trash",class:"mr-1 h-4 w-4"}),e[36]||(e[36]=i(" Delete "))]),_:2},1032,["onClick"])])]),_:2},1024)]),_:2},1024))),128))]),_:1})]),_:1})]),y.entries.links?(d(),p("div",Oe,[n("div",Re,[n("div",We," Showing "+c(q.value.length)+" of "+c(y.entries.data.length)+" entries ",1),n("div",Pe,[(d(!0),p(k,null,L(y.entries.links,l=>(d(),w(t(g),{key:l.label,onClick:u=>me(l.url),disabled:!l.url,variant:"ghost",size:"sm",class:I(["mx-1",{"bg-primary/10 text-primary border-primary":l.active}])},{default:o(()=>[n("span",{innerHTML:l.label},null,8,He)]),_:2},1032,["onClick","disabled","class"]))),128))])])])):V("",!0)]),_:1})]),_:1}),s(t(Q),{open:T.value,"onUpdate:open":e[14]||(e[14]=l=>T.value=l)},{default:o(()=>[s(t(X),{class:"sm:max-w-4xl"},{default:o(()=>[s(t(Z),null,{default:o(()=>[s(t(ee),null,{default:o(()=>[i(c(N.value),1)]),_:1}),s(t(te),null,{default:o(()=>[i(" Fill in the details to "+c(B.value.toLowerCase())+" a truck. ",1)]),_:1})]),_:1}),n("form",{onSubmit:H(ue,["prevent"]),class:"grid grid-cols-1 sm:grid-cols-2 gap-4"},[y.SuperAdmin?(d(),p("div",Ke,[s(t(m),{for:"tenant"},{default:o(()=>e[37]||(e[37]=[i("Company Name")])),_:1}),n("div",Ye,[b(n("select",{id:"tenant","onUpdate:modelValue":e[3]||(e[3]=l=>t(r).tenant_id=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},[e[38]||(e[38]=n("option",{value:""},"Select Company",-1)),(d(!0),p(k,null,L(y.tenants,l=>(d(),p("option",{key:l.id,value:l.id},c(l.name),9,Je))),128))],512),[[C,t(r).tenant_id]]),e[39]||(e[39]=n("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[n("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])])):V("",!0),n("div",null,[s(t(m),{for:"truckid"},{default:o(()=>e[40]||(e[40]=[i("Truck ID")])),_:1}),s(t(E),{id:"truckid",modelValue:t(r).truckid,"onUpdate:modelValue":e[4]||(e[4]=l=>t(r).truckid=l),modelModifiers:{number:!0},type:"number",required:""},null,8,["modelValue"])]),n("div",null,[s(t(m),{for:"type"},{default:o(()=>e[41]||(e[41]=[i("Type")])),_:1}),n("div",Qe,[b(n("select",{id:"type","onUpdate:modelValue":e[5]||(e[5]=l=>t(r).type=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",required:""},e[42]||(e[42]=[n("option",{value:"daycab"},"Daycab",-1),n("option",{value:"sleepercab"},"Sleepercab",-1)]),512),[[C,t(r).type]]),e[43]||(e[43]=n("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[n("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),n("div",null,[s(t(m),{for:"make"},{default:o(()=>e[44]||(e[44]=[i("Make")])),_:1}),n("div",Xe,[b(n("select",{id:"make","onUpdate:modelValue":e[6]||(e[6]=l=>t(r).make=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",required:""},e[45]||(e[45]=[n("option",{value:"international"},"International",-1),n("option",{value:"kenworth"},"Kenworth",-1),n("option",{value:"peterbilt"},"Peterbilt",-1),n("option",{value:"volvo"},"Volvo",-1),n("option",{value:"freightliner"},"Freightliner",-1)]),512),[[C,t(r).make]]),e[46]||(e[46]=n("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[n("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),n("div",null,[s(t(m),{for:"fuel"},{default:o(()=>e[47]||(e[47]=[i("Fuel")])),_:1}),n("div",Ze,[b(n("select",{id:"fuel","onUpdate:modelValue":e[7]||(e[7]=l=>t(r).fuel=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",required:""},e[48]||(e[48]=[n("option",{value:"diesel"},"Diesel",-1),n("option",{value:"cng"},"CNG",-1)]),512),[[C,t(r).fuel]]),e[49]||(e[49]=n("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[n("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),n("div",null,[s(t(m),{for:"license"},{default:o(()=>e[50]||(e[50]=[i("License")])),_:1}),s(t(E),{id:"license",modelValue:t(r).license,"onUpdate:modelValue":e[8]||(e[8]=l=>t(r).license=l),modelModifiers:{number:!0},type:"number",min:"0",required:""},null,8,["modelValue"])]),n("div",null,[s(t(m),{for:"vin"},{default:o(()=>e[51]||(e[51]=[i("VIN")])),_:1}),s(t(E),{id:"vin",modelValue:t(r).vin,"onUpdate:modelValue":e[9]||(e[9]=l=>t(r).vin=l),type:"text",required:""},null,8,["modelValue"])]),n("div",null,[s(t(m),{for:"inspection_status"},{default:o(()=>e[52]||(e[52]=[i("Annual Inspection Status")])),_:1}),n("div",et,[b(n("select",{id:"inspection_status","onUpdate:modelValue":e[10]||(e[10]=l=>t(r).inspection_status=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none",required:""},e[53]||(e[53]=[n("option",{value:"good"},"Good",-1),n("option",{value:"expired"},"Expired",-1)]),512),[[C,t(r).inspection_status]]),e[54]||(e[54]=n("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[n("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])]),n("div",null,[s(t(m),{for:"inspection_expiry_date"},{default:o(()=>e[55]||(e[55]=[i("Annual Inspection Expiry Date")])),_:1}),s(t(E),{id:"inspection_expiry_date",modelValue:t(r).inspection_expiry_date,"onUpdate:modelValue":e[11]||(e[11]=l=>t(r).inspection_expiry_date=l),type:"date",required:""},null,8,["modelValue"])]),n("div",tt,[n("div",nt,[b(n("input",{type:"checkbox",id:"is_active","onUpdate:modelValue":e[12]||(e[12]=l=>t(r).is_active=l),class:"h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"},null,512),[[K,t(r).is_active]]),s(t(m),{for:"is_active"},{default:o(()=>e[56]||(e[56]=[i("Active Status")])),_:1})])]),n("div",lt,[n("div",st,[b(n("input",{type:"checkbox",id:"is_returned","onUpdate:modelValue":e[13]||(e[13]=l=>t(r).is_returned=l),class:"h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-600"},null,512),[[K,t(r).is_returned]]),s(t(m),{for:"is_returned",class:"flex items-center"},{default:o(()=>[e[57]||(e[57]=i(" Returned Status ")),t(r).is_returned?(d(),p("span",ot,"Truck Returned")):(d(),p("span",rt,"With Company"))]),_:1})])]),s(t(ne),{class:"col-span-2 mt-4"},{default:o(()=>[s(t(g),{type:"button",onClick:G,variant:"outline"},{default:o(()=>e[58]||(e[58]=[i(" Cancel ")])),_:1}),s(t(g),{type:"submit",variant:"default"},{default:o(()=>[i(c(B.value),1)]),_:1})]),_:1})],32)]),_:1})]),_:1},8,["open"]),s(t(Q),{open:A.value,"onUpdate:open":e[16]||(e[16]=l=>A.value=l)},{default:o(()=>[s(t(X),null,{default:o(()=>[s(t(Z),null,{default:o(()=>[s(t(ee),null,{default:o(()=>e[59]||(e[59]=[i("Confirm Deletion")])),_:1}),s(t(te),null,{default:o(()=>e[60]||(e[60]=[i(" Are you sure you want to delete this truck? This action cannot be undone. ")])),_:1})]),_:1}),s(t(ne),{class:"mt-4"},{default:o(()=>[s(t(g),{type:"button",onClick:e[15]||(e[15]=l=>A.value=!1),variant:"outline"},{default:o(()=>e[61]||(e[61]=[i(" Cancel ")])),_:1}),s(t(g),{type:"button",onClick:pe,variant:"destructive"},{default:o(()=>e[62]||(e[62]=[i(" Delete ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),n("form",{ref_key:"exportForm",ref:h,method:"GET",class:"hidden"},null,512)])]),_:1},8,["tenantSlug"]))}};export{Vt as default};
