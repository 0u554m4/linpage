document.addEventListener('DOMContentLoaded', function () {
  // Show success/error messages if they exist
  const successMessage = document.querySelector('.success-message');
  const errorMessage = document.querySelector('.error-message');

  if (successMessage && successMessage.textContent.trim() !== '') {
    successMessage.style.display = 'block';
    // Scroll to the message
    successMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  if (errorMessage && errorMessage.textContent.trim() !== '') {
    errorMessage.style.display = 'block';
    // Scroll to the message
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Carousel functionality - Fixed
  const carousel = document.getElementById('carouselInner');
  const slides = carousel.querySelectorAll('.carousel-item');
  const navContainer = document.getElementById('carouselNav');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  let currentSlide = 0;

  // Create indicators
  slides.forEach((_, index) => {
    const indicator = document.createElement('div');
    indicator.classList.add('carousel-indicator');
    if (index === 0) indicator.classList.add('active');
    indicator.addEventListener('click', () => goToSlide(index));
    navContainer.appendChild(indicator);
  });

  const indicators = navContainer.querySelectorAll('.carousel-indicator');

  // Function to go to a specific slide - Fixed
  function goToSlide(n) {
    // Remove any existing transition for immediate opacity reset
    carousel.style.transition = 'none';
    currentSlide = (n + slides.length) % slides.length;
    carousel.style.transform = `translateX(-${currentSlide * 100}%)`;

    // Force reflow before re-enabling transitions
    void carousel.offsetWidth;
    carousel.style.transition = 'transform 0.5s ease';

    // Update indicators
    indicators.forEach((indicator, index) => {
      if (index === currentSlide) {
        indicator.classList.add('active');
      } else {
        indicator.classList.remove('active');
      }
    });
  }

  // Next button
  nextBtn.addEventListener('click', () => {
    goToSlide(currentSlide + 1);
  });

  // Previous button
  prevBtn.addEventListener('click', () => {
    goToSlide(currentSlide - 1);
  });

  // Auto-slide every 5 seconds
  let slideInterval = setInterval(() => {
    goToSlide(currentSlide + 1);
  }, 5000);

  // Pause auto-slide on hover
  carousel.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
  });

  // Resume auto-slide on mouse leave
  carousel.addEventListener('mouseleave', () => {
    slideInterval = setInterval(() => {
      goToSlide(currentSlide + 1);
    }, 5000);
  });

  // Size selection
  const sizeOptions = document.querySelectorAll('.size-option');
  const sizeInput = document.getElementById('sizeInput');

  sizeOptions.forEach(option => {
    option.addEventListener('click', () => {
      sizeOptions.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      sizeInput.value = option.getAttribute('data-size');
    });
  });

  // Color selection with carousel sync
  const colorOptions = document.querySelectorAll('.color-option');
  const colorInput = document.getElementById('colorInput');

  colorOptions.forEach(option => {
    option.addEventListener('click', () => {
      colorOptions.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      colorInput.value = option.getAttribute('data-color');

      const slideIndex = parseInt(option.getAttribute('data-slide'));
      if (!isNaN(slideIndex)) {
        goToSlide(slideIndex);
      }
    });
  });

  // Quantity input sync -
  // Quantity input sync - Fixed
  const quantityInput = document.getElementById('quantityInput');
  const quantityHidden = document.getElementById('quantityHidden');

  if (quantityInput && quantityHidden) {
    quantityInput.addEventListener('change', () => {
      // Ensure quantity is at least 1
      if (quantityInput.value < 1) {
        quantityInput.value = 1;
      }
      quantityHidden.value = quantityInput.value;
    });

    // Also update on input to handle typing
    quantityInput.addEventListener('input', () => {
      if (quantityInput.value) {
        quantityHidden.value = quantityInput.value;
      }
    });
  }

  // Form submission
  const orderForm = document.getElementById('orderForm');
  if (orderForm) {
    orderForm.addEventListener('submit', function (e) {
      // Additional client-side validation can be added here if needed
      const phone = document.getElementById('phone').value;
      const phoneRegex = /^0[5-7][0-9]{8}$/;

      if (!phoneRegex.test(phone)) {
        e.preventDefault();
        alert('رقم الهاتف غير صحيح. الرجاء إدخال رقم هاتف جزائري صحيح');
        return false;
      }

      // Ensure quantity is properly set before submission
      if (quantityInput && quantityHidden) {
        quantityHidden.value = quantityInput.value;
      }

      // Form will submit normally if validation passes
    });
  }

  // Countdown timer - Fixed
  function setupCountdown() {
    const countdownDate = new Date();
    countdownDate.setDate(countdownDate.getDate() + 2);
    countdownDate.setHours(countdownDate.getHours() + 11);
    countdownDate.setMinutes(countdownDate.getMinutes() + 45);
    countdownDate.setSeconds(countdownDate.getSeconds() + 22);

    function updateCountdown() {
      const now = new Date().getTime();
      const distance = countdownDate - now;

      if (distance < 0) {
        clearInterval(countdownTimer);
        const countdownEl = document.getElementById('countdown');
        if (countdownEl) {
          countdownEl.innerHTML = "<div class='countdown-expired'>العرض انتهى!</div>";
        }
        return;
      }

      // Calculate time units
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Update DOM elements if they exist
      const daysEl = document.getElementById('days');
      const hoursEl = document.getElementById('hours');
      const minutesEl = document.getElementById('minutes');
      const secondsEl = document.getElementById('seconds');

      if (daysEl) daysEl.innerText = days.toString().padStart(2, '0');
      if (hoursEl) hoursEl.innerText = hours.toString().padStart(2, '0');
      if (minutesEl) minutesEl.innerText = minutes.toString().padStart(2, '0');
      if (secondsEl) secondsEl.innerText = seconds.toString().padStart(2, '0');
    }

    // Initial update
    updateCountdown();

    // Set interval for updates
    const countdownTimer = setInterval(updateCountdown, 1000);

    // Return the timer so it can be cleared if needed
    return countdownTimer;
  }

  // Start the countdown
  const countdownTimer = setupCountdown();

  // Handle color selection and image carousel synchronization
  function setupColorImageSync() {
    const colorOptions = document.querySelectorAll('.color-option');

    colorOptions.forEach(option => {
      option.addEventListener('click', () => {
        // Update color selection UI
        colorOptions.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');

        // Update hidden input
        const colorInput = document.getElementById('colorInput');
        if (colorInput) {
          colorInput.value = option.getAttribute('data-color');
        }

        // Sync with carousel
        const slideIndex = parseInt(option.getAttribute('data-slide'));
        if (!isNaN(slideIndex)) {
          goToSlide(slideIndex);
        }
      });
    });
  }

  // Initialize color-image synchronization
  setupColorImageSync();

  // Initialize carousel to first slide explicitly
  goToSlide(0);
});