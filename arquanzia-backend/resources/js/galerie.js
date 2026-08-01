// Visionneuse des galeries d'encyclopédie.
export default function initGalerie() {
    const images = document.querySelectorAll('.lightbox-image');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const lightboxDownload = document.getElementById('lightbox-download');
    let currentIndex = 0;

    function showImage(index) {
        currentIndex = index;
        const img = images[index];
        lightboxImg.src = img.href;

        const caption = img.dataset.caption || '';
        lightboxCaption.textContent = caption;

        if (img.dataset.downloadable === '1') {
            lightboxDownload.classList.remove('hidden');
            lightboxDownload.onclick = function() {
                window.location.href = '/telechargement/image/' + img.dataset.imageId;
            };
        } else {
            lightboxDownload.classList.add('hidden');
        }

        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
    }

    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
    }

    images.forEach((img, index) => {
        img.addEventListener('click', (e) => {
            e.preventDefault();
            showImage(index);
        });
    });

    document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

    document.getElementById('lightbox-prev').addEventListener('click', () => {
        showImage((currentIndex - 1 + images.length) % images.length);
    });
    document.getElementById('lightbox-next').addEventListener('click', () => {
        showImage((currentIndex + 1) % images.length);
    });

    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showImage((currentIndex - 1 + images.length) % images.length);
        if (e.key === 'ArrowRight') showImage((currentIndex + 1) % images.length);
    });
}
