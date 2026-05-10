<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f0f7ff; color: #1e3a8a; }
        .wrapper { max-width: 560px; margin: 32px auto; padding: 0 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(30,58,138,0.10); }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: #bfdbfe; font-size: 13px; margin-top: 4px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 16px; }
        .greeting span { font-weight: 600; color: #1e3a8a; }
        .info { font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 28px; }
        .code-box { background: #eff6ff; border: 2px dashed #3b82f6; border-radius: 12px; padding: 24px 16px; text-align: center; margin-bottom: 28px; }
        .code-label { font-size: 11px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .code { font-size: 42px; font-weight: 800; letter-spacing: 10px; color: #1e3a8a; font-family: 'Courier New', monospace; }
        .code-note { font-size: 12px; color: #9ca3af; margin-top: 10px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 12px 16px; margin-bottom: 24px; }
        .warning p { font-size: 13px; color: #92400e; line-height: 1.5; }
        .matricula-info { background: #f0f9ff; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
        .matricula-info p { font-size: 13px; color: #0369a1; }
        .matricula-info strong { color: #1e3a8a; }
        .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }
        .footer-text { font-size: 12px; color: #9ca3af; line-height: 1.6; text-align: center; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { font-size: 11px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>🎓 Sistema de Tutoría Académica</h1>
                <p>Universidad Autónoma del Carmen</p>
            </div>
            <div class="body">
                <p class="greeting">Hola, <span>alumno con matrícula {{ $matricula }}</span> 👋</p>
                <p class="info">
                    Recibiste este correo porque solicitaste crear una cuenta en el
                    <strong>Sistema de Tutoría Académica</strong>. Usa el siguiente código
                    para verificar que esta dirección de correo te pertenece.
                </p>

                <div class="code-box">
                    <p class="code-label">Tu código de verificación</p>
                    <p class="code">{{ $codigo }}</p>
                    <p class="code-note">⏳ Válido por 10 minutos</p>
                </div>

                <div class="matricula-info">
                    <p>🎫 Matrícula: <strong>{{ $matricula }}</strong></p>
                </div>

                <div class="warning">
                    <p>
                        <strong>¿No solicitaste esto?</strong> Ignora este correo.
                        Nadie puede completar el registro sin acceso a esta bandeja.
                        Tu cuenta actual permanece segura.
                    </p>
                </div>

                <div class="divider"></div>
                <p class="footer-text">
                    Por seguridad, nunca compartas este código con nadie.<br>
                    El personal del sistema jamás te pedirá tu código por teléfono o chat.
                </p>
            </div>
            <div class="footer">
                <p>© {{ date('Y') }} Sistema de Tutoría · UNACAR · Generado automáticamente</p>
            </div>
        </div>
    </div>
</body>
</html>