const scrollContainer = document.querySelector(".scroll-container");
const scrollSection = document.querySelector(".news-section");

window.addEventListener("scroll", () => {
  const sectionTop = scrollSection.offsetTop;
  const sectionHeight = scrollSection.offsetHeight;
  const scrollY = window.scrollY;

  if (scrollY > sectionTop && scrollY < sectionTop + sectionHeight) {
    const move = (scrollY - sectionTop) * 0.6;
    scrollContainer.style.transform = `translateX(${-move}px)`;
  }
});
