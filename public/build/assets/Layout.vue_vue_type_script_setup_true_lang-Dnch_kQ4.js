import{J as l,a7 as t,N as a,an as s,a6 as m,af as n,a1 as i,a9 as x,aa as y,I as c,P as w,O as _,ae as $,aM as S}from"./vendor-xy97mY0-.js";import{_ as v}from"./Button.vue_vue_type_script_setup_true_lang-BW5qRT-D.js";import{_ as b}from"./Separator.vue_vue_type_script_setup_true_lang-DNKMpxOI.js";import{n as k,P as N}from"./vendor-vue-BRPOKWly.js";const P={class:"mb-0.5 text-base font-medium"},C={key:0,class:"text-sm text-muted-foreground"},U=l({__name:"HeadingSmall",props:{title:{},description:{}},setup(p){return(e,o)=>(a(),t("header",null,[s("h3",P,n(e.title),1),e.description?(a(),t("p",C,n(e.description),1)):m("",!0)]))}}),V={class:"mb-8 space-y-0.5"},z={class:"text-xl font-semibold tracking-tight"},A={key:0,class:"text-sm text-muted-foreground"},B=l({__name:"Heading",props:{title:{},description:{}},setup(p){return(e,o)=>(a(),t("div",V,[s("h2",z,n(e.title),1),e.description?(a(),t("p",A,n(e.description),1)):m("",!0)]))}}),L={class:"px-4 py-6"},T={class:"flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0"},H={class:"w-full max-w-xl lg:w-48"},I={class:"flex flex-col space-x-0 space-y-1"},M={class:"flex-1 md:max-w-2xl"},j={class:"max-w-xl space-y-12"},q=l({__name:"Layout",setup(p){var d;const e=k(),o=e.props.tenantSlug,f=e.props.SuperAdmin,h=[{title:"Profile",href:"/settings/profile"},{title:"Password",href:"/settings/password"},{title:"Appearance",href:"/settings/appearance"},...o&&!f?[{title:"Company",href:`/${o}/settings/tenant`}]:[]],u=(d=e.props.ziggy)!=null&&d.location?new URL(e.props.ziggy.location).pathname:"";return(g,E)=>(a(),t("div",L,[i(B,{title:"Settings",description:"Manage your profile and account settings"}),s("div",T,[s("aside",H,[s("nav",I,[(a(),t(x,null,y(h,r=>i(c(v),{key:r.href,variant:"ghost",class:S(["w-full justify-start",{"bg-muted":c(u)===r.href}]),"as-child":""},{default:_(()=>[i(c(N),{href:r.href},{default:_(()=>[$(n(r.title),1)]),_:2},1032,["href"])]),_:2},1032,["class"])),64))])]),i(c(b),{class:"my-6 md:hidden"}),s("div",M,[s("section",j,[w(g.$slots,"default")])])])]))}});export{q as _,U as a};
