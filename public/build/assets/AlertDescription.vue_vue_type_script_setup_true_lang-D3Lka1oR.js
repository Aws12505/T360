import{c as a}from"./utils-OMMm8ro2.js";import{J as n,a7 as o,N as l,P as c,aM as d,I as u}from"./vendor-xy97mY0-.js";const v=n({__name:"Alert",props:{class:{},variant:{default:"default"}},setup(e){const t=e,s={default:"bg-background text-foreground",destructive:"border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive",success:"border-green-500/50 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 [&>svg]:text-green-600"};return(r,i)=>(l(),o("div",{class:d(u(a)("relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground",s[r.variant],t.class)),role:"alert"},[c(r.$slots,"default")],2))}}),f=n({__name:"AlertTitle",props:{class:{}},setup(e){const t=e;return(s,r)=>(l(),o("h5",{class:d(u(a)("mb-1 font-medium leading-none tracking-tight",t.class))},[c(s.$slots,"default")],2))}}),m=n({__name:"AlertDescription",props:{class:{}},setup(e){const t=e;return(s,r)=>(l(),o("div",{class:d(u(a)("text-sm [&_p]:leading-relaxed",t.class))},[c(s.$slots,"default")],2))}});export{v as _,f as a,m as b};
