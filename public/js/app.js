
// Toggle de pestañas: "Contraseña" / "Código temporal"
const tabPassword = document.getElementById('tab-password');
const tabCode = document.getElementById('tab-code');
const formPassword = document.getElementById('form-password');
const formRequest = document.getElementById('form-request');
const formVerify = document.getElementById('form-verify');

function activateTab(tab) {
  if (!formPassword || !formRequest || !formVerify) return;
  if (tab === 'password') {
    formPassword.classList.remove('hidden');
    formRequest.classList.add('hidden');
    formVerify.classList.add('hidden');
    tabPassword?.classList.add('bg-white','text-[#235347]','shadow');
    tabCode?.classList.remove('bg-white','text-[#235347]','shadow');
  } else {
    formPassword.classList.add('hidden');
    formRequest.classList.remove('hidden');
    formVerify.classList.remove('hidden');
    tabCode?.classList.add('bg-white','text-[#235347]','shadow');
    tabPassword?.classList.remove('bg-white','text-[#235347]','shadow');
  }
}
if (tabPassword && tabCode) {
  tabPassword.addEventListener('click', () => activateTab('password'));
  tabCode.addEventListener('click', () => activateTab('code'));
}


// Validaciones básicas de cliente
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', (e) => {
    const email = form.querySelector('input[name="email"]');
    if (email && !/^\S+@\S+\.\S+$/.test(email.value)) {
      e.preventDefault();
      alert('Por favor ingresa un correo válido.');
      return;
    }
    const password = form.querySelector('input[name="password"]');
    if (password && password.value.length < 6) {
      e.preventDefault();
      alert('La contraseña debe tener al menos 6 caracteres.');
      return;
    }
    const code = form.querySelector('input[name="code"]');
    if (code && !/^\d{6}$/.test(code.value)) {
      e.preventDefault();
      alert('Ingresa un código de 6 dígitos.');
      return;
    }
  });
});

// Resend code
const resendBtn = document.getElementById('resend-code');
if (resendBtn) {
  resendBtn.addEventListener('click', () => {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'index.php?action=resend_code';
    const csrf = document.querySelector('input[name="csrf"]');
    if (csrf) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = 'csrf';
      csrfInput.value = csrf.value;
      form.appendChild(csrfInput);
    }
    document.body.appendChild(form);
    form.submit();
  });
}

// Back to login button
const backBtn = document.getElementById('back-btn');
if (backBtn) {
  backBtn.addEventListener('click', () => {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = 'index.php?action=cancel_code';
    const csrf = document.querySelector('input[name="csrf"]');
    if (csrf) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = 'csrf';
      csrfInput.value = csrf.value;
      form.appendChild(csrfInput);
    }
    document.body.appendChild(form);
    form.submit();
  });
}
