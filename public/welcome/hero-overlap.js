/* Revisi 13: place the dark/contrasting subject in the existing studio hero photo
   in front of the wordmark. No network request, no new image, no external library.
   Conservative color-based mask; if the photo cannot be read, retain original hero. */
(() => {
    'use strict';
    const hero = document.querySelector('.ve-hero');
    const photo = hero?.querySelector('.ve-hero__photos img:nth-child(2)');
    const word = hero?.querySelector('.ve-hero__word');
    const canvas = hero?.querySelector('.ve-hero__foreground');
    if (!hero || !photo || !word || !canvas) return;
    const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
    const lerp = (a, b, t) => a + (b - a) * t;
    const smooth = (a, b, x) => { const t = clamp((x-a)/(b-a), 0, 1); return t*t*(3-2*t); };
    const median = arr => arr.sort((a, b) => a - b)[Math.floor(arr.length / 2)];
    const shade = y => y <= .47 ? lerp(.46, .04, y/.47)
        : y <= .55 ? lerp(.04, .24, (y-.47)/.08)
        : lerp(.24, .82, (y-.55)/.45);
    function draw() {
        canvas.hidden = true;
        if (!photo.complete || !photo.naturalWidth) return;
        const p = photo.getBoundingClientRect();
        const h = hero.getBoundingClientRect();
        const t = word.getBoundingClientRect();
        if (!p.width || !p.height) return;
        // Same geometry and cover crop as the background panel, at reduced resolution.
        const scale = Math.min(1.5, 800 / p.width, 1200 / p.height);
        const w = Math.round(p.width * scale), height = Math.round(p.height * scale);
        if (w < 20 || height < 20) return;
        canvas.width = w; canvas.height = height;
        Object.assign(canvas.style, {left: `${p.left-h.left}px`, top: `${p.top-h.top}px`, width: `${p.width}px`, height: `${p.height}px`});
        const ctx = canvas.getContext('2d', {willReadFrequently:true});
        if (!ctx) return;
        const cover = Math.max(w/photo.naturalWidth, height/photo.naturalHeight);
        const dw = photo.naturalWidth*cover, dh = photo.naturalHeight*cover;
        const pos = getComputedStyle(photo).objectPosition.split(/\s+/);
        const pct = (value, fallback) => value?.endsWith('%') ? parseFloat(value)/100 : fallback;
        ctx.drawImage(photo, (w-dw)*pct(pos[0], .5), (height-dh)*pct(pos[1], .35), dw, dh);
        const im = ctx.getImageData(0,0,w,height), d = im.data;
        const y0 = clamp(Math.floor((t.top-p.top-10)*scale),0,height);
        const y1 = clamp(Math.ceil((t.bottom-p.top+12)*scale),0,height);
        if (y1<=y0) return;
        // The current studio photo has a neutral backdrop on both sides. Estimate
        // its color per scanline, then keep only a contiguous central subject.
        const candidates = new Uint8Array(w*height), confidence = new Uint8Array(w*height);
        const from = Math.floor(w*.14), to = Math.ceil(w*.86);
        const end = Math.min(height, y1+Math.round(height*.13));
        for(let y=y0;y<end;y++) {
            const a=[[],[],[]], b=[[],[],[]];
            for(let k=0;k<13;k++) {
                const xL=Math.round(w*(.025+k*.008)), xR=w-1-xL;
                for(let c=0;c<3;c++) { a[c].push(d[(y*w+xL)*4+c]); b[c].push(d[(y*w+xR)*4+c]); }
            }
            const left=a.map(median), right=b.map(median);
            for(let x=from;x<to;x++) {
                const idx=y*w+x, i=idx*4, f=x/(w-1);
                const r=lerp(left[0],right[0],f), g=lerp(left[1],right[1],f), bl=lerp(left[2],right[2],f);
                const dr=d[i]-r, dg=d[i+1]-g, db=d[i+2]-bl;
                const dist=Math.sqrt((dr*dr+dg*dg+db*db)/3);
                const darkness=-(.2126*dr+.7152*dg+.0722*db);
                const chroma=Math.max(dr,dg,db)-Math.min(dr,dg,db);
                const score=Math.max(darkness, chroma*.85, dist*.62);
                const alpha=Math.round(255*smooth(17,36,score));
                confidence[idx]=alpha;
                if(alpha>=32) candidates[idx]=1;
            }
        }
        // Keep the strongest connected subject near the middle of the panel;
        // reject small shadows, stray edges and isolated background patches.
        const seen = new Uint8Array(w*height), queue = new Int32Array(w*height);
        let best=[], bestScore=0;
        for(let y=y0;y<end;y++) for(let x=from;x<to;x++) {
            const seed=y*w+x;
            if(!candidates[seed]||seen[seed]) continue;
            let head=0, tail=1, strong=0, mid=0, overlap=0;
            queue[0]=seed;seen[seed]=1;
            while(head<tail){
                const n=queue[head++], xx=n%w, yy=Math.floor(n/w);
                if(confidence[n]>175) strong++;
                if(xx>w*.32&&xx<w*.70) mid++;
                if(yy<y1) overlap++;
                for(const neighbor of [xx>from?n-1:-1,xx+1<to?n+1:-1,yy>y0?n-w:-1,yy+1<end?n+w:-1]) {
                    if(neighbor>=0&&candidates[neighbor]&&!seen[neighbor]){seen[neighbor]=1;queue[tail++]=neighbor;}
                }
            }
            if(!overlap || mid<tail*.20 || strong<24) continue;
            const score=strong*(.25+mid/tail);
            if(score>bestScore){bestScore=score;best=Array.from(queue.subarray(0,tail));}
        }
        if(best.length<Math.max(30,w*height*.0003)) return;
        const mask=new Uint8Array(w*height);
        for(const n of best){ if(Math.floor(n/w)<y1) mask[n]=confidence[n]; }
        // Fill enclosed gaps along each row (e.g. highlights on the face), but
        // only short gaps inside the chosen subject, never the whole panel.
        for(let y=y0;y<y1;y++){
            let prev=-1;
            for(let x=from;x<to;x++)if(mask[y*w+x]>160){
                if(prev>=0 && x-prev<Math.max(5,w*.065))for(let k=prev+1;k<x;k++)mask[y*w+k]=Math.max(mask[y*w+k],220);
                prev=x;
            }
        }
        // Reapply the original hero shading so the foreground matches the photo.
        for(let y=0;y<height;y++){
            const f=shade(clamp((p.top-h.top+(y+.5)/scale)/h.height,0,1));
            for(let x=0;x<w;x++){
                const n=y*w+x,i=n*4;
                d[i+3]=mask[n];
                if(mask[n]){d[i]=Math.round(lerp(d[i],23,f));d[i+1]=Math.round(lerp(d[i+1],10,f));d[i+2]=Math.round(lerp(d[i+2],11,f));}
            }
        }
        ctx.putImageData(im,0,0);canvas.hidden=false;
    }
    let frame=0;
    const schedule=()=>{cancelAnimationFrame(frame);frame=requestAnimationFrame(()=>{try{draw();}catch(_){canvas.hidden=true;}});};
    photo.addEventListener('load',schedule);
    window.addEventListener('resize',schedule,{passive:true});
    if(typeof ResizeObserver!=='undefined'){const observer=new ResizeObserver(schedule);observer.observe(hero);observer.observe(photo);}
    if(document.fonts?.ready) document.fonts.ready.then(schedule);
    schedule();
})();
