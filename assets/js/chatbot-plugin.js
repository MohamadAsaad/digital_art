/* =========================================
   Chatbot Plugin - Bilingual & Smart Version
   ========================================= */

// ====== String Similarity ======
const stringSimilarity = (function () {
    function compareTwoStrings(first, second) {
        first = first.replace(/\s+/g, '');
        second = second.replace(/\s+/g, '');
        if (!first.length && !second.length) return 1;
        if (!first.length || !second.length) return 0;
        if (first === second) return 1;
        if (first.length === 1 && second.length === 1) return 0;
        if (first.length < 2 || second.length < 2) return 0;

        let firstBigrams = new Map();
        for (let i = 0; i < first.length - 1; i++) {
            const bigram = first.substring(i, i + 2);
            const count = firstBigrams.has(bigram) ? firstBigrams.get(bigram) + 1 : 1;
            firstBigrams.set(bigram, count);
        }

        let intersectionSize = 0;
        for (let i = 0; i < second.length - 1; i++) {
            const bigram = second.substring(i, i + 2);
            const count = firstBigrams.has(bigram) ? firstBigrams.get(bigram) : 0;
            if (count > 0) {
                firstBigrams.set(bigram, count - 1);
                intersectionSize++;
            }
        }

        return (2.0 * intersectionSize) / (first.length + second.length - 2);
    }
    return { compareTwoStrings };
})();

// ====== Language Detection ======
function detectLanguage(text) {
    return /[\u0600-\u06FF]/.test(text) ? 'ar' : 'en';
}

