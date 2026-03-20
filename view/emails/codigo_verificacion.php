<?php /* Email HTML para envío de código temporal */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Código de verificación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light only">
  <style>
    /* Reset básico para correos */
    body { 
      background-color: #f1f5f9; /* Slate-100: fondo pastel profesional */
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
      margin: 0; 
      padding: 0; 
      -webkit-font-smoothing: antialiased;
    }
    
    .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 40px 0; }
    
    .container { 
      max-width: 500px; 
      margin: 0 auto; 
      background-color: #ffffff; 
      border-radius: 24px; 
      overflow: hidden; 
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      border: 1px solid #e2e8f0;
    }
    
    /* Header con el azul institucional de la imagen */
    .header { 
      background: linear-gradient(135deg, #3366cc 0%, #1e40af 100%); 
      color: #ffffff; 
      padding: 30px 40px; 
      text-align: center;
    }
    
    .header h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
    
    .content { padding: 40px; color: #334155; line-height: 1.6; }
    
    .content p { margin-top: 0; margin-bottom: 16px; font-size: 15px; }
    
    /* El código con el verde de "Pague Aquí" de la imagen */
    .code-box { 
      margin: 30px 0;
      padding: 20px;
      background-color: #f0fdf4; /* Verde pastel muy claro */
      border: 2px dashed #2ecc71; /* Verde institucional */
      border-radius: 16px;
      text-align: center;
    }
    
    .code { 
      font-size: 36px; 
      letter-spacing: 8px; 
      font-weight: 800; 
      color: #15803d; 
      margin: 0;
      font-family: 'Courier New', monospace; /* Para asegurar legibilidad de caracteres */
    }
    
    .info-box {
      background-color: #f8fafc;
      border-radius: 12px;
      padding: 15px;
      margin-top: 20px;
    }

    .muted { color: #64748b; font-size: 13px; margin: 0; }
    
    .footer { 
      padding: 24px 40px; 
      background-color: #f8fafc;
      border-top: 1px solid #f1f5f9;
      color: #94a3b8; 
      font-size: 12px; 
      text-align: center;
    }
    
    .brand-accent { color: #3366cc; font-weight: 600; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <h1>Verificacion de Identidad</h1>
      </div>
      
      <div class="content">
        <p>Hola, <span class="brand-accent"><?= htmlspecialchars($emailUsername) ?></span>.</p>
        <p>Has solicitado un acceso seguro a nuestra plataforma. Utiliza el siguiente codigo temporal para completar tu ingreso:</p>
        
        <div class="code-box">
          <div class="code"><?= $emailCode ?></div>
        </div>
        
        <div class="info-box">
          <p class="muted">
            <strong>Validez:</strong> Este codigo caduca el <span style="color: #334155;"><?= htmlspecialchars($displayExpiry) ?></span>.<br>
            <strong>Seguridad:</strong> Por tu proteccion, este codigo es de uso único.
          </p>
        </div>
        
        <p class="muted" style="margin-top: 25px; font-style: italic;">
          Si no has solicitado este acceso, puedes ignorar este mensaje de forma segura. Tu cuenta sigue protegida.
        </p>
      </div>
      
      <div class="footer">
        Este es un mensaje automático de <strong>EDUMAS - Portal Institucional</strong>.<br>
        Por favor, no respondas a este correo electronico.
      </div>
    </div>
  </div>
</body>
</html>