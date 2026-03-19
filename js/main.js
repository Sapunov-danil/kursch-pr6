// js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Анимация появления элементов при скролле
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.category-card, .product-card, .benefit-card');
        
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                element.style.opacity = "1";
                element.style.transform = "translateY(0)";
            }
        });
    };
    
    // Установка начального состояния для анимации
    const elementsToAnimate = document.querySelectorAll('.category-card, .product-card, .benefit-card');
    elementsToAnimate.forEach(element => {
        element.style.opacity = "0";
        element.style.transform = "translateY(20px)";
        element.style.transition = "opacity 0.5s, transform 0.5s";
    });
    
    // Запуск анимации при загрузке и скролле
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
    
    // Обработка поиска
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value.trim();
                if (searchValue) {
                    window.location.href = `catalog/catalog.php?search=${encodeURIComponent(searchValue)}`;
                }
            }
        });
    }
    
    // Обработка сообщений (авто-скрытие через 5 секунд)
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});