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


window.addEventListener('scroll', function() {
  const textElement = document.querySelector('.scroll-text');
  const rect = textElement.getBoundingClientRect();
  const windowHeight = window.innerHeight;

  // Check if the text is in the viewport
  if (rect.top <= windowHeight && rect.bottom >= 0) {
    textElement.classList.add('grow');
  } else {
    textElement.classList.remove('grow');
  }
});


window.addEventListener('scroll', function() {
  const logo = document.querySelector('.logo');
  const nav = document.querySelector('nav');
  
  // Check if the page has been scrolled more than 100px
  if (window.scrollY > 100) {
    logo.classList.add('hidden');
    nav.classList.add('shrink'); // Add class to shrink the navbar
  } else {
    logo.classList.remove('hidden');
    nav.classList.remove('shrink'); // Remove class to restore original navbar size
  }
});


