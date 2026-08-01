// Champ d'étoiles en parallaxe de l'arrière-plan.
export default function initEtoiles() {
    // Parallax starfield — génération et recyclage infinis
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const blobs   = document.getElementById('arq-parallax-blobs');
        const container = document.getElementById('arq-parallax-stars');
        if (!blobs || !container) return;

        const COLORS = ['#9B6FD4','#5FFEB0','#40C8E0','#D4709A','#B87333'];
        const SIZES  = [10, 12, 14, 16, 18, 20, 22, 24, 28];
        const COUNT  = 40;

        const speedMap = {10:0.08,12:0.14,14:0.20,16:0.28,18:0.36,20:0.43,22:0.50,24:0.56,28:0.64};
        const rand = (a, b) => a + Math.random() * (b - a);

        function randomSide() {
            const vw = window.innerWidth;
            // Marge gauche (0–10%) ou droite (88–98%)
            return Math.random() > 0.5
                ? rand(0, vw * 0.10)
                : rand(vw * 0.88, vw * 0.97);
        }

        function spawnStar(topPx) {
            const size  = SIZES[Math.floor(Math.random() * SIZES.length)];
            const color = COLORS[Math.floor(Math.random() * COLORS.length)];
            const opa   = rand(0.38, 0.68);
            const leftPx = randomSide();

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', '0 0 20 20');
            svg.style.cssText = `position:absolute;pointer-events:none;width:${size}px;height:${size}px;opacity:${opa};left:${leftPx}px;top:${topPx}px;`;
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', 'M10,2 L11.5,7.5 L17,9 L11.5,10.5 L10,16 L8.5,10.5 L3,9 L8.5,7.5Z');
            path.setAttribute('fill', color);
            svg.appendChild(path);
            container.appendChild(svg);

            return { el: svg, top: topPx, speed: speedMap[size] };
        }

        const vh = window.innerHeight;
        const stars = [];

        // Distribution uniforme sur toute la zone (réservoir -600px → bas viewport)
        // Un slot par étoile = espacement garanti, pas de clusters
        const RESERVOIR = 600;
        const totalRange = vh + RESERVOIR;
        const slotSize = totalRange / COUNT;
        // Mélanger l'ordre des slots pour que vitesses et couleurs ne soient pas corrélées à la position
        const slots = Array.from({length: COUNT}, (_, i) => i).sort(() => Math.random() - 0.5);
        for (let i = 0; i < COUNT; i++) {
            const slotTop = -RESERVOIR + slots[i] * slotSize + rand(0, slotSize * 0.85);
            stars.push(spawnStar(slotTop));
        }

        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrollY = window.scrollY;
                    const currentVh = window.innerHeight;

                    blobs.style.transform = `translateY(${scrollY * 0.15}px)`;

                    stars.forEach(star => {
                        const screenY = star.top + scrollY * star.speed;
                        star.el.style.transform = `translateY(${scrollY * star.speed}px)`;

                        // Recyclage : dès que l'étoile passe sous le viewport
                        if (screenY > currentVh + 60) {
                            star.top = rand(-350, -30);
                            star.el.style.top = `${star.top}px`;
                            // Nouvelle position horizontale pour la variété
                            star.el.style.left = `${randomSide()}px`;
                        }
                    });

                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    })();
}
