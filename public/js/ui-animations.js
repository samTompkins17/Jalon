/* ui-animations.js — aucune dépendance. <script src="{{ asset('js/ui-animations.js') }}" defer></script> */
(() => {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Déclenche quand l'élément entre à l'écran
  const onVisible = (els, cb, threshold = .2) => {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { cb(e.target); io.unobserve(e.target); } });
    }, { threshold });
    els.forEach(el => io.observe(el));
  };

  // 1. Arrivée douce (stagger automatique par ordre dans le DOM)
  const arrive = [...document.querySelectorAll('[data-anim="arrive"]')];
  arrive.forEach((el, i) => el.style.setProperty('--i', el.dataset.index ?? i));
  onVisible(arrive, el => el.classList.add('is-in'), .1);

  // 3. Compteurs à bandes de chiffres : <span data-count="27,26" data-suffix="h">
  document.querySelectorAll('[data-count]').forEach(el => {
    const value = el.dataset.count, suffix = el.dataset.suffix || '';
    el.textContent = '';
    el.classList.add('rolling');
    el.setAttribute('aria-label', value + suffix);
    let d = 0;
    [...value].forEach(ch => {
      if (/\d/.test(ch)) {
        const wrap = document.createElement('span'); wrap.className = 'rolling__digit'; wrap.setAttribute('aria-hidden', 'true');
        const strip = document.createElement('span'); strip.className = 'rolling__strip';
        strip.style.setProperty('--d', d++);
        strip.dataset.to = ch;
        strip.innerHTML = [...'0123456789'].map(n => `<span>${n}</span>`).join('');
        wrap.appendChild(strip); el.appendChild(wrap);
      } else {
        const s = document.createElement('span'); s.className = 'rolling__static'; s.textContent = ch === ' ' ? ' ' : ch; el.appendChild(s);
      }
    });
    if (suffix) { const s = document.createElement('span'); s.className = 'rolling__static'; s.textContent = suffix; el.appendChild(s); }
  });
  onVisible([...document.querySelectorAll('[data-count]')], el => {
    el.querySelectorAll('.rolling__strip').forEach(s => {
      requestAnimationFrame(() => requestAnimationFrame(() => { s.style.transform = `translateY(-${s.dataset.to}em)`; }));
    });
  });

  // 4. Barres
  const bars = [...document.querySelectorAll('[data-bars]')];
  bars.forEach(g => g.querySelectorAll('.bar').forEach((b, i) => b.style.setProperty('--i', i)));
  onVisible(bars, el => el.classList.add('is-in'));

  // 6. Anneau : <div class="ring" data-ring="68">
  document.querySelectorAll('[data-ring]').forEach(el => {
    const pct = Math.max(0, Math.min(100, +el.dataset.ring)), size = +(el.dataset.size || 96), sw = +(el.dataset.stroke || 9);
    const r = (size - sw) / 2, c = 2 * Math.PI * r;
    el.innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
      <circle class="ring__track" cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke-width="${sw}"/>
      <circle class="ring__bar" cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke-width="${sw}" stroke-dasharray="${c}" stroke-dashoffset="${c}"/>
    </svg><span class="ring__label">${pct} %</span>`;
    el._target = c * (1 - pct / 100);
  });
  onVisible([...document.querySelectorAll('[data-ring]')], el => {
    const bar = el.querySelector('.ring__bar');
    requestAnimationFrame(() => requestAnimationFrame(() => bar.style.strokeDashoffset = el._target));
  });

  // 2. Inclinaison 3D au survol : <div data-tilt data-tilt-max="10">
  document.querySelectorAll('[data-tilt]').forEach(el => {
    if (reduce) return;
    const max = +(el.dataset.tiltMax || 10);
    let frame = 0;
    el.addEventListener('pointermove', e => {
      if (frame) return;
      frame = requestAnimationFrame(() => {
        frame = 0;
        const r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        el.style.transform = `perspective(800px) rotateY(${x * max}deg) rotateX(${-y * max}deg)`;
      });
    });
    el.addEventListener('pointerleave', () => { cancelAnimationFrame(frame); frame = 0; el.style.transform = ''; });
  });

  // 5. Filtre favoris : <button data-filter="video"> + <div data-filter-card data-type="video">
  document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => {
    const type = btn.dataset.filter;
    document.querySelectorAll('[data-filter-card]').forEach(c =>
      c.classList.toggle('is-dim', type !== 'all' && c.dataset.type !== type));
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.toggle('is-active', b === btn));
  }));

  // 7. Rail de navigation avec pastille élastique : <nav class="rail" data-rail>
  document.querySelectorAll('[data-rail]').forEach(rail => {
    let pill = rail.querySelector('.rail-pill');
    if (!pill) {
      pill = document.createElement('span'); pill.className = 'rail-pill'; rail.prepend(pill);
    }
    const move = a => {
      pill.style.height = a.offsetHeight + 'px';
      const railRect = rail.getBoundingClientRect();
      const aRect = a.getBoundingClientRect();
      const offsetTop = aRect.top - railRect.top + rail.scrollTop;
      pill.style.transform = `translateY(${offsetTop}px)`;
      rail.querySelectorAll('a').forEach(x => x.classList.toggle('is-active', x === a));
    };
    rail.querySelectorAll('a').forEach(a => a.addEventListener('click', e => { if (a.getAttribute('href') === '#') e.preventDefault(); move(a); }));
    const start = rail.querySelector('a.is-active') || rail.querySelector('a');
    if (start) requestAnimationFrame(() => move(start));
  });
})();
