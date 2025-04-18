import{J as S,A as b,B as w,L as y,N as V,O as r,a1 as o,I as s,an as t,a8 as I,ae as l,aS as k,a4 as x,a5 as C}from"./vendor-xy97mY0-.js";import{_ as m}from"./InputError.vue_vue_type_script_setup_true_lang-BJZvQ1On.js";import{_ as $}from"./AppLayout.vue_vue_type_script_setup_true_lang-D6HeKE5A.js";import{_ as N,a as h}from"./Layout.vue_vue_type_script_setup_true_lang-Dnch_kQ4.js";import{n as P,C as B,m as E}from"./vendor-vue-BRPOKWly.js";import{_ as T}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as c,a as f}from"./Label.vue_vue_type_script_setup_true_lang-BAsbsLwI.js";import"./vendor-d3-DpcuoaSC.js";import"./utils-OMMm8ro2.js";import"./vendor-ui-CqtnUFeu.js";import"./Icon.vue_vue_type_script_setup_true_lang-ChfPiT8z.js";import"./vendor-icons-CrF1H9yF.js";import"./app-COgRaFVd.js";import"./vendor-axios-t--hEgTQ.js";import"./vendor-lodash-DCUXXPMP.js";import"./AppLogoIcon.vue_vue_type_script_setup_true_lang-evYd1tA8.js";import"./Separator.vue_vue_type_script_setup_true_lang-DNKMpxOI.js";const U={class:"space-y-6"},D={class:"grid gap-2"},L={class:"grid gap-2"},M={class:"grid gap-2"},A={class:"flex items-center gap-4"},H={class:"text-sm text-neutral-600"},os=S({__name:"Password",props:{tenantSlug:{default:void 0}},setup(_){P();const n=_,g=b(()=>[{title:n.tenantSlug?"Dashboard":"Admin Dashboard",href:n.tenantSlug?route("dashboard",{tenantSlug:n.tenantSlug}):route("admin.dashboard")}]),p=w(null),i=w(null),a=B({current_password:"",password:"",password_confirmation:""}),v=()=>{a.put(route("password.update",n.tenantSlug?{tenantSlug:n.tenantSlug}:{}),{preserveScroll:!0,onSuccess:()=>a.reset(),onError:u=>{u.password&&(a.reset("password","password_confirmation"),p.value instanceof HTMLInputElement&&p.value.focus()),u.current_password&&(a.reset("current_password"),i.value instanceof HTMLInputElement&&i.value.focus())}})};return(u,e)=>(V(),y($,{breadcrumbs:g.value,tenantSlug:n.tenantSlug},{default:r(()=>[o(s(E),{title:"Password settings"}),o(N,null,{default:r(()=>[t("div",U,[o(h,{title:"Update password",description:"Ensure your account is using a long, random password to stay secure"}),t("form",{onSubmit:I(v,["prevent"]),class:"space-y-6"},[t("div",D,[o(s(c),{for:"current_password"},{default:r(()=>e[3]||(e[3]=[l("Current password")])),_:1}),o(s(f),{id:"current_password",ref_key:"currentPasswordInput",ref:i,modelValue:s(a).current_password,"onUpdate:modelValue":e[0]||(e[0]=d=>s(a).current_password=d),type:"password",class:"mt-1 block w-full",autocomplete:"current-password",placeholder:"Current password"},null,8,["modelValue"]),o(m,{message:s(a).errors.current_password},null,8,["message"])]),t("div",L,[o(s(c),{for:"password"},{default:r(()=>e[4]||(e[4]=[l("New password")])),_:1}),o(s(f),{id:"password",ref_key:"passwordInput",ref:p,modelValue:s(a).password,"onUpdate:modelValue":e[1]||(e[1]=d=>s(a).password=d),type:"password",class:"mt-1 block w-full",autocomplete:"new-password",placeholder:"New password"},null,8,["modelValue"]),o(m,{message:s(a).errors.password},null,8,["message"])]),t("div",M,[o(s(c),{for:"password_confirmation"},{default:r(()=>e[5]||(e[5]=[l("Confirm password")])),_:1}),o(s(f),{id:"password_confirmation",modelValue:s(a).password_confirmation,"onUpdate:modelValue":e[2]||(e[2]=d=>s(a).password_confirmation=d),type:"password",class:"mt-1 block w-full",autocomplete:"new-password",placeholder:"Confirm password"},null,8,["modelValue"]),o(m,{message:s(a).errors.password_confirmation},null,8,["message"])]),t("div",A,[o(s(T),{disabled:s(a).processing},{default:r(()=>e[6]||(e[6]=[l("Save password")])),_:1},8,["disabled"]),o(k,{"enter-active-class":"transition ease-in-out","enter-from-class":"opacity-0","leave-active-class":"transition ease-in-out","leave-to-class":"opacity-0"},{default:r(()=>[x(t("p",H,"Saved.",512),[[C,s(a).recentlySuccessful]])]),_:1})])],32)])]),_:1})]),_:1},8,["breadcrumbs","tenantSlug"]))}});export{os as default};
