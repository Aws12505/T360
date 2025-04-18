import{J as l,L as i,N as m,O as t,a1 as r,an as a,I as s,a8 as f,ae as n,a6 as c}from"./vendor-xy97mY0-.js";import{_ as u}from"./InputError.vue_vue_type_script_setup_true_lang-BJZvQ1On.js";import{_}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as w,a as C}from"./Label.vue_vue_type_script_setup_true_lang-BAsbsLwI.js";import{_ as g}from"./AuthLayout.vue_vue_type_script_setup_true_lang-C17gQ0-g.js";import{C as V,m as b}from"./vendor-vue-BRPOKWly.js";import{L as h}from"./vendor-icons-CrF1H9yF.js";import"./vendor-d3-DpcuoaSC.js";import"./utils-OMMm8ro2.js";import"./vendor-ui-CqtnUFeu.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-evYd1tA8.js";import"./vendor-axios-t--hEgTQ.js";import"./vendor-lodash-DCUXXPMP.js";const x={class:"space-y-6"},y={class:"grid gap-2"},N={class:"flex items-center"},j=l({__name:"ConfirmPassword",setup($){const o=V({password:""}),d=()=>{o.post(route("password.confirm"),{onFinish:()=>{o.reset()}})};return(k,e)=>(m(),i(g,{title:"Confirm your password",description:"This is a secure area of the application. Please confirm your password before continuing."},{default:t(()=>[r(s(b),{title:"Confirm password"}),a("form",{onSubmit:f(d,["prevent"])},[a("div",x,[a("div",y,[r(s(w),{htmlFor:"password"},{default:t(()=>e[1]||(e[1]=[n("Password")])),_:1}),r(s(C),{id:"password",type:"password",class:"mt-1 block w-full",modelValue:s(o).password,"onUpdate:modelValue":e[0]||(e[0]=p=>s(o).password=p),required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),r(u,{message:s(o).errors.password},null,8,["message"])]),a("div",N,[r(s(_),{class:"w-full",disabled:s(o).processing},{default:t(()=>[s(o).processing?(m(),i(s(h),{key:0,class:"h-4 w-4 animate-spin"})):c("",!0),e[2]||(e[2]=n(" Confirm Password "))]),_:1},8,["disabled"])])])],32)]),_:1}))}});export{j as default};
