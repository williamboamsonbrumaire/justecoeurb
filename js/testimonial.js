// Sélectionnez tous les éléments de témoignage de la liste
const testimonialItems = document.querySelectorAll('.testimonials-item');
// Sélectionnez l'élément <img> à mettre à jour
const testimonialImage = document.getElementById('testimonial-image');

// Détermine l'index du témoignage actuellement actif
let currentIndex = 0;

function updateTestimonial(newIndex) {
    // 1. Gère l'index pour le défilement circulaire
    const totalItems = testimonialItems.length;
    
    if (newIndex >= totalItems) {
        currentIndex = 0; // Revient au premier
    } else if (newIndex < 0) {
        currentIndex = totalItems - 1; // Va au dernier
    } else {
        currentIndex = newIndex;
    }
    
    // 2. Retire la classe 'active' de TOUS les éléments pour les masquer
    testimonialItems.forEach(item => {
        item.classList.remove('active');
    });

    // 3. Ajoute la classe 'active' au nouvel élément pour l'afficher (le commentaire + nom/rôle)
    const currentItem = testimonialItems[currentIndex];
    currentItem.classList.add('active');

    // 4. Met à jour l'image en lisant l'attribut data-image du témoignage actif
    const newImageSrc = currentItem.getAttribute('data-image');
    testimonialImage.src = newImageSrc;
}

// Fonction appelée par le bouton "Suivant" (›)
function nextTestimonial() {
    updateTestimonial(currentIndex + 1);
}

// Fonction appelée par le bouton "Précédent" (‹)
function prevTestimonial() {
    updateTestimonial(currentIndex - 1);
}