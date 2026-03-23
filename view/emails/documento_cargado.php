<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documento cargado</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f5f8ea;
      font-family: Arial, Helvetica, sans-serif;
      color: #334155;
    }

    table {
      border-collapse: collapse;
    }

    .wrapper {
      width: 100%;
      background: linear-gradient(180deg, #fffdf4 0%, #f3f8e5 100%);
      padding: 32px 14px;
    }

    .card {
      width: 100%;
      max-width: 640px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #dce8ba;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 18px 40px rgba(117, 144, 61, 0.14);
    }

    .hero {
      background: linear-gradient(135deg, #a8cf45 0%, #40b554 100%);
      padding: 28px 32px;
      color: #ffffff;
    }

    .eyebrow {
      margin: 0 0 8px;
      font-size: 12px;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      opacity: 0.9;
    }

    .title {
      margin: 0;
      font-size: 28px;
      line-height: 1.2;
      font-weight: 700;
    }

    .hero-copy {
      margin: 10px 0 0;
      font-size: 14px;
      line-height: 1.7;
      color: rgba(255, 255, 255, 0.95);
    }

    .content {
      padding: 30px 32px 26px;
    }

    .greeting {
      margin: 0 0 16px;
      font-size: 16px;
      line-height: 1.7;
      color: #475569;
    }

    .summary-box {
      margin: 0 0 22px;
      padding: 20px;
      border: 1px solid #e5edcb;
      border-radius: 18px;
      background: linear-gradient(180deg, #fffef8 0%, #f8fbef 100%);
    }

    .summary-title {
      margin: 0 0 8px;
      font-size: 15px;
      font-weight: 700;
      color: #516630;
    }

    .summary-text {
      margin: 0;
      font-size: 14px;
      line-height: 1.7;
      color: #5b6470;
    }

    .details {
      width: 100%;
      margin: 0 0 18px;
      border: 1px solid #e7efd0;
      border-radius: 18px;
      overflow: hidden;
    }

    .details td {
      padding: 14px 16px;
      font-size: 14px;
      border-bottom: 1px solid #edf3dc;
    }

    .details tr:last-child td {
      border-bottom: none;
    }

    .details-label {
      width: 32%;
      font-weight: 700;
      color: #63713f;
      background: #fbfdf4;
    }

    .details-value {
      color: #334155;
      background: #ffffff;
    }

    .pill {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(64, 181, 84, 0.12);
      color: #44753c;
      font-weight: 700;
      font-size: 12px;
      letter-spacing: 0.04em;
    }

    .footer {
      padding: 0 32px 28px;
      font-size: 12px;
      line-height: 1.7;
      color: #7c8593;
    }

    .brand {
      color: #54812e;
      font-weight: 700;
    }

    @media only screen and (max-width: 640px) {
      .hero,
      .content,
      .footer {
        padding-left: 22px !important;
        padding-right: 22px !important;
      }

      .title {
        font-size: 24px !important;
      }

      .details td {
        display: block;
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <table role="presentation" width="100%">
      <tr>
        <td align="center">
          <table role="presentation" class="card">
            <tr>
              <td class="hero">
                <p class="eyebrow">Sistema EDUMAS</p>
                <h1 class="title">Documento cargado correctamente</h1>
                <p class="hero-copy">El archivo PDF de tu documento ya fue registrado en la plataforma y se encuentra disponible en el sistema.</p>
              </td>
            </tr>
            <tr>
              <td class="content">
                <p class="greeting">Hola, <span class="brand"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>.</p>

                <div class="summary-box">
                  <p class="summary-title">Confirmacion de carga</p>
                  <p class="summary-text">Te notificamos que el archivo PDF asociado a tu documento fue cargado exitosamente. A continuacion puedes ver el resumen del registro:</p>
                </div>

                <table role="presentation" class="details">
                  <tr>
                    <td class="details-label">Numero</td>
                    <td class="details-value"><?= htmlspecialchars($emailDoc['numero'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <td class="details-label">Nombre</td>
                    <td class="details-value"><?= htmlspecialchars($emailDoc['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                  <tr>
                    <td class="details-label">Area</td>
                    <td class="details-value">
                      <?= htmlspecialchars($emailDoc['area_nombre'], ENT_QUOTES, 'UTF-8') ?>
                      <span class="pill"><?= htmlspecialchars($emailDoc['area_codigo'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                  </tr>
                  <tr>
                    <td class="details-label">Fecha</td>
                    <td class="details-value"><?= htmlspecialchars($emailDoc['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td class="footer">
                Este es un mensaje automatico de <span class="brand">EDUMAS</span> para confirmar el cargue del documento. Por favor, no respondas directamente a este correo.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
