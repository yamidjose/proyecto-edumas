<?php // Variables: $csrf, $flash, $awaiting ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login | Verificación Segura</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="min-h-screen  bg-gradient-to-tr from-[#f8fafc] via-[#f1f5f9] to-[#ecfdf5] flex items-center justify-center py-12 px-4">
  
  <div class="w-[400px]  max-w-md transition-all duration-500 ease-in-out">
    
    

    <div class="bg-white/80 backdrop-blur-xl rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] p-5 border border-white">
      <div class="text-center mb-8">
      <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Acceso Institucional</h1>
      <p class="mt-1 text-slate-500 text-sm font-medium uppercase tracking-wider">Portal de Verificación Segura</p>
    </div>
    <?php include __DIR__ . '/partials/flash.php'; ?>

      <form id="form-login" method="post" action="index.php?action=password" class="space-y-6<?= isset($_SESSION['awaiting_code']) ? ' hidden' : '' ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        
        <div class="group space-y-1">
          <label class="block text-sm font-bold text-slate-700 ml-1">Nombre de usuario</label>
          <input 
            type="text" 
            name="username" 
            required 
            class="w-full px-5 py-2 bg-slate-50 border-2 border-slate-100 rounded-md focus:border-[#3366cc] focus:bg-white focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-300 font-medium placeholder-slate-400" 
            placeholder="Ej: usuario123"
          >
        </div>

        <div class="group space-y-1">
          <label class="block text-sm font-bold text-slate-700 ml-1">Contraseña</label>
          <input 
            type="password" 
            name="password" 
            minlength="6" 
            required 
            class="w-full px-5 py-2 bg-slate-50 border-2 border-slate-100 rounded-md focus:border-[#3366cc] focus:bg-white focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-300 font-medium" 
            placeholder="••••••••"
          >
        </div>

        <div class="group space-y-1">
          <label class="block text-sm font-bold text-slate-700 ml-1">Correo electrónico</label>
          <input 
            type="email" 
            name="email" 
            required 
            class="w-full px-5 py-2 bg-slate-50 border-2 border-slate-100 rounded-md focus:border-[#3366cc] focus:bg-white focus:ring-4 focus:ring-blue-50 focus:outline-none transition duration-300 font-medium placeholder-slate-400" 
            placeholder="nombre@ejemplo.com"
          >
        </div>

        <button 
          type="submit" 
          class="w-full px-6 py-2 bg-gradient-to-r from-[#2ecc71] to-[#27ae60] text-white font-bold rounded-md hover:shadow-[0_10px_20px_rgba(46,204,113,0.3)] hover:-translate-y-0.5 transition-all duration-300 active:scale-95 mt-4"
        >
          Iniciar Sesión
        </button>
      </form>

      <div id="code-section" class="space-y-2<?= !isset($_SESSION['awaiting_code']) ? ' hidden' : '' ?>">
        <div class="bg-blue-50/50 border-l-4 border-[#3366cc] p-5 rounded-2xl">
          <div class="flex items-center gap-2 mb-1">
             <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
             <p class="text-sm text-blue-900 font-bold">Usuario confirmado</p>
          </div>
          <p class="text-xs text-blue-700 leading-relaxed">Hemos enviado un código de 6 dígitos a su correo electrónico institucional.</p>
        </div>

        <form id="form-verify" method="post" action="index.php?action=verify_code" class="space-y-6">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['awaiting_code']['email'] ?? '') ?>">
          <input type="hidden" name="username" value="<?= htmlspecialchars($_SESSION['awaiting_code']['username'] ?? '') ?>">
          
          <div class="space-y-3">
            <label class="block text-center text-sm font-bold text-slate-700">Código de verificación</label>
            <input 
              type="text" 
              name="code" 
              inputmode="numeric" 
              pattern="\d{6}" 
              required 
              class="w-full px-4 py-2 bg-slate-50 border-2 border-slate-100 rounded-md focus:border-[#2ecc71] focus:bg-white focus:outline-none transition duration-300 font-bold text-3xl text-center tracking-[0.5em] text-slate-800" 
              placeholder="000000"
              maxlength="6"
            >
            <p class="text-[10px] text-slate-400 text-center uppercase font-bold tracking-widest">Válido por 5 minutos</p>
          </div>

          <button 
            type="submit" 
            class="w-full px-6 py-2 bg-gradient-to-r from-[#2ecc71] to-[#27ae60] text-white font-bold rounded-md hover:shadow-[0_10px_20px_rgba(46,204,113,0.3)] hover:-translate-y-0.5 transition-all duration-300 active:scale-95"
          >
            Verificar Identidad
          </button>
        </form>

        <div class="grid grid-cols-1 gap-3 pt-4 border-t border-slate-100">
          <button 
            id="resend-code" 
            type="button" 
            class="w-full px-4 py-3 text-xs font-bold text-[#27ae60] bg-green-50 rounded-xl hover:bg-green-100 transition duration-300 flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Reenviar Codigo
          </button>
          <button 
            id="back-btn" 
            type="button" 
            class="w-full px-4 py-3 text-xs font-bold text-slate-400 hover:text-slate-600 transition duration-300"
          >
            ← Volver al inicio de sesion
          </button>
        </div>
      </div>

      <p class="mt-4 text-center text-[10px] text-slate-400 font-medium leading-relaxed">
        SISTEMA DE SEGURIDAD PROTEGIDO<br>
        © 2026 EDUMAS - Todos los derechos reservados
      </p>
    </div>

    <div class="mt-4 flex items-center justify-center gap-6 opacity-60">
      <div class="flex items-center gap-1.5">
        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 4.946-2.597 9.174-6.5 11.29a11.715 11.715 0 01-1.5.71 11.715 11.715 0 01-1.5-.71C4.596 16.174 2 11.945 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-[11px] font-bold text-slate-600 uppercase tracking-widest">SSL Secure</span>
      </div>
      <div class="h-4 w-px bg-slate-300"></div>
      <span class="text-[11px] font-bold text-slate-600 uppercase tracking-widest">Gov.co</span>
    </div>
  </div>

  <script src="js/app.js"></script>
</body>
</html>