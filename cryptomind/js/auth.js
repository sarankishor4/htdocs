document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');

    if(showRegister) showRegister.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    });

    if(showLogin) showLogin.addEventListener('click', (e) => {
        e.preventDefault();
        registerForm.style.display = 'none';
        loginForm.style.display = 'block';
    });

    const loginBtn = document.getElementById('login-btn');
    if(loginBtn) loginBtn.addEventListener('click', async () => {
        const loginInput = document.getElementById('login-input').value;
        const passInput = document.getElementById('login-pass').value;
        const err = document.getElementById('login-error');
        
        err.style.display = 'none';
        if(!loginInput || !passInput) {
            err.textContent = 'Please fill all fields';
            err.style.display = 'block';
            return;
        }

        loginBtn.classList.add('loading');
        
        try {
            const res = await fetch('api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ login: loginInput, password: passInput })
            });
            const data = await res.json();
            
            if(data.status === 'success') {
                window.location.href = 'dashboard.php';
            } else {
                err.textContent = data.message;
                err.style.display = 'block';
            }
        } catch(e) {
            err.textContent = 'Connection error. Try again.';
            err.style.display = 'block';
        }
        
        loginBtn.classList.remove('loading');
    });

    const regBtn = document.getElementById('register-btn');
    if(regBtn) regBtn.addEventListener('click', async () => {
        const user = document.getElementById('reg-user').value;
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;
        const pass = document.getElementById('reg-pass').value;
        const err = document.getElementById('register-error');
        
        err.style.display = 'none';
        
        regBtn.classList.add('loading');
        
        try {
            const res = await fetch('api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: user, full_name: name, email: email, password: pass })
            });
            const data = await res.json();
            
            if(data.status === 'success') {
                window.location.href = 'dashboard.php';
            } else {
                err.textContent = data.message;
                err.style.display = 'block';
            }
        } catch(e) {
            err.textContent = 'Connection error. Try again.';
            err.style.display = 'block';
        }
        
        regBtn.classList.remove('loading');
    });
});
