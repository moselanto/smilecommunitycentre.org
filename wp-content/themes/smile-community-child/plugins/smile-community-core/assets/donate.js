(function(){var f=document.querySelector('.smile-give__form');if(!f)return;
var txt=f.querySelector('.smile-give__submit-text'),fee=f.querySelector('.smile-give__fee-text'),feeBase=fee?fee.textContent:'';
function cur(){var c=f.querySelector('input[name=currency]:checked')||f.querySelector('input[name=currency]');return c?c.value:'KES';}
function fmt(a,c){var d=(c==='KES'||Math.abs(a-Math.round(a))<0.005)?0:2;return c+' '+a.toLocaleString('en-US',{minimumFractionDigits:d,maximumFractionDigits:d});}
function update(){var c=cur(),lc=c.toLowerCase(),g=f.querySelector('.smile-give__amounts[data-currency='+c+']');
var r=f.querySelector('input[name=amount_'+lc+']:checked'),a=0;
if(r){a=r.value==='other'?parseFloat((f.querySelector('input[name=custom_'+lc+']')||{}).value||'0'):parseFloat(r.value);}
var rate=g?parseFloat(g.getAttribute('data-fee')||'0'):0,cover=f.querySelector('input[name=cover_fee]'),total=a;
if(a>0&&rate>0&&rate<50){var extra=Math.ceil(a*100/(1-rate/100))/100-a;if(fee)fee.textContent=feeBase+' (+'+fmt(extra,c)+')';if(cover&&cover.checked)total=a+extra;}else if(fee){fee.textContent=feeBase;}
var m=(f.querySelector('input[name=frequency]:checked')||{}).value==='monthly';
if(txt)txt.textContent=a>0?('Donate '+fmt(total,c)+(m?' monthly':'')):'Continue to secure payment';}
f.addEventListener('change',update);f.addEventListener('input',function(e){if(e.target&&/^custom_/.test(e.target.name)){var o=e.target.closest('.smile-give__tile');var r=o&&o.querySelector('input[type=radio]');if(r)r.checked=true;}update();});
var popupOff=false;
function busy(on){var b=f.querySelector('.smile-give__submit');if(b){if(on){b.setAttribute('aria-busy','true');}else{b.removeAttribute('aria-busy');}b.disabled=on;}}
function plain(){popupOff=true;busy(true);HTMLFormElement.prototype.submit.call(f);}
function loadPop(){return new Promise(function(ok,no){if(window.PaystackPop)return ok();var s=document.createElement('script'),t=setTimeout(no,8000);s.src='https://js.paystack.co/v2/inline.js';s.async=true;s.onload=function(){clearTimeout(t);window.PaystackPop?ok():no();};s.onerror=function(){clearTimeout(t);no();};document.head.appendChild(s);});}
f.addEventListener('submit',function(e){if(popupOff||!window.fetch||!window.Promise||!window.FormData){busy(true);return;}
if(f.checkValidity&&!f.checkValidity())return;e.preventDefault();busy(true);
loadPop().then(function(){var fd=new FormData(f);fd.append('smile_popup','1');
return fetch(f.getAttribute('action'),{method:'POST',body:fd,credentials:'same-origin',headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){
if(!d||!d.ok){location.href=(d&&d.redirect)||location.href;return;}
try{new window.PaystackPop().resumeTransaction(d.access_code,{onSuccess:function(){location.href=d.done;},onCancel:function(){location.href=d.cancel;},onError:function(){location.href=d.url;}});busy(false);}
catch(x){location.href=d.url;}},function(){plain();});},function(){plain();});});
update();var d=document.querySelector('.smile-give__done,.smile-give__notice');if(d&&d.focus)d.focus();})();
