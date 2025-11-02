/**
 * ===========================================================
 * Dynamic Component Loader + Header/Dropdown Controller (Stable)
 * ===========================================================
 */
async function loadComponent(url, targetId) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const html = await response.text();
        const target = document.getElementById(targetId);
        if (!target) return console.error(`❌ Element with id="${targetId}" not found.`);
        target.innerHTML = html;

        if (targetId === "header") initializeHeader();
        if (targetId === "footer") initializeFooter();
    } catch (error) {
        console.error(`❌ Error loading ${url}:`, error);
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadComponent("header.html", "header");
    await loadComponent("footer.html", "footer");
});

/* ===========================================================
   HEADER INITIALIZATION
=========================================================== */
function initializeHeader() {
    const body = document.body;
    const header = document.querySelector(".header");
    const mobileToggle = header.querySelector(".mobile-nav-toggle");
    const themeToggle = header.querySelector("#themeToggle");

    /* ---------- Mobile Nav Toggle ---------- */
    if (mobileToggle) {
        mobileToggle.addEventListener("click", () => {
            body.classList.toggle("mobile-nav-active");
            mobileToggle.classList.toggle("bi-list");
            mobileToggle.classList.toggle("bi-x");
        });
    }

    /* ---------- Dropdown Logic ---------- */
    header.querySelectorAll(".navmenu .dropdown > a").forEach((link) => {
        const parentLi = link.parentElement;
        link.addEventListener("click", (e) => {
            if (window.innerWidth < 1200) {
                e.preventDefault();
                parentLi.classList.toggle("active");
            }
        });
    });

    /* ---------- Deep Dropdown Direction ---------- */
    function adjustDropdownDirection(li, dropdownMenu) {
        const rect = dropdownMenu.getBoundingClientRect();
        if (rect.right > window.innerWidth - 20) li.classList.add("submenu-left");
        else li.classList.remove("submenu-left");
    }

    header.querySelectorAll(".navmenu .dropdown").forEach((li) => {
        const submenu = li.querySelector("ul");
        if (submenu) li.addEventListener("mouseenter", () => adjustDropdownDirection(li, submenu));
    });

    /* ---------- Theme Toggle ---------- */
    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            document.documentElement.classList.toggle("dark-theme");
            const icon = themeToggle.querySelector("i");
            icon.classList.toggle("fa-sun");
            icon.classList.toggle("fa-moon");
        });
    }

    /* ---------- Scroll Effect (Optimized) ---------- */
    let isScrolled = false;
    window.addEventListener("scroll", () => {
        const scrolledNow = window.scrollY > 40;
        if (scrolledNow !== isScrolled) {
            isScrolled = scrolledNow;
            header.classList.toggle("scrolled", isScrolled);
        }
    });
}

/* ===========================================================
   FOOTER INITIALIZATION (optional placeholder)
=========================================================== */
function initializeFooter() {
    // يمكن إضافة أكواد تفاعلية للفوتر هنا
}
