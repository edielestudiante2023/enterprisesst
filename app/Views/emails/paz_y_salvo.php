<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paz y Salvo por Todo Concepto — <?= esc($nombre_cliente) ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;color:#1a1a2e;">

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:32px 0;">
  <tr>
    <td align="center">
      <!-- Contenedor principal -->
      <table width="640" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:640px;width:100%;">

        <!-- ── Encabezado ── -->
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#2563eb 100%);padding:36px 40px 28px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <p style="margin:0;font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#93c5fd;font-weight:600;">
                    Sistema de Gestión SST
                  </p>
                  <h1 style="margin:6px 0 0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">
                    Cycloid Talent SAS
                  </h1>
                  <p style="margin:4px 0 0;font-size:13px;color:#bfdbfe;">
                    NIT: 901.653.912 &nbsp;·&nbsp; cycloidtalent.com
                  </p>
                </td>
                <td align="right" valign="middle">
                  <!-- Sello visual PAZ Y SALVO -->
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.3);
                                 border-radius:10px;padding:10px 18px;text-align:center;">
                        <p style="margin:0;font-size:10px;letter-spacing:.12em;text-transform:uppercase;
                                  color:#93c5fd;font-weight:600;">Certificado</p>
                        <p style="margin:4px 0 0;font-size:14px;font-weight:700;color:#ffffff;">
                          PAZ Y SALVO
                        </p>
                        <p style="margin:2px 0 0;font-size:10px;color:#bfdbfe;">por todo concepto</p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- ── Cuerpo ── -->
        <tr>
          <td style="padding:36px 40px 0;">

            <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.7;">
              Estimados señores,
            </p>

            <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.7;">
              Por medio de la presente, <strong>Cycloid Talent SAS</strong> certifica que
              <strong><?= esc($nombre_cliente) ?></strong>, identificado(a) con NIT <strong><?= esc($nit_cliente) ?></strong>,
              con sede en <strong><?= esc($ciudad_cliente) ?></strong>, se encuentra a
              <strong>paz y salvo por todo concepto</strong> en relación a los servicios de asesoría en
              Seguridad y Salud en el Trabajo (SST) prestados durante su vinculación, la cual tuvo
              vigencia desde el <strong><?= esc($fecha_ingreso) ?></strong> hasta el <strong><?= esc($fecha_emision_corta) ?></strong>.
            </p>

            <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.7;">
              Al momento de la emisión de este documento, se verificó el cierre total de todas
              las actividades gestionadas, a saber:
            </p>

            <!-- Tabla de verificación -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;margin-bottom:28px;">
              <tr style="background:#f0fdf4;">
                <td style="padding:14px 18px;border-bottom:1px solid #d1fae5;">
                  <span style="font-size:18px;">✅</span>
                  <span style="font-size:14px;font-weight:600;color:#065f46;margin-left:10px;">
                    Plan de Trabajo Anual (PTA)
                  </span>
                  <p style="margin:4px 0 0 28px;font-size:13px;color:#6b7280;">
                    Todas las actividades cerradas
                  </p>
                </td>
              </tr>
              <tr style="background:#f0fdf4;">
                <td style="padding:14px 18px;border-bottom:1px solid #d1fae5;">
                  <span style="font-size:18px;">✅</span>
                  <span style="font-size:14px;font-weight:600;color:#065f46;margin-left:10px;">
                    Cronograma de Capacitación
                  </span>
                  <p style="margin:4px 0 0 28px;font-size:13px;color:#6b7280;">
                    Todas las sesiones ejecutadas o cerradas
                  </p>
                </td>
              </tr>
              <tr style="background:#f0fdf4;">
                <td style="padding:14px 18px;">
                  <span style="font-size:18px;">✅</span>
                  <span style="font-size:14px;font-weight:600;color:#065f46;margin-left:10px;">
                    Pendientes
                  </span>
                  <p style="margin:4px 0 0 28px;font-size:13px;color:#6b7280;">
                    Sin ítems abiertos
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.7;">
              Este paz y salvo fue emitido el día <strong><?= esc($fecha_emision_completa) ?></strong>
              por el consultor <strong><?= esc($nombre_consultor) ?></strong>, asignado a la cuenta.
            </p>

            <p style="margin:0 0 32px;font-size:15px;color:#374151;line-height:1.7;">
              Agradecemos la confianza depositada en nuestros servicios y quedamos atentos ante
              cualquier requerimiento futuro.
            </p>

          </td>
        </tr>

        <!-- ── Firma ── -->
        <tr>
          <td style="padding:0 40px 36px;">
            <table cellpadding="0" cellspacing="0"
                   style="border-left:3px solid #2563eb;padding-left:16px;">
              <tr>
                <td>
                  <p style="margin:0;font-size:15px;font-weight:600;color:#1e3a5f;">
                    Cordialmente,
                  </p>
                  <p style="margin:6px 0 2px;font-size:16px;font-weight:700;color:#0f172a;">
                    Cycloid Talent SAS
                  </p>
                  <p style="margin:0;font-size:13px;color:#6b7280;">
                    NIT: 901.653.912 &nbsp;·&nbsp; cycloidtalent.com
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- ── Footer ── -->
        <tr>
          <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:18px 40px;">
            <p style="margin:0;font-size:11px;color:#9ca3af;text-align:center;line-height:1.6;">
              Mensaje generado automáticamente por el sistema de gestión SST de Cycloid Talent.<br>
              Por favor no responder directamente a este correo.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
