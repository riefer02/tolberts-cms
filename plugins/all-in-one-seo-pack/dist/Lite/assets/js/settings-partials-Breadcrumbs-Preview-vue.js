(window["aioseopjsonp"]=window["aioseopjsonp"]||[]).push([["settings-partials-Breadcrumbs-Preview-vue"],{c468:function(e,a,t){"use strict";t.r(a);var r=function(){var e=this,a=e.$createElement,t=e._self._c||a;return t("div",{staticClass:"preview-box"},[e.label?t("span",{staticClass:"label"},[e._v(" "+e._s(e.label)+": ")]):e._e(),e._l(this.getPreviewData(),(function(a,r){return[1<e.previewLength&&r>0&&r<e.previewLength?t("span",{key:r+"sep",staticClass:"aioseo-breadcrumb-separator",domProps:{innerHTML:e._s(e.options.breadcrumbs.separator)}}):e._e(),r<e.previewLength-1?t("span",{key:r+"crumb",class:{"aioseo-breadcrumb":!a.match(/aioseo-breadcrumb/),link:a!==e.options.breadcrumbs.breadcrumbPrefix&&!a.match(/<a /)},domProps:{innerHTML:e._s(a)}}):e._e(),r===e.previewLength-1?t("span",{key:r+"crumbLast",class:{last:!0,link:e.options.breadcrumbs.linkCurrentItem&&e.useDefaultTemplate&&!a.match(/<a /),noLink:!e.options.breadcrumbs.linkCurrentItem&&e.useDefaultTemplate,"aioseo-breadcrumb":!a.match(/aioseo-breadcrumb/)},domProps:{innerHTML:e._s(a)}}):e._e()]}))],2)},s=[],n=t("5530"),i=(t("d81d"),t("4de4"),t("ac1f"),t("5319"),t("fb6a"),t("2f62")),o={props:{previewData:{type:Array,default:null},useDefaultTemplate:{type:Boolean,default:!0},label:String},computed:Object(n["a"])(Object(n["a"])({},Object(i["e"])(["options"])),{},{previewLength:function(){return this.getPreviewData()?this.getPreviewData().length:0}}),methods:{getPreviewData:function(){var e=this,a=this.previewData.filter((function(e){return!!e})).map((function(a){return e.$tags.decodeHTMLEntities(a).replace(/#breadcrumb_separator/g,'<span class="aioseo-breadcrumb-separator">'+e.options.breadcrumbs.separator+"</span>").replace(/#breadcrumb_link/g,"Permalink")}));return this.useDefaultTemplate&&!this.options.breadcrumbs.showCurrentItem&&(a=a.slice(0,a.length-1)),a}}},u=o,l=t("2877"),c=Object(l["a"])(u,r,s,!1,null,null,null);a["default"]=c.exports}}]);