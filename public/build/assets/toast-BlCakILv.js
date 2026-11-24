class h{constructor(){this.container=document.getElementById("toast-container"),this.toasts=[],this.maxToasts=3,this.recentToasts=new Map}show(t,i="info",o="",n=5e3){this.container||(this.container=document.getElementById("toast-container"),this.container||(this.container=document.createElement("div"),this.container.id="toast-container",this.container.className="toast-container",document.body.appendChild(this.container)));const s=String(t||"").trim();if(!s)return null;const a=`${i}:${o}:${s}`,e=Date.now();if(this.recentToasts.has(a)&&e-this.recentToasts.get(a)<2500)return null;this.recentToasts.set(a,e);for(const[c,l]of this.recentToasts.entries())e-l>1e4&&this.recentToasts.delete(c);for(;this.toasts.length>=this.maxToasts;)this.remove(this.toasts[0]);const r=this.create(t,i,o,n);return this.container.appendChild(r),this.toasts.push(r),setTimeout(()=>{r.classList.add("show")},10),n>0&&setTimeout(()=>{this.remove(r)},n),r}create(t,i,o,n){const s=document.createElement("div");s.className=`toast toast-${i}`;const a={success:o||"Success",error:o||"Error",warning:o||"Warning",info:o||"Information"};return s.innerHTML=`
    <div class="toast-left">
        <div class="toast-inner">
        <span class="material-icons-outlined toast-left-icon">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.81087 0.477295V2.46063C2.81087 2.78733 2.81087 2.95067 2.87445 3.07546C2.93038 3.18522 3.01962 3.27446 3.12938 3.33038C3.25416 3.39396 3.41751 3.39396 3.74421 3.39396H7.71087C8.03757 3.39396 8.20092 3.39396 8.3257 3.33038C8.43546 3.27446 8.5247 3.18522 8.58063 3.07546C8.64421 2.95067 8.64421 2.78733 8.64421 2.46063V1.06063M8.64421 10.9773V7.24396C8.64421 6.91726 8.64421 6.75392 8.58063 6.62913C8.5247 6.51937 8.43546 6.43013 8.3257 6.37421C8.20092 6.31063 8.03757 6.31063 7.71087 6.31063H3.74421C3.41751 6.31063 3.25416 6.31063 3.12938 6.37421C3.01962 6.43013 2.93038 6.51937 2.87445 6.62913C2.81087 6.75392 2.81087 6.91726 2.81087 7.24396V10.9773M10.9775 4.16716V8.17729C10.9775 9.15739 10.9775 9.64743 10.7868 10.0218C10.619 10.3511 10.3513 10.6188 10.022 10.7866C9.64768 10.9773 9.15763 10.9773 8.17754 10.9773H3.27754C2.29745 10.9773 1.8074 10.9773 1.43306 10.7866C1.10377 10.6188 0.836056 10.3511 0.668278 10.0218C0.477539 9.64743 0.477539 9.15739 0.477539 8.17729V3.27729C0.477539 2.2972 0.477539 1.80716 0.668278 1.43281C0.836056 1.10353 1.10377 0.835812 1.43306 0.668034C1.8074 0.477295 2.29745 0.477295 3.27754 0.477295H7.28767C7.57303 0.477295 7.71571 0.477295 7.84998 0.50953C7.96902 0.53811 8.08282 0.585248 8.1872 0.649215C8.30494 0.721363 8.40583 0.822252 8.60761 1.02403L10.4308 2.84723C10.6326 3.049 10.7335 3.14989 10.8056 3.26763C10.8696 3.37201 10.9167 3.48582 10.9453 3.60486C10.9775 3.73913 10.9775 3.8818 10.9775 4.16716Z" stroke="white" stroke-width="0.954545" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        </div>
    </div>

    <div class="toast-body">
        <div class="toast-title">${a[i]}</div>
        <div class="toast-message">${t}</div>
    </div>

    <button type="button" class="toast-close" aria-label="Dismiss notification">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="m6 6 12 12M18 6 6 18" />
        </svg>
    </button>

    ${n>0?`<div class="toast-progress" style="animation-duration:${n}ms;"></div>`:""}
`,s.querySelector(".toast-close").addEventListener("click",e=>{e.preventDefault(),e.stopPropagation(),this.remove(s)}),s.addEventListener("click",e=>{e.target.closest(".toast-close")||this.remove(s)}),s}remove(t){!t||!t.parentElement||(t.classList.add("hide"),setTimeout(()=>{t.parentElement&&t.parentElement.removeChild(t),this.toasts=this.toasts.filter(i=>i!==t)},300))}clear(){this.toasts.forEach(t=>this.remove(t))}}new h;
