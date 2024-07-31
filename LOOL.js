let currentIndex = 0;

function moveSlide(direction) {
  const slides = document.querySelector(".slides");
  const slideWidth = slides.children[0].clientWidth;
  const totalSlides = slides.children.length;

  currentIndex += direction;

  if (currentIndex >= totalSlides) {
    currentIndex = 0;
  }
  // If currentIndex becomes negative, set it to the index of the last slide
  else if (currentIndex < 0) {
    currentIndex = totalSlides - 1;
  }

  // Calculate the new position of the slides container
  const offset = -currentIndex * slideWidth;
  slides.style.transform = `translateX(${offset}px)`;
}