// ====== Normalize Arabic ======
function normalizeArabic(text) {
    return text
        .replace(/[أآإ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/ى/g, 'ي')
        .replace(/[ؤئ]/g, 'ء')
        .replace(/[^\u0621-\u063A\u0641-\u064A0-9a-zA-Z ]/g, '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

// ====== Rules ======
const rules = [
    // --- Greetings ---
    {
        keywords: ["مرحبا", "مرحبًا", "اهلا", "اهلاً", "كيف الحال", "كيفك", "hello", "hi", "hey"],
        response: {
            ar: "مرحبًا بك في Digital Art! كيف يمكنني مساعدتك اليوم؟",
            en: "Welcome to Digital Art! How can we help bring your vision to life today?"
        }
    },
    {
        keywords: ["السلام عليكم", "سلام عليكم", "السلام", "سلام", "salam", "peace"],
        response: {
            ar: "وعليكم السلام ورحمة الله وبركاته! أهلًا بك في Digital Art. كيف يمكنني مساعدتك اليوم؟",
            en: "Peace be upon you! Welcome to Digital Art. How may we assist you today?"
        }
    },

    // --- About Us ---
    {
        keywords: ["من أنت", "من انتم", "من انت", "ما اسمك", "تعريف", "عن الشركة", "نبذة عنكم",
            "who are you", "about your company", "tell me about you"],
        response: {
            ar: "أنا روبوت دردشة لشركة Digital Art. نحن متخصصون في تصميم وتطوير المواقع والتسويق الرقمي بخبرة تزيد عن 5 سنوات.",
            en: "I am the virtual assistant for Digital Art. We specialize in web design, development, and digital marketing, backed by over 5 years of proven expertise."
        }
    },
    {
        keywords: ["فريق العمل", "المبرمجين", "المصممين", "خبرتكم", "عدد الموظفين",
            "your team", "developers", "designers", "your experience"],
        response: {
            ar: "لدينا فريق من 10 مصممين ومبرمجين ومسوقين محترفين بأكثر من 100 مشروع ناجح.",
            en: "Our team of 10 talented designers, developers, and marketers has delivered over 100 successful projects."
        }
    },

    // --- Services ---
    {
        keywords: ["خدمات", "ماذا تقدمون", "عروض", "خدماتكم",
            "services", "what do you offer", "offers"],
        response: {
            ar: "خدماتنا تشمل:\n- تصميم وتطوير المواقع\n- تطبيقات الجوال\n- التسويق الرقمي\n- الحملات الإعلانية\n- الاستضافة",
            en: "Our services include:\n- Web design & development\n- Mobile applications\n- Digital marketing\n- Ad campaigns\n- Web hosting"
        }
    },
    {
        keywords: ["تصميم المواقع", "موقع ويب", "مواقع الويب", "موقع إلكتروني",
            "web design", "website", "company website"],
        response: {
            ar: "نصمم مواقع حديثة سريعة ومتجاوبة مع جميع الأجهزة.",
            en: "We create modern, fast, and fully responsive websites for all devices."
        }
    },
    {
        keywords: ["متجر إلكتروني", "موقع بيع", "موقع تسوق",
            "ecommerce", "online store", "shopping site"],
        response: {
            ar: "نطور متاجر إلكترونية مع بوابات دفع آمنة وإدارة مخزون وشحن.",
            en: "We build e-commerce stores with secure payment gateways, inventory management, and shipping integration."
        }
    },
    {
        keywords: ["تطوير المواقع", "برمجة", "برمجة مواقع",
            "web development", "website coding"],
        response: {
            ar: "برمجة مواقع وحلول مخصصة باستخدام أحدث التقنيات.",
            en: "We develop custom websites using the latest technologies."
        }
    },

    // --- New Services ---
    {
        keywords: ["تطبيقات الجوال", "برمجة تطبيق", "تطبيق موبايل",
            "mobile app", "app development", "android app", "ios app"],
        response: {
            ar: "نطور تطبيقات موبايل لأنظمة Android وiOS بواجهة مستخدم عصرية.",
            en: "We develop modern mobile applications for Android and iOS with sleek user interfaces."
        }
    },
    {
        keywords: ["هوية بصرية", "تصميم شعار", "شعار", "branding", "logo design", "brand identity"],
        response: {
            ar: "تصميم هوية بصرية متكاملة وشعارات تعكس رؤية شركتك.",
            en: "We create complete brand identities and logos that reflect your company's vision."
        }
    },
    {
        keywords: ["كتابة محتوى", "محتوى تسويقي", "content writing", "marketing content"],
        response: {
            ar: "كتابة محتوى تسويقي يجذب جمهورك المستهدف ويزيد المبيعات.",
            en: "We craft marketing content that engages your audience and drives sales."
        }
    },
    {
        keywords: ["استشارات تقنية", "consulting", "technical consulting"],
        response: {
            ar: "نوفر استشارات تقنية لمساعدتك في اختيار أفضل الحلول لمشروعك.",
            en: "We provide technical consulting to help you choose the best solutions for your project."
        }
    },

    // --- Marketing ---
    {
        keywords: ["التسويق الرقمي", "اعلانات", "إعلانات", "تسويق", "تسويق الكتروني",
            "digital marketing", "ads", "advertising", "marketing"],
        response: {
            ar: "خدماتنا التسويقية تشمل SEO وإعلانات جوجل وحملات السوشيال ميديا.",
            en: "Our marketing services include SEO, Google Ads, and social media campaigns."
        }
    },

    // --- Prices ---
    {
        keywords: ["تكلفة", "اسعار", "أسعار", "السعر", "بكم", "بكم الموقع",
            "pricing", "how much", "cost", "price", "website price"],
        response: {
            ar: "الأسعار تبدأ من:\n- موقع: 500$\n- متجر: 1000$\n- تسويق: 300$/شهر",
            en: "Pricing starts from:\n- Website: $500\n- E-commerce: $1000\n- Marketing: $300/month"
        }
    },

    // --- Support ---
    {
        keywords: ["دعم", "مساعدة", "support", "help"],
        response: {
            ar: "فريق الدعم متاح 24/7 عبر البريد أو الواتساب.",
            en: "Our support team is available 24/7 via email or WhatsApp."
        }
    },

    // --- Contact ---
    {
        keywords: ["تواصل", "اتصال", "whatsapp", "contact", "call"],
        response: {
            ar: "يمكنك التواصل عبر:\nواتساب: +905398847282\nالبريد: di9ital.site@gmail.com",
            en: "You can reach us via:\nWhatsApp: +905398847282\nEmail: di9ital.site@gmail.com"
        }
    }
];

// ====== Find Reply ======
function findReply(message) {
    const lang = detectLanguage(message);
    const cleanMessage = normalizeArabic(message);
    let bestMatch = { rating: 0, response: null };

    const rulesToCheck = rules.filter(r =>
        r.keywords.some(k => detectLanguage(k) === lang)
    );

    for (const rule of rulesToCheck) {
        for (const keyword of rule.keywords) {
            const score = stringSimilarity.compareTwoStrings(
                cleanMessage,
                normalizeArabic(keyword)
            );
            if (score > bestMatch.rating) {
                bestMatch = { rating: score, response: rule.response };
            }
        }
    }

    if (bestMatch.rating > 0.4) {
        if (typeof bestMatch.response === 'object') {
            return bestMatch.response[lang] || bestMatch.response['en'];
        }
        return bestMatch.response;
    }

    return lang === "ar"
        ? "لم أفهم سؤالك، ممكن توضحه أكثر؟"
        : "I didn’t quite get that, could you clarify?";
}

// ====== UI ======
function createChatbotElements() {
    const chatbotContainer = document.createElement('div');
    chatbotContainer.id = 'digital-art-chatbot-container';
    chatbotContainer.className = 'digital-art-chatbot-container';

    const header = document.createElement('div');
    header.className = 'digital-art-chatbot-header';
    header.innerHTML = `<h3 id="digital-art-chatbot-title">روبوت الدردشة الذكي</h3>
        <span class="digital-art-close-btn">&times;</span>`;

    const body = document.createElement('div');
    body.id = 'digital-art-chatbot-body';
    body.className = 'digital-art-chatbot-body';
    body.innerHTML = `<div class="digital-art-message digital-art-bot-message">
            <p id="digital-art-welcome-message">مرحبًا بك في Digital Art! كيف يمكنني مساعدتك اليوم؟</p>
        </div>`;

    const footer = document.createElement('div');
    footer.className = 'digital-art-chatbot-footer';
    footer.innerHTML = `<input type="text" id="digital-art-messageInput" placeholder="اكتب رسالتك هنا..." />
        <button id="digital-art-sendBtn"><i class="fas fa-paper-plane"></i></button>
        <button id="digital-art-whatsappBtn"><i class="fab fa-whatsapp"></i></button>`;

    const toggleBtn = document.createElement('div');
    toggleBtn.className = 'digital-art-chatbot-toggle';
    toggleBtn.innerHTML = '<i class="fa-solid fa-robot"></i>';

    chatbotContainer.appendChild(header);
    chatbotContainer.appendChild(body);
    chatbotContainer.appendChild(footer);
    document.body.appendChild(chatbotContainer);
    document.body.appendChild(toggleBtn);

    const typingIndicator = document.createElement('div');
    typingIndicator.id = 'digital-art-typing-indicator';
    typingIndicator.style.display = 'none';
    typingIndicator.innerHTML = '<div class="digital-art-typing-dots"><span></span><span></span><span></span></div>';
    body.appendChild(typingIndicator);
}

// ====== Events ======
function initChatbotEvents() {
    document.querySelector('.digital-art-chatbot-toggle').addEventListener('click', toggleChatbot);
    document.querySelector('.digital-art-close-btn').addEventListener('click', toggleChatbot);
    document.getElementById('digital-art-sendBtn').addEventListener('click', sendMessage);
    document.getElementById('digital-art-messageInput').addEventListener('keypress', e => {
        if (e.key === 'Enter') sendMessage();
    });
    document.getElementById('digital-art-whatsappBtn').addEventListener('click', () => {
        window.open("https://wa.me/905398847282?text=Hello, I want to inquire about your services", '_blank');
    });
    document.addEventListener('click', e => {
        const chatbot = document.getElementById('digital-art-chatbot-container');
        const toggleBtn = document.querySelector('.digital-art-chatbot-toggle');
        if (!chatbot.contains(e.target) && !toggleBtn.contains(e.target)) {
            chatbot.classList.remove('digital-art-active');
        }
    });
}

function showTypingIndicator() {
    const typing = document.getElementById('digital-art-typing-indicator');
    typing.style.display = 'block';
    const chatBody = document.getElementById('digital-art-chatbot-body');
    chatBody.scrollTop = chatBody.scrollHeight;
}

function hideTypingIndicator() {
    document.getElementById('digital-art-typing-indicator').style.display = 'none';
}

function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

function addMessage(text, sender) {
    const chatBox = document.getElementById('digital-art-chatbot-body');
    const message = document.createElement('div');
    message.className = `digital-art-message digital-art-${sender}-message`;
    message.innerHTML = `<p>${text}</p>`;
    chatBox.appendChild(message);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function toggleChatbot() {
    document.getElementById('digital-art-chatbot-container').classList.toggle('digital-art-active');
}

function sendMessage() {
    const input = document.getElementById('digital-art-messageInput');
    const message = sanitizeInput(input.value.trim());
    if (message) {
        addMessage(message, 'user');
        input.value = '';
        showTypingIndicator();
        setTimeout(() => {
            hideTypingIndicator();
            addMessage(findReply(message), 'bot');
        }, 1000 + Math.random() * 2000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    createChatbotElements();
    initChatbotEvents();
});

