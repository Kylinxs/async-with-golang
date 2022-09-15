function e(e,t){const n=Object.create(null),o=e.split(",");for(let r=0;r<o.length;r++)n[o[r]]=!0;return t?e=>!!n[e.toLowerCase()]:e=>!!n[e]}const t=e("Infinity,undefined,NaN,isFinite,isNaN,parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,BigInt"),n=e("itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly");function o(e){return!!e||""===e}function r(e){if(E(e)){const t={};for(let n=0;n<e.length;n++){const o=e[n],s=A(o)?l(o):r(o);if(s)for(const e in s)t[e]=s[e]}return t}return A(e)||M(e)?e:void 0}const s=/;(?![^(]*\))/g,i=/:(.+)/;function l(e){const t={};return e.split(s).forEach((e=>{if(e){const n=e.split(i);n.length>1&&(t[n[0].trim()]=n[1].trim())}})),t}function c(e){let t="";if(A(e))t=e;else if(E(e))for(let n=0;n<e.length;n++){const o=c(e[n]);o&&(t+=o+" ")}else if(M(e))for(const n in e)e[n]&&(t+=n+" ");return t.trim()}function a(e){if(!e)return null;let{class:t,style:n}=e;return t&&!A(t)&&(e.class=c(t)),n&&(e.style=r(n)),e}const u=e("html,body,base,head,link,meta,style,title,address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,nav,section,div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,ruby,s,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,embed,object,param,source,canvas,script,noscript,del,ins,caption,col,colgroup,table,thead,tbody,td,th,tr,button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,output,progress,select,textarea,details,dialog,menu,summary,template,blockquote,iframe,tfoot"),p=e("svg,animate,animateMotion,animateTransform,circle,clipPath,color-profile,defs,desc,discard,ellipse,feBlend,feColorMatrix,feComponentTransfer,feComposite,feConvolveMatrix,feDiffuseLighting,feDisplacementMap,feDistanceLight,feDropShadow,feFlood,feFuncA,feFuncB,feFuncG,feFuncR,feGaussianBlur,feImage,feMerge,feMergeNode,feMorphology,feOffset,fePointLight,feSpecularLighting,feSpotLight,feTile,feTurbulence,filter,foreignObject,g,hatch,hatchpath,image,line,linearGradient,marker,mask,mesh,meshgradient,meshpatch,meshrow,metadata,mpath,path,pattern,polygon,polyline,radialGradient,rect,set,solidcolor,stop,switch,symbol,text,textPath,title,tspan,unknown,use,view"),f=e("area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr");function d(e,t){if(e===t)return!0;let n=R(e),o=R(t);if(n||o)return!(!n||!o)&&e.getTime()===t.getTime();if(n=P(e),o=P(t),n||o)return e===t;if(n=E(e),o=E(t),n||o)return!(!n||!o)&&function(e,t){if(e.length!==t.length)return!1;let n=!0;for(let o=0;n&&o<e.length;o++)n=d(e[o],t[o]);return n}(e,t);if(n=M(e),o=M(t),n||o){if(!n||!o)return!1;if(Object.keys(e).length!==Object.keys(t).length)return!1;for(const n in e){const o=e.hasOwnProperty(n),r=t.hasOwnProperty(n);if(o&&!r||!o&&r||!d(e[n],t[n]))return!1}}return String(e)===String(t)}function h(e,t){return e.findIndex((e=>d(e,t)))}const m=e=>A(e)?e:null==e?"":E(e)||M(e)&&(e.toString===I||!F(e.toString))?JSON.stringify(e,g,2):String(e),g=(e,t)=>t&&t.__v_isRef?g(e,t.value):$(t)?{[`Map(${t.size})`]:[...t.entries()].reduce(((e,[t,n])=>(e[`${t} =>`]=n,e)),{})}:O(t)?{[`Set(${t.size})`]:[...t.values()]}:!M(t)||E(t)||L(t)?t:String(t),v={},y=[],_=()=>{},b=()=>!1,S=/^on[^a-z]/,x=e=>S.test(e),C=e=>e.startsWith("onUpdate:"),k=Object.assign,w=(e,t)=>{const n=e.indexOf(t);n>-1&&e.splice(n,1)},T=Object.prototype.hasOwnProperty,N=(e,t)=>T.call(e,t),E=Array.isArray,$=e=>"[object Map]"===B(e),O=e=>"[object Set]"===B(e),R=e=>"[object Date]"===B(e),F=e=>"function"==typeof e,A=e=>"string"==typeof e,P=e=>"symbol"==typeof e,M=e=>null!==e&&"object"==typeof e,V=e=>M(e)&&F(e.then)&&F(e.catch),I=Object.prototype.toString,B=e=>I.call(e),L=e=>"[object Object]"===B(e),j=e=>A(e)&&"NaN"!==e&&"-"!==e[0]&&""+parseInt(e,10)===e,U=e(",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"),D=e("bind,cloak,else-if,else,for,html,if,model,on,once,pre,show,slot,text,memo"),H=e=>{const t=Object.create(null);return n=>t[n]||(t[n]=e(n))},W=/-(\w)/g,z=H((e=>e.replace(W,((e,t)=>t?t.toUpperCase():"")))),K=/\B([A-Z])