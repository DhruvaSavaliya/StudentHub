/* =========================================================
   EXISTING PRACTICAL 1 JAVASCRIPT
   ========================================================= */

   console.log("StudentHub javascript loaded successfully");
   console.log("Welcome to StudentHub");
   console.log("Practical - 1");
   
   
   // Variables
   let studentName = "Dhruva";
   let course = "Information Technology";
   let semester = 5;
   
   console.log(studentName);
   console.log(course);
   console.log(semester);
   
   
   // Datatypes
   let college = "CHARUSAT";
   let year = 2026;
   let isStudent = true;
   
   console.log(college);
   console.log(year);
   console.log(isStudent);
   
   
   // Function
   function WelcomeMessage() {
       console.log("Welcome to StudentHUB");
   }
   
   WelcomeMessage();
   
   
   function WelcomeStudent(name) {
       console.log("Welcome " + name);
   }
   
   WelcomeStudent("Dhruva");
   
   
   let heading = document.getElementById("welcomeheading");
   
   
   /* =========================================================
      PRACTICAL 4
      DOM MANIPULATION & EVENT HANDLING
      ========================================================= */
   
   
   /* =========================================================
      1. HAMBURGER MENU
      ========================================================= */
   
   const hamburger = document.getElementById("hamburger");
   const navLinks = document.getElementById("navLinks");
   
   if (hamburger && navLinks) {
   
       hamburger.addEventListener("click", function () {
   
           navLinks.classList.toggle("show");
   
           if (navLinks.classList.contains("show")) {
               hamburger.innerHTML = "✕";
           } else {
               hamburger.innerHTML = "☰";
           }
   
       });
   
   }
   
   
   /* =========================================================
      2. LIGHT / DARK THEME
      ========================================================= */
   
   const themeToggle = document.getElementById("themeToggle");
   
   if (themeToggle) {
   
       // Check saved theme
       const savedTheme = localStorage.getItem("studentHubTheme");
   
       if (savedTheme === "dark") {
           document.body.classList.add("dark-mode");
           themeToggle.innerHTML = "☀️";
       } else {
           themeToggle.innerHTML = "🌙";
       }
   
   
       // Toggle theme
       themeToggle.addEventListener("click", function () {
   
           document.body.classList.toggle("dark-mode");
   
           if (document.body.classList.contains("dark-mode")) {
   
               localStorage.setItem("studentHubTheme", "dark");
               themeToggle.innerHTML = "☀️";
   
           } else {
   
               localStorage.setItem("studentHubTheme", "light");
               themeToggle.innerHTML = "🌙";
   
           }
   
       });
   
   }
   
   
   /* =========================================================
      3. NOTIFICATION BANNER
      ========================================================= */
   
   const notification = document.getElementById("notification");
   const closeNotification = document.getElementById("closeNotification");
   
   if (closeNotification && notification) {
   
       closeNotification.addEventListener("click", function () {
   
           notification.style.display = "none";
   
       });
   
   }
   
   
   /* =========================================================
      4. IMAGE / CONTENT SLIDER
      ========================================================= */
   
   const sliderImage = document.getElementById("sliderImage");
   const prevSlide = document.getElementById("prevSlide");
   const nextSlide = document.getElementById("nextSlide");
   const sliderDots = document.getElementById("sliderDots");
   
   
   const sliderImages = [
       "images/charusat.png",
       "images/event1.jpeg",
       "images/event2.jpeg",
       "images/event3.jpeg"
   ];
   
   let currentSlide = 0;
   
   
   /* Create slider dots */
   
   if (sliderDots) {
   
       sliderImages.forEach(function (image, index) {
   
           const dot = document.createElement("button");
   
           dot.classList.add("slider-dot");
   
           if (index === 0) {
               dot.classList.add("active");
           }
   
           dot.addEventListener("click", function () {
   
               currentSlide = index;
               showSlide(currentSlide);
   
           });
   
           sliderDots.appendChild(dot);
   
       });
   
   }
   
   
   /* Display selected slide */
   
   function showSlide(index) {
   
       if (!sliderImage) {
           return;
       }
   
       sliderImage.src = sliderImages[index];
   
       const dots = document.querySelectorAll(".slider-dot");
   
       dots.forEach(function (dot, i) {
   
           dot.classList.toggle("active", i === index);
   
       });
   
   }
   
   
   /* Previous button */
   
   if (prevSlide) {
   
       prevSlide.addEventListener("click", function () {
   
           currentSlide--;
   
           if (currentSlide < 0) {
               currentSlide = sliderImages.length - 1;
           }
   
           showSlide(currentSlide);
   
       });
   
   }
   
   
   /* Next button */
   
   if (nextSlide) {
   
       nextSlide.addEventListener("click", function () {
   
           currentSlide++;
   
           if (currentSlide >= sliderImages.length) {
               currentSlide = 0;
           }
   
           showSlide(currentSlide);
   
       });
   
   }
   
   
   /* Automatic slider */
   
   if (sliderImage) {
   
       setInterval(function () {
   
           currentSlide++;
   
           if (currentSlide >= sliderImages.length) {
               currentSlide = 0;
           }
   
           showSlide(currentSlide);
   
       }, 4000);
   
   }
   
   
   /* =========================================================
      5. MODAL POPUP
      ========================================================= */
   
   const learnMoreBtn = document.getElementById("learnMoreBtn");
   const infoModal = document.getElementById("infoModal");
   const modalClose = document.getElementById("modalClose");
   const modalOk = document.getElementById("modalOk");
   
   
   /* Open modal */
   
   if (learnMoreBtn && infoModal) {
   
       learnMoreBtn.addEventListener("click", function () {
   
           infoModal.classList.add("show");
   
       });
   
   }
   
   
   /* Close modal */
   
   if (modalClose && infoModal) {
   
       modalClose.addEventListener("click", function () {
   
           infoModal.classList.remove("show");
   
       });
   
   }
   
   
   /* Close using Got it button */
   
   if (modalOk && infoModal) {
   
       modalOk.addEventListener("click", function () {
   
           infoModal.classList.remove("show");
   
       });
   
   }
   
   
   /* Close modal by clicking outside */
   
   if (infoModal) {
   
       infoModal.addEventListener("click", function (event) {
   
           if (event.target === infoModal) {
   
               infoModal.classList.remove("show");
   
           }
   
       });
   
   }
   
   
   /* =========================================================
      6. COLLAPSIBLE FAQ
      ========================================================= */
   
   const faqQuestions = document.querySelectorAll(".faq-question");
   
   faqQuestions.forEach(function (question) {
   
       question.addEventListener("click", function () {
   
           const faqItem = question.parentElement;
   
           faqItem.classList.toggle("active");
   
       });
   
   });
   
   
   /* =========================================================
      7. ESC KEY
      Close modal when pressing Escape
      ========================================================= */
   
   document.addEventListener("keydown", function (event) {
   
       if (event.key === "Escape" && infoModal) {
   
           infoModal.classList.remove("show");
   
       }
   
   });
   
   
   console.log("Practical 4 JavaScript loaded successfully");