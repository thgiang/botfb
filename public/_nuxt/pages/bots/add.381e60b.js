(window.webpackJsonp=window.webpackJsonp||[]).push([[2],{209:function(t,e,o){"use strict";o.r(e);var n={data:function(){return{sendData:{cookie:"",proxy:"",frequency:3}}},methods:{save:function(){this.$axios.post("/api/bots/new",this.sendData).then((function(t){"success"===t.data.status?alert("Lưu thành công"):alert(t.data.message)})).catch((function(t){console.log(t),alert("Lỗi khi lưu")}))}}},r=o(43),component=Object(r.a)(n,(function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("div",{staticClass:"container"},[o("form",{on:{submit:function(e){return e.preventDefault(),t.save(e)}}},[o("div",{staticClass:"form-group"},[o("label",{attrs:{for:"cookie"}},[t._v("Cookie")]),t._v(" "),o("input",{directives:[{name:"model",rawName:"v-model",value:t.sendData.cookie,expression:"sendData.cookie"}],staticClass:"form-control",attrs:{id:"cookie",type:"text",placeholder:"Nhập cookie"},domProps:{value:t.sendData.cookie},on:{input:function(e){e.target.composing||t.$set(t.sendData,"cookie",e.target.value)}}})]),t._v(" "),o("div",{staticClass:"form-group"},[o("label",{attrs:{for:"proxy"}},[t._v("Cookie")]),t._v(" "),o("input",{directives:[{name:"model",rawName:"v-model",value:t.sendData.proxy,expression:"sendData.proxy"}],staticClass:"form-control",attrs:{id:"proxy",type:"text",placeholder:"Nhập proxy"},domProps:{value:t.sendData.proxy},on:{input:function(e){e.target.composing||t.$set(t.sendData,"proxy",e.target.value)}}})]),t._v(" "),o("div",{staticClass:"form-group"},[o("label",{attrs:{for:"frequency"}},[t._v("Giãn cách thời gian mỗi comment")]),t._v(" "),o("select",{directives:[{name:"model",rawName:"v-model",value:t.sendData.frequency,expression:"sendData.frequency"}],staticClass:"form-control",attrs:{id:"frequency"},on:{change:function(e){var o=Array.prototype.filter.call(e.target.options,(function(t){return t.selected})).map((function(t){return"_value"in t?t._value:t.value}));t.$set(t.sendData,"frequency",e.target.multiple?o:o[0])}}},[o("option",{attrs:{value:"1"}},[t._v("\n          1 phút\n        ")]),t._v(" "),o("option",{attrs:{value:"3"}},[t._v("\n          3 phút\n        ")]),t._v(" "),o("option",{attrs:{value:"5"}},[t._v("\n          5 phút\n        ")]),t._v(" "),o("option",{attrs:{value:"10"}},[t._v("\n          10 phút\n        ")]),t._v(" "),o("option",{attrs:{value:"30"}},[t._v("\n          30 phút\n        ")])])]),t._v(" "),o("button",{staticClass:"btn btn-success",attrs:{type:"submit"}},[t._v("\n      Lưu\n    ")])])])}),[],!1,null,null,null);e.default=component.exports}}]);
