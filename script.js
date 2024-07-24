let typed;

async function fetchQuote() {
  const api_url = "https://ron-swanson-quotes.herokuapp.com/v2/quotes";
  const res = await fetch(api_url);
  var data = await res.json();
  return data;
}

async function displayQuote() {
  if (window.location.pathname === "/project.html") {
    const data = await fetchQuote();
    if (typed) {
      typed.destroy();
    }
    typed = new Typed("#quote-text", {
      strings: [data[0]],
      typeSpeed: 25,
    });
  }
}
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

displayQuote();

// Audio Section
if (annyang) {
  // Let's define a command.
  const homeCommands = {
    "navigate to *page": (page) => {
      window.location.href = `/${page}.html`;
    },
    "slide *direction": (direction) => {
      if (direction === "left") {
        const leftButton = document.getElementById("prev");
        leftButton.click();
      } else if (direction === "right") {
        const rightButton = document.getElementById("next");
        rightButton.click();
      } else {
        alert(
          "Invalid Direction! Try saying either left or right more clearly."
        );
      }
    },
  };

  const projectCommands = {
    "navigate to *page": (page) => {
      window.location.href = `/${page}.html`;
    },
    "generate new quote": () => {
      const quoteButton = document.getElementById("quote-button");
      quoteButton.click();
    },
  };

  const aboutCommands = {
    "navigate to *page": (page) => {
      window.location.href = `/${page}.html`;
    },
  };

  // Add our commands to annyang
  if (window.location.pathname === "/home.html") {
    annyang.addCommands(homeCommands);
  } else if (window.location.pathname === "/project.html") {
    annyang.addCommands(projectCommands);
  } else if (window.location.pathname === "/about.html") {
    annyang.addCommands(aboutCommands);
  }
}

function audioButton(e) {
  const id = e.target.id;
  if (id === "on") {
    annyang.start();
  } else {
    annyang.abort();
  }
}

// 2 backend API endpoints

async function loadSavedQuotes() {
  var host = window.location.origin;
  const res = await fetch(`${host}/savedQuotes`);
  var data = await res.json();
  return data;
}

async function displayQuoteList() {
  const data = await loadSavedQuotes();
  const quoteList = document.getElementById("quote-list");

  quoteList.innerHTML = "";

  data.forEach((item) => {
    const quoteLi = document.createElement("li");
    quoteLi.textContent = item.quote;
    quoteLi.classList.add("quote-li");

    quoteList.appendChild(quoteLi);
  });
}

async function saveQuote() {
  var host = window.location.origin;
  const quote = document.getElementById("quote-text");
  await fetch(`${host}/postQuote`, {
    method: "POST",
    body: JSON.stringify({
      quote: quote.textContent,
    }),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((res) => res.json)
    .then((res) => {});
  await displayQuoteList();
}

displayQuoteList();
