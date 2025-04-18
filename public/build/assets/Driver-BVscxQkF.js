import{B as c,A as G,E as De,L as w,N as m,O as n,a1 as a,an as s,I as t,a6 as C,ae as o,af as p,a8 as me,a7 as g,aM as F,a9 as $,aa as L,a4 as Q,aN as Fe,ao as fe}from"./vendor-xy97mY0-.js";import{C as j,m as Ae,W as Y}from"./vendor-vue-BRPOKWly.js";import{_ as Me}from"./AppLayout.vue_vue_type_script_setup_true_lang-D6HeKE5A.js";import{_ as V}from"./Icon.vue_vue_type_script_setup_true_lang-ChfPiT8z.js";import{_ as Ne,a as Te,b as J,c as q,d as Ue,e as P}from"./TableRow.vue_vue_type_script_setup_true_lang-B3yArJuB.js";import{_ as v}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as X}from"./Card.vue_vue_type_script_setup_true_lang-DYwS7A8-.js";import{_ as Ee,a as Be}from"./CardTitle.vue_vue_type_script_setup_true_lang-Wjlz8bZz.js";import{_ as Z}from"./CardContent.vue_vue_type_script_setup_true_lang-DuDvULMo.js";import{_ as ee,a as te,b as ae,c as le,d as ne,e as re}from"./DialogTitle.vue_vue_type_script_setup_true_lang-CL_BLXsN.js";import{_ as k,a as h}from"./Label.vue_vue_type_script_setup_true_lang-BAsbsLwI.js";import{_ as Le,a as Pe,b as ze}from"./AlertDescription.vue_vue_type_script_setup_true_lang-D3Lka1oR.js";import"./vendor-d3-DpcuoaSC.js";import"./vendor-axios-t--hEgTQ.js";import"./vendor-lodash-DCUXXPMP.js";import"./utils-OMMm8ro2.js";import"./vendor-ui-CqtnUFeu.js";import"./app-COgRaFVd.js";import"./vendor-icons-CrF1H9yF.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-evYd1tA8.js";const Re={class:"max-w-7xl mx-auto p-6 space-y-8"},je={class:"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"},qe={class:"flex flex-wrap gap-3"},He={class:"cursor-pointer"},Ke={class:"flex flex-col gap-2"},Ie={class:"flex flex-wrap gap-2"},Oe={key:0,class:"text-sm text-muted-foreground"},We={key:0},Ge={key:1},Qe={key:2},Ye={class:"flex flex-col sm:flex-row justify-between items-end gap-4"},Je={class:"grid grid-cols-1 sm:grid-cols-3 gap-4 w-full"},Xe={class:"overflow-x-auto"},Ze={class:"flex items-center justify-center"},et=["checked"],tt={class:"flex items-center"},at={key:0,class:"ml-2"},lt={key:0,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},nt={key:1,class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},rt={key:1,class:"ml-2 opacity-50"},st=["value"],ot={class:"flex space-x-2"},it={key:0,class:"bg-muted/20 px-4 py-3 border-t"},ut={class:"flex justify-between items-center"},dt={class:"text-sm text-muted-foreground flex items-center gap-4"},mt={class:"flex items-center gap-2"},ft={class:"flex"},vt=["innerHTML"],pt={key:0,class:"col-span-2"},gt={class:"relative"},ct=["value"],yt={class:"sm:col-span-2"},bt={class:"sm:col-span-2"},_t={class:"sm:col-span-2"},Rt={__name:"Driver",props:{entries:Object,tenantSlug:String,SuperAdmin:Boolean,tenants:Array,dateRange:Object,dateFilter:{type:String,default:"full"}},setup(f){const d=f,S=c(d.dateFilter||"full"),A=c(d.perPage||10),b=c([]),M=c(!1),x=c(""),N=c(!1),T=c(!1),H=c("Create Driver"),z=c("Create"),K=c(null),I=c(null),U=c("last_name"),D=c("asc"),y=c({search:"",dateFrom:"",dateTo:""}),ve=[{title:d.tenantSlug?"Dashboard":"Admin Dashboard",href:d.tenantSlug?route("dashboard",{tenantSlug:d.tenantSlug}):route("admin.dashboard")},{title:"Drivers",href:d.tenantSlug?route("driver.index",{tenantSlug:d.tenantSlug}):route("driver.index.admin")}];G(()=>{const r=[{accessorKey:"first_name",header:"First Name",cell:e=>e.getValue(),enableSorting:!0},{accessorKey:"last_name",header:"Last Name",cell:e=>e.getValue(),enableSorting:!0},{accessorKey:"email",header:"Email",cell:e=>e.getValue(),enableSorting:!0},{accessorKey:"mobile_phone",header:"Mobile Phone",cell:e=>e.getValue(),enableSorting:!0},{accessorKey:"hiring_date",header:"Hiring Date",cell:e=>E(e.getValue()),enableSorting:!0},{id:"actions",header:"Actions",cell:e=>({driver:e.row.original,edit:()=>ie(e.row.original),delete:()=>ue(e.row.original.id)}),enableSorting:!1}];return d.SuperAdmin&&r.unshift({accessorKey:"tenant.name",header:"Company Name",cell:e=>{var l;return((l=e.row.original.tenant)==null?void 0:l.name)??"—"},enableSorting:!0}),r});const R=["first_name","last_name","email","mobile_phone","hiring_date"],i=j({id:null,first_name:"",last_name:"",email:"",mobile_phone:"",hiring_date:"",tenant_id:null}),se=j({csv_file:null}),pe=j({}),oe=G(()=>{let r=[...d.entries.data];if(y.value.search){const e=y.value.search.toLowerCase();r=r.filter(l=>{var u,_,de;return((u=l.first_name)==null?void 0:u.toLowerCase().includes(e))||((_=l.last_name)==null?void 0:_.toLowerCase().includes(e))||((de=l.email)==null?void 0:de.toLowerCase().includes(e))})}return y.value.dateFrom&&(r=r.filter(e=>e.hiring_date&&e.hiring_date>=y.value.dateFrom)),y.value.dateTo&&(r=r.filter(e=>e.hiring_date&&e.hiring_date<=y.value.dateTo)),r.sort((e,l)=>{let u=e[U.value],_=l[U.value];return u===null?1:_===null?-1:(typeof u=="string"&&(u=u.toLowerCase(),_=_.toLowerCase()),u<_?D.value==="asc"?-1:1:u>_?D.value==="asc"?1:-1:0)}),r});function ge(r){U.value===r?D.value=D.value==="asc"?"desc":"asc":(U.value=r,D.value="asc")}function O(){}function ce(){y.value={search:"",dateFrom:"",dateTo:""}}function ye(){i.reset(),i.tenant_id=null,H.value="Create Driver",z.value="Create",N.value=!0}function ie(r){i.id=r.id,i.first_name=r.first_name,i.last_name=r.last_name,i.email=r.email,i.mobile_phone=r.mobile_phone,i.hiring_date=r.hiring_date,i.tenant_id=r.tenant_id,H.value="Edit Driver",z.value="Update",N.value=!0}function W(){N.value=!1}function be(){const r={first_name:i.first_name,last_name:i.last_name,email:i.email,mobile_phone:i.mobile_phone,hiring_date:i.hiring_date,tenant_id:i.tenant_id};i.id?i.put(d.SuperAdmin?route("driver.update.admin",[i.id]):route("driver.update",[d.tenantSlug,i.id]),{data:r,onSuccess:()=>{x.value="Driver updated.",W()},onError:()=>alert("Error updating driver")}):i.post(d.SuperAdmin?route("driver.store.admin"):route("driver.store",d.tenantSlug),{data:r,onSuccess:()=>{x.value="Driver created.",W()},onError:()=>alert("Error creating driver")})}function ue(r){I.value=r,T.value=!0}function _e(){pe.delete(d.SuperAdmin?route("driver.destroy.admin",[I.value]):route("driver.destroy",[d.tenantSlug,I.value]),{onSuccess:()=>{x.value="Driver deleted.",T.value=!1}})}function xe(r){var l;const e=(l=r.target.files)==null?void 0:l[0];e&&(se.csv_file=e,se.post(d.SuperAdmin?route("driver.import.admin"):route("driver.import",d.tenantSlug),{forceFormData:!0,onSuccess:()=>x.value="Data imported successfully.",onError:()=>alert("Import failed")}))}function ke(){var e,l;const r=d.SuperAdmin?route("driver.export.admin"):route("driver.export",d.tenantSlug);(e=K.value)==null||e.setAttribute("action",r),(l=K.value)==null||l.submit()}function Se(r){if(r){const e=new URL(r);e.searchParams.set("perPage",A.value),Y.get(e.href,{},{only:["entries"],preserveState:!0})}}De(x,r=>{r&&setTimeout(()=>{x.value=""},5e3)});function E(r){if(!r)return"";const e=r.split("-");if(e.length!==3)return r;const[l,u,_]=e;return`${Number(u)}/${Number(_)}/${l}`}function B(r){S.value=r;const e=d.tenantSlug?route("driver.index",{tenantSlug:d.tenantSlug}):route("driver.index.admin");Y.get(e,{dateFilter:r,perPage:A.value},{preserveState:!0})}function we(){const r=d.tenantSlug?route("driver.index",{tenantSlug:d.tenantSlug}):route("driver.index.admin");Y.get(r,{dateFilter:S.value,perPage:A.value},{preserveState:!0})}const Ce=G(()=>oe.value.length>0&&b.value.length===oe.value.length);function he(r){r.target.checked?b.value=d.entries.data.map(e=>e.id):b.value=[]}function $e(){b.value.length>0&&(M.value=!0)}function Ve(){const r=j({ids:b.value}),e=d.SuperAdmin?"driver.destroyBulk.admin":"driver.destroyBulk",l=d.SuperAdmin?{}:{tenantSlug:d.tenantSlug};r.delete(route(e,l),{preserveScroll:!0,onSuccess:()=>{x.value=`${b.value.length} driver records deleted successfully.`,b.value=[],M.value=!1},onError:u=>{console.error(u)}})}return(r,e)=>(m(),w(Me,{breadcrumbs:ve,tenantSlug:f.tenantSlug},{default:n(()=>[a(t(Ae),{title:"Drivers"}),s("div",Re,[x.value?(m(),w(t(Le),{key:0,variant:"success"},{default:n(()=>[a(t(Pe),null,{default:n(()=>e[23]||(e[23]=[o("Success")])),_:1}),a(t(ze),null,{default:n(()=>[o(p(x.value),1)]),_:1})]),_:1})):C("",!0),s("div",je,[e[27]||(e[27]=s("h1",{class:"text-2xl font-bold text-gray-800 dark:text-gray-200"},"Driver Management",-1)),s("div",qe,[a(t(v),{onClick:ye,variant:"default"},{default:n(()=>[a(V,{name:"plus",class:"mr-2 h-4 w-4"}),e[24]||(e[24]=o(" Create New Driver "))]),_:1}),b.value.length>0?(m(),w(t(v),{key:0,onClick:e[0]||(e[0]=l=>$e()),variant:"destructive"},{default:n(()=>[a(V,{name:"trash",class:"mr-2 h-4 w-4"}),o(" Delete Selected ("+p(b.value.length)+") ",1)]),_:1})):C("",!0),s("label",He,[a(t(v),{variant:"secondary",as:"span"},{default:n(()=>[a(V,{name:"upload",class:"mr-2 h-4 w-4"}),e[25]||(e[25]=o(" Upload CSV "))]),_:1}),s("input",{type:"file",class:"hidden",onChange:xe,accept:".csv, .txt"},null,32)]),a(t(v),{onClick:me(ke,["prevent"]),variant:"outline"},{default:n(()=>[a(V,{name:"download",class:"mr-2 h-4 w-4"}),e[26]||(e[26]=o(" Download CSV "))]),_:1})])]),a(t(X),null,{default:n(()=>[a(t(Z),{class:"p-4"},{default:n(()=>[s("div",Ke,[s("div",Ie,[a(t(v),{onClick:e[1]||(e[1]=l=>B("yesterday")),variant:"outline",size:"sm",class:F({"bg-primary/10 text-primary border-primary":S.value==="yesterday"})},{default:n(()=>e[28]||(e[28]=[o(" Yesterday ")])),_:1},8,["class"]),a(t(v),{onClick:e[2]||(e[2]=l=>B("current-week")),variant:"outline",size:"sm",class:F({"bg-primary/10 text-primary border-primary":S.value==="current-week"})},{default:n(()=>e[29]||(e[29]=[o(" Current Week ")])),_:1},8,["class"]),a(t(v),{onClick:e[3]||(e[3]=l=>B("6w")),variant:"outline",size:"sm",class:F({"bg-primary/10 text-primary border-primary":S.value==="6w"})},{default:n(()=>e[30]||(e[30]=[o(" 6 Weeks ")])),_:1},8,["class"]),a(t(v),{onClick:e[4]||(e[4]=l=>B("quarterly")),variant:"outline",size:"sm",class:F({"bg-primary/10 text-primary border-primary":S.value==="quarterly"})},{default:n(()=>e[31]||(e[31]=[o(" Quarterly ")])),_:1},8,["class"]),a(t(v),{onClick:e[5]||(e[5]=l=>B("full")),variant:"outline",size:"sm",class:F({"bg-primary/10 text-primary border-primary":S.value==="full"})},{default:n(()=>e[32]||(e[32]=[o(" Full ")])),_:1},8,["class"])]),f.dateRange?(m(),g("div",Oe,[S.value==="yesterday"&&f.dateRange.start?(m(),g("span",We," Showing data from "+p(E(f.dateRange.start)),1)):f.dateRange.start&&f.dateRange.end?(m(),g("span",Ge," Showing data from "+p(E(f.dateRange.start))+" to "+p(E(f.dateRange.end)),1)):(m(),g("span",Qe,p(f.dateRange.label),1))])):C("",!0)])]),_:1})]),_:1}),a(t(X),null,{default:n(()=>[a(t(Ee),null,{default:n(()=>[a(t(Be),null,{default:n(()=>e[33]||(e[33]=[o("Filters")])),_:1})]),_:1}),a(t(Z),null,{default:n(()=>[s("div",Ye,[s("div",Je,[s("div",null,[a(t(k),{for:"search"},{default:n(()=>e[34]||(e[34]=[o("Search")])),_:1}),a(t(h),{id:"search",modelValue:y.value.search,"onUpdate:modelValue":e[6]||(e[6]=l=>y.value.search=l),type:"text",placeholder:"Search by name or email...",onInput:O},null,8,["modelValue"])]),s("div",null,[a(t(k),{for:"dateFrom"},{default:n(()=>e[35]||(e[35]=[o("Hiring Date From")])),_:1}),a(t(h),{id:"dateFrom",modelValue:y.value.dateFrom,"onUpdate:modelValue":e[7]||(e[7]=l=>y.value.dateFrom=l),type:"date",onChange:O},null,8,["modelValue"])]),s("div",null,[a(t(k),{for:"dateTo"},{default:n(()=>e[36]||(e[36]=[o("Hiring Date To")])),_:1}),a(t(h),{id:"dateTo",modelValue:y.value.dateTo,"onUpdate:modelValue":e[8]||(e[8]=l=>y.value.dateTo=l),type:"date",onChange:O},null,8,["modelValue"])])]),a(t(v),{onClick:ce,variant:"ghost",size:"sm"},{default:n(()=>[a(V,{name:"rotate_ccw",class:"mr-2 h-4 w-4"}),e[37]||(e[37]=o(" Reset "))]),_:1})])]),_:1})]),_:1}),a(t(X),null,{default:n(()=>[a(t(Z),{class:"p-0"},{default:n(()=>[s("div",Xe,[a(t(Ne),{class:"relative h-[600px] overflow-auto"},{default:n(()=>[a(t(Te),null,{default:n(()=>[a(t(J),{class:"sticky top-0 bg-background border-b z-10 hover:bg-background"},{default:n(()=>[a(t(q),{class:"w-[50px]"},{default:n(()=>[s("div",Ze,[s("input",{type:"checkbox",onChange:he,checked:Ce.value,class:"h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"},null,40,et)])]),_:1}),f.SuperAdmin?(m(),w(t(q),{key:0},{default:n(()=>e[38]||(e[38]=[o("Company Name")])),_:1})):C("",!0),(m(),g($,null,L(R,l=>a(t(q),{key:l,class:"cursor-pointer",onClick:u=>ge(l)},{default:n(()=>[s("div",tt,[o(p(l.replace(/_/g," ").split(" ").map(u=>u.charAt(0).toUpperCase()+u.slice(1)).join(" "))+" ",1),U.value===l?(m(),g("div",at,[D.value==="asc"?(m(),g("svg",lt,e[39]||(e[39]=[s("path",{d:"M8 15l4-4 4 4"},null,-1)]))):(m(),g("svg",nt,e[40]||(e[40]=[s("path",{d:"M16 9l-4 4-4-4"},null,-1)])))])):(m(),g("div",rt,e[41]||(e[41]=[s("svg",{class:"h-4 w-4",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2"},[s("path",{d:"M8 10l4-4 4 4"}),s("path",{d:"M16 14l-4 4-4-4"})],-1)])))])]),_:2},1032,["onClick"])),64)),a(t(q),null,{default:n(()=>e[42]||(e[42]=[o("Actions")])),_:1})]),_:1})]),_:1}),a(t(Ue),null,{default:n(()=>[f.entries.data.length===0?(m(),w(t(J),{key:0},{default:n(()=>[a(t(P),{colspan:f.SuperAdmin?R.length+3:R.length+2,class:"text-center py-8"},{default:n(()=>e[43]||(e[43]=[o(" No driver records found matching your criteria ")])),_:1},8,["colspan"])]),_:1})):C("",!0),(m(!0),g($,null,L(f.entries.data,l=>(m(),w(t(J),{key:l.id},{default:n(()=>[a(t(P),{class:"text-center"},{default:n(()=>[Q(s("input",{type:"checkbox",value:l.id,"onUpdate:modelValue":e[9]||(e[9]=u=>b.value=u),class:"h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"},null,8,st),[[Fe,b.value]])]),_:2},1024),f.SuperAdmin?(m(),w(t(P),{key:0},{default:n(()=>{var u;return[o(p(((u=l.tenant)==null?void 0:u.name)??"—"),1)]}),_:2},1024)):C("",!0),(m(),g($,null,L(R,u=>a(t(P),{key:u},{default:n(()=>[u==="hiring_date"?(m(),g($,{key:0},[o(p(E(l[u])),1)],64)):(m(),g($,{key:1},[o(p(l[u]),1)],64))]),_:2},1024)),64)),a(t(P),null,{default:n(()=>[s("div",ot,[a(t(v),{onClick:u=>ie(l),variant:"warning",size:"sm"},{default:n(()=>[a(V,{name:"pencil",class:"mr-1 h-4 w-4"}),e[44]||(e[44]=o(" Edit "))]),_:2},1032,["onClick"]),a(t(v),{onClick:u=>ue(l.id),variant:"destructive",size:"sm"},{default:n(()=>[a(V,{name:"trash",class:"mr-1 h-4 w-4"}),e[45]||(e[45]=o(" Delete "))]),_:2},1032,["onClick"])])]),_:2},1024)]),_:2},1024))),128))]),_:1})]),_:1})]),f.entries.links?(m(),g("div",it,[s("div",ut,[s("div",dt,[s("span",null,"Showing "+p(f.entries.from)+" to "+p(f.entries.to)+" of "+p(f.entries.total)+" entries",1),s("div",mt,[e[47]||(e[47]=s("span",{class:"text-sm"},"Show:",-1)),Q(s("select",{id:"perPage","onUpdate:modelValue":e[10]||(e[10]=l=>A.value=l),onChange:we,class:"h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"},e[46]||(e[46]=[s("option",{value:"10"},"10",-1),s("option",{value:"25"},"25",-1),s("option",{value:"50"},"50",-1),s("option",{value:"100"},"100",-1)]),544),[[fe,A.value]])])]),s("div",ft,[(m(!0),g($,null,L(f.entries.links,l=>(m(),w(t(v),{key:l.label,onClick:u=>Se(l.url),disabled:!l.url,variant:"ghost",size:"sm",class:F(["mx-1",{"bg-primary/10 text-primary border-primary":l.active}])},{default:n(()=>[s("span",{innerHTML:l.label},null,8,vt)]),_:2},1032,["onClick","disabled","class"]))),128))])])])):C("",!0)]),_:1})]),_:1}),a(t(ee),{open:N.value,"onUpdate:open":e[17]||(e[17]=l=>N.value=l)},{default:n(()=>[a(t(te),{class:"sm:max-w-4xl"},{default:n(()=>[a(t(ae),null,{default:n(()=>[a(t(le),null,{default:n(()=>[o(p(H.value),1)]),_:1}),a(t(ne),null,{default:n(()=>[o(" Fill in the details to "+p(z.value.toLowerCase())+" a driver. ",1)]),_:1})]),_:1}),s("form",{onSubmit:me(be,["prevent"]),class:"grid grid-cols-1 sm:grid-cols-2 gap-4"},[f.SuperAdmin?(m(),g("div",pt,[a(t(k),{for:"tenant"},{default:n(()=>e[48]||(e[48]=[o("Company")])),_:1}),s("div",gt,[Q(s("select",{id:"tenant","onUpdate:modelValue":e[11]||(e[11]=l=>t(i).tenant_id=l),class:"flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"},[e[49]||(e[49]=s("option",{value:""},"Select Company",-1)),(m(!0),g($,null,L(f.tenants,l=>(m(),g("option",{key:l.id,value:l.id},p(l.name),9,ct))),128))],512),[[fe,t(i).tenant_id]]),e[50]||(e[50]=s("div",{class:"pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"},[s("svg",{class:"h-4 w-4 opacity-50",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor"},[s("path",{"fill-rule":"evenodd",d:"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z","clip-rule":"evenodd"})])],-1))])])):C("",!0),s("div",null,[a(t(k),{for:"first_name"},{default:n(()=>e[51]||(e[51]=[o("First Name")])),_:1}),a(t(h),{id:"first_name",modelValue:t(i).first_name,"onUpdate:modelValue":e[12]||(e[12]=l=>t(i).first_name=l),type:"text",required:""},null,8,["modelValue"])]),s("div",null,[a(t(k),{for:"last_name"},{default:n(()=>e[52]||(e[52]=[o("Last Name")])),_:1}),a(t(h),{id:"last_name",modelValue:t(i).last_name,"onUpdate:modelValue":e[13]||(e[13]=l=>t(i).last_name=l),type:"text",required:""},null,8,["modelValue"])]),s("div",yt,[a(t(k),{for:"email"},{default:n(()=>e[53]||(e[53]=[o("Email Address")])),_:1}),a(t(h),{id:"email",modelValue:t(i).email,"onUpdate:modelValue":e[14]||(e[14]=l=>t(i).email=l),type:"email",required:""},null,8,["modelValue"])]),s("div",bt,[a(t(k),{for:"mobile_phone"},{default:n(()=>e[54]||(e[54]=[o("Mobile Phone Number")])),_:1}),a(t(h),{id:"mobile_phone",modelValue:t(i).mobile_phone,"onUpdate:modelValue":e[15]||(e[15]=l=>t(i).mobile_phone=l),type:"text",required:""},null,8,["modelValue"])]),s("div",_t,[a(t(k),{for:"hiring_date"},{default:n(()=>e[55]||(e[55]=[o("Hiring Date")])),_:1}),a(t(h),{id:"hiring_date",modelValue:t(i).hiring_date,"onUpdate:modelValue":e[16]||(e[16]=l=>t(i).hiring_date=l),type:"date",required:""},null,8,["modelValue"])]),a(t(re),{class:"col-span-2 mt-4"},{default:n(()=>[a(t(v),{type:"button",onClick:W,variant:"outline"},{default:n(()=>e[56]||(e[56]=[o(" Cancel ")])),_:1}),a(t(v),{type:"submit",variant:"default"},{default:n(()=>[o(p(z.value),1)]),_:1})]),_:1})],32)]),_:1})]),_:1},8,["open"]),a(t(ee),{open:T.value,"onUpdate:open":e[19]||(e[19]=l=>T.value=l)},{default:n(()=>[a(t(te),null,{default:n(()=>[a(t(ae),null,{default:n(()=>[a(t(le),null,{default:n(()=>e[57]||(e[57]=[o("Confirm Deletion")])),_:1}),a(t(ne),null,{default:n(()=>e[58]||(e[58]=[o(" Are you sure you want to delete this driver? This action cannot be undone. ")])),_:1})]),_:1}),a(t(re),{class:"mt-4"},{default:n(()=>[a(t(v),{type:"button",onClick:e[18]||(e[18]=l=>T.value=!1),variant:"outline"},{default:n(()=>e[59]||(e[59]=[o(" Cancel ")])),_:1}),a(t(v),{type:"button",onClick:_e,variant:"destructive"},{default:n(()=>e[60]||(e[60]=[o(" Delete ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),a(t(ee),{open:M.value,"onUpdate:open":e[22]||(e[22]=l=>M.value=l)},{default:n(()=>[a(t(te),null,{default:n(()=>[a(t(ae),null,{default:n(()=>[a(t(le),null,{default:n(()=>e[61]||(e[61]=[o("Confirm Bulk Deletion")])),_:1}),a(t(ne),null,{default:n(()=>[o(" Are you sure you want to delete "+p(b.value.length)+" driver records? This action cannot be undone. ",1)]),_:1})]),_:1}),a(t(re),{class:"mt-4"},{default:n(()=>[a(t(v),{type:"button",onClick:e[20]||(e[20]=l=>M.value=!1),variant:"outline"},{default:n(()=>e[62]||(e[62]=[o(" Cancel ")])),_:1}),a(t(v),{type:"button",onClick:e[21]||(e[21]=l=>Ve()),variant:"destructive"},{default:n(()=>e[63]||(e[63]=[o(" Delete Selected ")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]),s("form",{ref_key:"exportForm",ref:K,method:"GET",class:"hidden"},null,512)])]),_:1},8,["tenantSlug"]))}};export{Rt as default};
