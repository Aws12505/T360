import{d as a,a as n,o,q as d,n as i}from"./app-B__ptHTF.js";const c=a({__name:"Badge",props:{variant:{type:String,default:"default",validator:e=>["default","secondary","destructive","outline"].includes(e)},size:{type:String,default:"default",validator:e=>["default","sm","lg"].includes(e)},className:{type:String,default:""}},setup(e){const t={default:"border-transparent bg-primary text-primary-foreground hover:bg-primary/80",secondary:"border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",destructive:"border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80",outline:"text-foreground"},r={default:"text-xs",sm:"text-xs",lg:"text-sm"};return(s,l)=>(o(),n("div",{class:i(["inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",t[e.variant],r[e.size],e.className])},[d(s.$slots,"default")],2))}});export{c as _};
