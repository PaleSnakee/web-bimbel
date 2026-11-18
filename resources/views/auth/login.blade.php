<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - EL Renaissance</title>
    <style>
        :root {
            --background: #F8FAFC;
            --border: #E2E8F0;
            --input-border: #CBD5E1;
            --text-primary: #111827;
            --text-secondary: #475569;
            --primary: #2563EB;
            --primary-hover: #1D4ED8;
            --error: #DC2626;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: var(--background);
            color: var(--text-primary);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 32px;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
        }

        .login-header {
            text-align: center;
        }

        .login-header h1 {
            margin: 0;
            color: var(--text-primary);
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
        }

        .login-header p {
            margin: 10px 0 0;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .login-form {
            margin-top: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            height: 44px;
            padding: 0 16px;
            border: 1px solid var(--input-border);
            border-radius: 6px;
            outline: none;
            color: var(--text-primary);
            background: #FFFFFF;
            font: inherit;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input::placeholder {
            color: #94A3B8;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }

        .form-input.is-invalid {
            border-color: var(--error);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-input {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 5px;
            color: #64748B;
            background: transparent;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--primary);
            background: #EFF6FF;
        }

        .password-toggle:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;
        }

        .field-error {
            min-height: 18px;
            margin: 6px 0 0;
            color: var(--error);
            font-size: 12px;
            line-height: 1.5;
        }

        .alert-error {
            margin-bottom: 20px;
            padding: 11px 12px;
            border: 1px solid #FECACA;
            border-radius: 6px;
            color: #B91C1C;
            background: #FEF2F2;
            font-size: 13px;
            line-height: 1.5;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 2px 0 24px;
        }

        .remember-me {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
        }

        .remember-me input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .link {
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .sign-in-button {
            width: 100%;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 6px;
            color: #FFFFFF;
            background: var(--primary);
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s ease, opacity 0.2s ease;
        }

        .sign-in-button:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .sign-in-button:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.25);
            outline-offset: 2px;
        }

        .sign-in-button:disabled {
            cursor: not-allowed;
            opacity: 0.75;
        }

        .loading-spinner {
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #FFFFFF;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        .signup-text {
            margin: 24px 0 0;
            color: var(--text-secondary);
            text-align: center;
            font-size: 14px;
        }

        .signup-text .link {
            margin-left: 4px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 22px;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <main class="login-card" aria-labelledby="signin-title">
        <header class="login-header">
            <h1 id="signin-title">EL-Renaissance BimBel
                <hr>Sign In
            </h1>
            <p>Enter your credentials to continue</p>
        </header>

        <form id="loginForm" class="login-form" method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            @if (session('error'))
                <div class="alert-error" role="alert">{{ session('error') }}</div>
            @endif

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-input @error('email') is-invalid @enderror" id="email" name="email"
                    type="email" value="{{ old('email') }}" placeholder="Email" autocomplete="email"
                    aria-describedby="emailError" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" required
                    autofocus>
                <p id="emailError" class="field-error" role="alert">
                    @error('email')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-wrapper">
                    <input class="form-input @error('password') is-invalid @enderror" id="password" name="password"
                        type="password" placeholder="Password" autocomplete="current-password"
                        aria-describedby="passwordError"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" required>

                    <button id="passwordToggle" class="password-toggle" type="button" aria-label="Tampilkan password"
                        aria-pressed="false">
                        <svg id="passwordIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <p id="passwordError" class="field-error" role="alert">
                    @error('password')
                        {{ $message }}
                    @enderror
                </p>
            </div>

            <div class="form-options">
                <label class="remember-me" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <span>Remember me</span>
                </label>


            </div>

            <button id="submitButton" class="sign-in-button" type="submit">
                <span id="buttonText">Sign In</span>
            </button>
        </form>

    </main>

    <script>
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const passwordIcon = document.getElementById('passwordIcon');

        function setFieldError(input, errorElement, message) {
            input.classList.toggle('is-invalid', Boolean(message));
            input.setAttribute('aria-invalid', message ? 'true' : 'false');
            errorElement.textContent = message;
        }

        function validateEmail() {
            const email = emailInput.value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!email) {
                setFieldError(emailInput, emailError, 'Email wajib diisi.');
                return false;
            }

            if (!emailPattern.test(email)) {
                setFieldError(emailInput, emailError, 'Masukkan format email yang valid.');
                return false;
            }

            setFieldError(emailInput, emailError, '');
            return true;
        }

        function validatePassword() {
            if (!passwordInput.value) {
                setFieldError(passwordInput, passwordError, 'Password wajib diisi.');
                return false;
            }

            setFieldError(passwordInput, passwordError, '');
            return true;
        }

        passwordToggle.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'text';

            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.setAttribute('aria-pressed', String(!isVisible));
            passwordToggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');

            passwordIcon.innerHTML = isVisible ?
                '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle>' :
                '<path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 4.2A10.6 10.6 0 0 1 12 4c6.5 0 10 8 10 8a18.4 18.4 0 0 1-3.1 4.2"></path><path d="M6.6 6.6C3.7 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path>';
            passwordInput.focus();
        });

        emailInput.addEventListener('input', validateEmail);
        passwordInput.addEventListener('input', validatePassword);

        loginForm.addEventListener('submit', (event) => {
            const emailValid = validateEmail();
            const passwordValid = validatePassword();

            if (!emailValid || !passwordValid) {
                event.preventDefault();
                (!emailValid ? emailInput : passwordInput).focus();
                return;
            }

            submitButton.disabled = true;
            buttonText.textContent = 'Signing in...';

            const spinner = document.createElement('span');
            spinner.className = 'loading-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            submitButton.prepend(spinner);
        });
    </script>
</body>

</html>
