const testimonials = [
  {
    text: "Travailler avec Juste-Cœur, c’est collaborer avec quelqu’un qui comprend à la fois les réalités du terrain et les attentes des partenaires internationaux.",
    name: "Name labori",
    role: "Excepteur eiusmod cupidatat",
    image: "https://images.unsplash.com/photo-1580489944761-15a19d654956"
  },
  {
    text: "Une équipe engagée, professionnelle et totalement orientée vers l’impact réel dans les communautés locales.",
    name: "Sarah Joseph",
    role: "Responsable projet",
    image: "https://images.unsplash.com/photo-1607746882042-944635dfe10e"
  },
  {
    text: "Un partenariat solide et stratégique qui apporte des résultats mesurables sur le terrain.",
    name: "David Michel",
    role: "Consultant international",
    image: "https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9"
  }
];

let index = 0;

function showTestimonial() {
  document.getElementById("testimonial-text").innerText = "“ " + testimonials[index].text + " ”";
  document.getElementById("testimonial-name").innerText = testimonials[index].name;
  document.getElementById("testimonial-role").innerText = testimonials[index].role;
  document.getElementById("testimonial-image").src = testimonials[index].image;
}

function nextTestimonial() {
  index++;
  if (index >= testimonials.length) index = 0;
  showTestimonial();
}

function prevTestimonial() {
  index--;
  if (index < 0) index = testimonials.length - 1;
  showTestimonial();
}
