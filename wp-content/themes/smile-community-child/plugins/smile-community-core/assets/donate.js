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
f.addEventListener('submit',function(){var b=f.querySelector('.smile-give__submit');if(b){b.setAttribute('aria-busy','true');b.disabled=true;}});
update();var d=document.querySelector('.smile-give__done,.smile-give__notice');if(d&&d.focus)d.focus();})();
