// تهيئة particles.js للخلفية
document.addEventListener('DOMContentLoaded', function () {
    particlesJS('particles-js', {
        "particles": {
            "number": {
                "value": 60, // عدد أقل للجسيمات لإضفاء مظهر أنظف
                "density": {
                    "enable": true,
                    "value_area": 900 // منطقة أكبر
                }
            },
            "color": {
                "value": "#007bff" // لون جسيمات يتناسب مع التصميم الجديد
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                }
            },
            "opacity": {
                "value": 0.6,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 0.8, // حركة أبطأ
                    "opacity_min": 0.2,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 1.5, // حركة أبطأ
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 180, // مسافة أطول للروابط
                "color": "#28a745", // لون روابط يتناسب مع التصميم
                "opacity": 0.5,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 1,
                "direction": "none",
                "random": true,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": true,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 150,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "push": {
                    "particles_nb": 4
                }
            }
        },
        "retina_detect": true
    });

    // تأثيرات الظهور التدريجي
    const elements = document.querySelectorAll('.eid-title, .eid-subtitle, .eid-content p, .eid-signature p, .social-share'); // تحديث المحددات
    elements.forEach((el, index) => {
        el.classList.add('fade-in');
        el.style.animationDelay = `${index * 0.15}s`; // تأخير أقصر لجعل الظهور أسرع قليلاً
    });

    // مشاركة واتساب
    document.getElementById('shareWhatsApp').addEventListener('click', function () {
        const shareText = `🎉 عيد أضحى مبارك من Digital by Mohamad Al-Asaad! 🎉
        
كل عام وأنتم بخير.
نتمنى لكم عيداً سعيداً مليئاً بالبركات.
        
زوروا موقعنا للمزيد من الخدمات الرقمية: https://www.di9ital.site/`;

        const encodedText = encodeURIComponent(shareText);
        window.open(`https://wa.me/?text=${encodedText}`, '_blank');
    });





    // رسالة في الكونسول
    console.log('%c🎉 عيد أضحى مبارك من Digital by Mohamad Al-Asaad!', 'color: #007bff; font-size: 20px; font-weight: bold;');
    console.log('%cزوروا موقعنا: https://www.di9ital.site/', 'color: #28a745; font-size: 14px;');
});

// تأثير حركة البطاقة عند تحريك الماوس
document.addEventListener('mousemove', function (e) {
    const card = document.querySelector('.eid-card');
    // قيم أقل لحركة أكثر دقة ونعومة
    const xAxis = (window.innerWidth / 2 - e.pageX) / 35;
    const yAxis = (window.innerHeight / 2 - e.pageY) / 35;
    card.style.transform = `perspective(1000px) rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
});

// إعادة البطاقة إلى وضعها الطبيعي عند مغادرة الماوس
document.querySelector('.eid-card').addEventListener('mouseleave', function () {
    this.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg)';
    this.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)'; // انتقال أطول وأكثر سلاسة
    setTimeout(() => {
        this.style.transition = '';
    }, 600);
});