(function() {
    // ---------- Registration Form Validation ----------
    const regFormEl = document.querySelector('.reg-form');
    if (regFormEl) {
      const emailEl = regFormEl.querySelector('.email-reg');
      const passEl = regFormEl.querySelector('.password-reg');
      const repeatEl = regFormEl.querySelector('.repeat-reg');
      const emailErr = regFormEl.querySelector('.email-reg-err');
      const passErr = regFormEl.querySelector('.password-reg-err');
      const repeatErr = regFormEl.querySelector('.repeat-reg-err');
  
      function hasNumber(str) {
        return /\d/.test(str);
      }
      function accountExists(email) {
        // Placeholder for AJAX/server-side check
        return false;
      }
  
      // Use the "input" event for immediate feedback
      emailEl.addEventListener('input', () => {
        const emailVal = emailEl.value.trim();
        if (!/^\S+@\S+\.\S+$/.test(emailVal)) {
          emailErr.textContent = "Invalid email format";
        } else {
          emailErr.textContent = "";
          // Optionally, check if the email is already in use
        }
      });
  
      passEl.addEventListener('input', () => {
        const passVal = passEl.value.trim();
        if (passVal.length < 7) {
          passErr.textContent = "Password is too short!";
        } else if (passVal.length > 50) {
          passErr.textContent = "Password is too long!";
        } else if (!hasNumber(passVal)) {
          passErr.textContent = "Password must contain at least one number!";
        } else {
          passErr.textContent = "";
        }
      });
  
      repeatEl.addEventListener('input', () => {
        if (repeatEl.value !== passEl.value) {
          repeatErr.textContent = "Passwords do not match!";
        } else {
          repeatErr.textContent = "";
        }
      });
  
      regFormEl.addEventListener('submit', (e) => {
        e.preventDefault();
        let valid = true;
        const email = emailEl.value.trim();
        const pass = passEl.value.trim();
        const repeat = repeatEl.value.trim();
  
        // Clear previous errors
        emailErr.textContent = "";
        passErr.textContent = "";
        repeatErr.textContent = "";
  
        if (!/^\S+@\S+\.\S+$/.test(email)) {
          emailErr.textContent = "Invalid email format";
          valid = false;
        }
        if (accountExists(email)) {
          emailErr.textContent = "Email already in use";
          valid = false;
        }
        if (pass.length < 7) {
          passErr.textContent = "Password is too short!";
          valid = false;
        } else if (pass.length > 50) {
          passErr.textContent = "Password is too long!";
          valid = false;
        } else if (!hasNumber(pass)) {
          passErr.textContent = "Password must contain at least one number!";
          valid = false;
        }
        if (repeat !== pass) {
          repeatErr.textContent = "Passwords do not match!";
          valid = false;
        }
        if (valid) {
          regFormEl.submit();
        }
      });
    }
  
    // ---------- Login Form Validation ----------
    const loginFormEl = document.querySelector('.login-form');
    if (loginFormEl) {
      const loginEmailEl = loginFormEl.querySelector('.email-input');
      const loginPassEl = loginFormEl.querySelector('.password-input');
      // These error elements should exist as per your HTML
      const emailError = loginFormEl.querySelector('.email-error');
      const passwordError = loginFormEl.querySelector('.password-error');
  
      // Switch "change" to "input" for real-time validation
      if (loginEmailEl) {
        loginEmailEl.addEventListener('input', () => {
          const emailVal = loginEmailEl.value.trim();
          if (!/^\S+@\S+\.\S+$/.test(emailVal)) {
            if (emailError) emailError.textContent = "Invalid email format";
          } else {
            if (emailError) emailError.textContent = "";
          }
        });
      }
  
      if (loginPassEl) {
        loginPassEl.addEventListener('input', () => {
          const passVal = loginPassEl.value.trim();
          if (passVal.length < 7) {
            if (passwordError) passwordError.textContent = "Password is too short!";
          } else if (passVal.length > 50) {
            if (passwordError) passwordError.textContent = "Password is too long!";
          } else if (!/\d/.test(passVal)) {
            if (passwordError) passwordError.textContent = "Password must contain at least one number!";
          } else {
            if (passwordError) passwordError.textContent = "";
          }
        });
      }
  
      loginFormEl.addEventListener('submit', (e) => {
        let valid = true;
        const emailVal = loginEmailEl.value.trim();
        const passVal = loginPassEl.value.trim();
  
        if (emailError) emailError.textContent = "";
        if (passwordError) passwordError.textContent = "";
  
        if (!/^\S+@\S+\.\S+$/.test(emailVal)) {
          if (emailError) emailError.textContent = "Invalid email format";
          valid = false;
        }
        if (passVal.length < 7) {
          if (passwordError) passwordError.textContent = "Password is too short!";
          valid = false;
        } else if (passVal.length > 50) {
          if (passwordError) passwordError.textContent = "Password is too long!";
          valid = false;
        } else if (!/\d/.test(passVal)) {
          if (passwordError) passwordError.textContent = "Password must contain at least one number!";
          valid = false;
        }
        if (!valid) {
          e.preventDefault();
        }
      });
    }
  })();
  