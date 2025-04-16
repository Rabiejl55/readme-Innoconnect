document.addEventListener('DOMContentLoaded', () => {
    // Validation des formulaires
    const registerForm = document.querySelector('#register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const email = document.querySelector('#email').value;
            const password = document.querySelector('#password').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer un email valide.');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                return;
            }
        });
    }

    const loginForm = document.querySelector('#login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const email = document.querySelector('#email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer un email valide.');
                return;
            }
        });
    }

    const forgotPasswordForm = document.querySelector('#forgot-password-form');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', (e) => {
            const email = document.querySelector('#email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer un email valide.');
                return;
            }
        });
    }

    // Animation des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.style.opacity = '0';
        setTimeout(() => {
            form.style.transition = 'opacity 0.5s ease';
            form.style.opacity = '1';
        }, 100);
    });

    // Thème sombre
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        // Charger le thème sauvegardé au chargement de la page
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-theme');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }

        // Gérer le clic sur le bouton
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            const isDarkTheme = document.body.classList.contains('dark-theme');
            themeToggle.innerHTML = isDarkTheme ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
        });
    } else {
        console.error('Le bouton de thème sombre n\'a pas été trouvé.');
    }
});