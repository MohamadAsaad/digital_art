document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('contactForm');
  const loading = form.querySelector('.loading');
  const errorMessage = form.querySelector('.error-message');
  const sentMessage = form.querySelector('.sent-message');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    loading.style.display = 'block';
    errorMessage.style.display = 'none';
    sentMessage.style.display = 'none';

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json' }
    })
      .then(response => {
        loading.style.display = 'none';
        if (response.ok) {
          sentMessage.style.display = 'block';
          form.reset();
        } else {
          response.json().then(data => {
            errorMessage.textContent = data.errors ? data.errors.map(err => err.message).join(', ') : 'حدث خطأ أثناء إرسال الرسالة.';
            errorMessage.style.display = 'block';
          });
        }
      })
      .catch(() => {
        loading.style.display = 'none';
        errorMessage.textContent = 'حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.';
        errorMessage.style.display = 'block';
      });
  });
});
