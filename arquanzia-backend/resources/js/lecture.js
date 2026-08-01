// Confort de lecture d'un chapitre : barre de progression, taille de police, police
// adaptée à la dyslexie. Les préférences vivent dans localStorage — le site n'a pas de
// compte lecteur, et l'endpoint serveur qui les stockait a été retiré au lot 4.
export default function initLecture() {
    var progressBar = document.getElementById('reading-progress');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var progress = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
            progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
        });
    }

    var chapterContent = document.getElementById('chapter-content');
    var fontSizeValue = document.getElementById('reader-font-size-value');
    var fontDecrease = document.getElementById('reader-font-decrease');
    var fontIncrease = document.getElementById('reader-font-increase');
    var dyslexicToggle = document.getElementById('reader-dyslexic-toggle');

    var initialScale = parseInt(localStorage.getItem('reader_font_scale') || '100', 10);

    var applyFontScale = function(scale) {
        if (!chapterContent) return;
        var normalized = Math.min(130, Math.max(85, scale));
        chapterContent.style.fontSize = normalized / 100 + 'em';
        if (fontSizeValue) fontSizeValue.textContent = normalized + '%';
        localStorage.setItem('reader_font_scale', normalized.toString());
    };

    applyFontScale(initialScale);
    if (fontDecrease) fontDecrease.addEventListener('click', function() {
        applyFontScale(parseInt(localStorage.getItem('reader_font_scale') || '100', 10) - 5);
    });
    if (fontIncrease) fontIncrease.addEventListener('click', function() {
        applyFontScale(parseInt(localStorage.getItem('reader_font_scale') || '100', 10) + 5);
    });

    if (dyslexicToggle && chapterContent) {
        var isDyslexic = localStorage.getItem('reader_font_dyslexic') === '1';

        var applyDyslexic = function(enabled) {
            chapterContent.classList.toggle('font-reader-dyslexic', enabled);
            dyslexicToggle.setAttribute('data-state', enabled ? 'active' : 'inactive');
            dyslexicToggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            localStorage.setItem('reader_font_dyslexic', enabled ? '1' : '0');
        };

        applyDyslexic(isDyslexic);
        dyslexicToggle.addEventListener('click', function() {
            applyDyslexic(dyslexicToggle.getAttribute('data-state') !== 'active');
        });
    }
}
