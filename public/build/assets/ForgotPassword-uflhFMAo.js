import{J as u,L as n,N as m,O as r,a1 as o,a7 as _,a6 as d,an as a,I as s,af as c,a8 as g,ae as l}from"./vendor-xy97mY0-.js";import{_ as w}from"./InputError.vue_vue_type_script_setup_true_lang-BJZvQ1On.js";import{_ as x}from"./TextLink.vue_vue_type_script_setup_true_lang-fst2-rte.js";import{_ as y}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as k,a as v}from"./Label.vue_vue_type_script_setup_true_lang-BAsbsLwI.js";import{_ as V}from"./AuthLayout.vue_vue_type_script_setup_true_lang-C17gQ0-g.js";import{C as $,m as b}from"./vendor-vue-BRPOKWly.js";import{L as C}from"./vendor-icons-CrF1H9yF.js";import"./vendor-d3-DpcuoaSC.js";import"./utils-OMMm8ro2.js";import"./vendor-ui-CqtnUFeu.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-evYd1tA8.js";import"./vendor-axios-t--hEgTQ.js";import"./vendor-lodash-DCUXXPMP.js";const N={key:0,class:"mb-4 text-center text-sm font-medium text-green-600"},B={class:"space-y-6"},E={class:"grid gap-2"},F={class:"my-6 flex items-center justify-start"},L={class:"space-x-1 text-center text-sm text-muted-foreground"},H=u({__name:"ForgotPassword",props:{status:{}},setup(h){const t=$({email:""}),p=()=>{t.post(route("password.email"))};return(i,e)=>(m(),n(V,{title:"Forgot password",description:"Enter your email to receive a password reset link"},{default:r(()=>[o(s(b),{title:"Forgot password"}),i.status?(m(),_("div",N,c(i.status),1)):d("",!0),a("div",B,[a("form",{onSubmit:g(p,["prevent"])},[a("div",E,[o(s(k),{for:"email"},{default:r(()=>e[1]||(e[1]=[l("Email address")])),_:1}),o(s(v),{id:"email",type:"email",name:"email",autocomplete:"off",modelValue:s(t).email,"onUpdate:modelValue":e[0]||(e[0]=f=>s(t).email=f),autofocus:"",placeholder:"email@example.com"},null,8,["modelValue"]),o(w,{message:s(t).errors.email},null,8,["message"])]),a("div",F,[o(s(y),{class:"w-full",disabled:s(t).processing},{default:r(()=>[s(t).processing?(m(),n(s(C),{key:0,class:"h-4 w-4 animate-spin"})):d("",!0),e[2]||(e[2]=l(" Email password reset link "))]),_:1},8,["disabled"])])],32),a("div",L,[e[4]||(e[4]=a("span",null,"Or, return to",-1)),o(x,{href:i.route("login")},{default:r(()=>e[3]||(e[3]=[l("log in")])),_:1},8,["href"])])])]),_:1}))}});export{H as default};
