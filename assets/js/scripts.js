document.addEventListener('DOMContentLoaded', () => {
    const stats = document.querySelectorAll('.stat-value');
    stats.forEach(stat => {
        const value = parseInt(stat.textContent);
        let current = 0;
        const increment = value / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= value) {
                stat.textContent = value;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
            }
        }, 20);
    });
});