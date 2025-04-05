async function loadComponent(componentPath, placeholderId) {
    const placeholder = document.getElementById(placeholderId);
    if (!placeholder) {
        console.error(`Element with id ${placeholderId} not found.`);
        return;
    }

    // إضافة مؤشر تحميل
    placeholder.innerHTML = '<div class="loading-spinner">Loading...</div>';

    try {
        const response = await fetch(componentPath);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.text();
        placeholder.innerHTML = data;

        // تهيئة الأحداث بعد تحميل الـ Header
        if (placeholderId === 'header') {
            initializeHeaderEvents();
        }
    } catch (error) {
        placeholder.innerHTML = '<div>Error loading component.</div>';
        console.error('Error loading component:', error);
    }
}

function initializeHeaderEvents() {
    // تهيئة الزر التبديل
    const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

    if (mobileNavToggleBtn) {
        mobileNavToggleBtn.addEventListener('click', () => {
            document.querySelector('body').classList.toggle('mobile-nav-active');
            mobileNavToggleBtn.classList.toggle('bi-list');
            mobileNavToggleBtn.classList.toggle('bi-x');
        });
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    await loadComponent('header.html', 'header');
    await loadComponent('footer.html', 'footer');
    await loadComponent('contact-form.html', 'contact');
});