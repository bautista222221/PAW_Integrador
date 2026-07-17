<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Certificado') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Great+Vibes&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --cert-gold: #c9a84c;
            --cert-gold-dark: #a67c2e;
            --cert-dark: #1a1f2e;
            --cert-darker: #0f1320;
            --cert-text: #e8e0d0;
            --cert-text-muted: #9ca3af;
            --cert-accent: #14b8a6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--cert-darker);
            font-family: 'Roboto', sans-serif;
            color: var(--cert-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* ====== CERTIFICATE CARD ====== */
        .cert-wrapper {
            max-width: 900px;
            width: 100%;
        }

        .cert-card {
            position: relative;
            background: linear-gradient(145deg, #1f2937 0%, #111827 100%);
            border: 2px solid var(--cert-gold);
            border-radius: 16px;
            padding: 3.5rem 4rem;
            box-shadow:
                0 0 40px rgba(201, 168, 76, 0.08),
                0 25px 50px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }

        /* Decorative corner ornaments */
        .cert-card::before,
        .cert-card::after {
            content: '✦';
            position: absolute;
            font-size: 1.5rem;
            color: var(--cert-gold);
            opacity: 0.5;
        }
        .cert-card::before { top: 1.5rem; left: 1.5rem; }
        .cert-card::after { bottom: 1.5rem; right: 1.5rem; }

        .cert-corner-tr, .cert-corner-bl {
            position: absolute;
            font-size: 1.5rem;
            color: var(--cert-gold);
            opacity: 0.5;
        }
        .cert-corner-tr { top: 1.5rem; right: 1.5rem; }
        .cert-corner-bl { bottom: 1.5rem; left: 1.5rem; }

        /* Inner decorative border */
        .cert-inner-border {
            border: 1px solid rgba(201, 168, 76, 0.2);
            border-radius: 10px;
            padding: 2.5rem 3rem;
            text-align: center;
        }

        /* Logo / Platform icon */
        .cert-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .cert-logo i {
            font-size: 2rem;
            color: var(--cert-accent);
        }

        .cert-logo span {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--cert-text-muted);
        }

        /* Heading */
        .cert-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--cert-gold), #e8d48b, var(--cert-gold-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .cert-subheading {
            font-size: 0.95rem;
            color: var(--cert-text-muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 2rem;
        }

        /* Divider */
        .cert-divider {
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cert-gold), transparent);
            margin: 1.5rem auto;
        }

        /* Student Name */
        .cert-otorga {
            font-size: 0.9rem;
            color: var(--cert-text-muted);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .cert-student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            color: var(--cert-gold);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .cert-divider-thin {
            width: 200px;
            height: 1px;
            background: var(--cert-gold);
            opacity: 0.4;
            margin: 0.5rem auto 2rem;
        }

        /* Course info */
        .cert-body-text {
            font-size: 1rem;
            color: var(--cert-text);
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto 1.5rem;
        }

        .cert-course-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--cert-accent);
            font-size: 1.15rem;
        }

        /* Score badge */
        .cert-score-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(20, 184, 166, 0.1);
            border: 1px solid rgba(20, 184, 166, 0.3);
            border-radius: 40px;
            padding: 0.5rem 1.5rem;
            margin: 1rem auto;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--cert-accent);
        }

        .cert-score-badge i {
            font-size: 1.2rem;
        }

        /* Footer details */
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cert-footer-item {
            text-align: center;
        }

        .cert-footer-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--cert-text-muted);
            margin-bottom: 0.25rem;
        }

        .cert-footer-value {
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cert-text);
        }

        .cert-footer-signature {
            font-family: 'Great Vibes', cursive;
            font-size: 1.8rem;
            color: var(--cert-gold);
        }

        /* ====== ACTIONS (not printed) ====== */
        .cert-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .cert-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.8rem;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .cert-btn-primary {
            background: linear-gradient(135deg, var(--cert-accent), #0d9488);
            color: white;
        }
        .cert-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
        }

        .cert-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--cert-text);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .cert-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        /* ====== PRINT STYLES ====== */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .cert-card {
                border-color: #b8960f;
                box-shadow: none;
                background: white;
                padding: 2rem 3rem;
            }

            .cert-heading {
                background: none;
                -webkit-text-fill-color: var(--cert-gold-dark);
                color: var(--cert-gold-dark);
            }

            .cert-student-name { color: #333; }
            .cert-text, .cert-body-text { color: #333; }
            .cert-text-muted, .cert-otorga, .cert-subheading, .cert-footer-label { color: #666; }
            .cert-inner-border { border-color: rgba(201, 168, 76, 0.35); }
            .cert-actions { display: none !important; }
            .cert-score-badge { border-color: #0d9488; color: #0d9488; background: rgba(20, 184, 166, 0.05); }

            .cert-wrapper {
                max-width: 100%;
            }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .cert-card { padding: 2rem 1.5rem; }
            .cert-inner-border { padding: 1.5rem 1rem; }
            .cert-heading { font-size: 1.6rem; letter-spacing: 0.1em; }
            .cert-student-name { font-size: 2.2rem; }
            .cert-footer { flex-direction: column; gap: 1.5rem; align-items: center; }
        }
    </style>
</head>
<body>

    <div class="cert-wrapper">
        <div class="cert-card">
            <span class="cert-corner-tr">✦</span>
            <span class="cert-corner-bl">✦</span>

            <div class="cert-inner-border">
                <!-- Logo -->
                <div class="cert-logo">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Plataforma de Aprendizaje Digital</span>
                </div>

                <!-- Heading -->
                <h1 class="cert-heading">Certificado</h1>
                <p class="cert-subheading">de finalización y aprobación</p>

                <div class="cert-divider"></div>

                <!-- Student -->
                <p class="cert-otorga">Se otorga a</p>
                <h2 class="cert-student-name"><?= htmlspecialchars($certificado['nombre_estudiante']) ?></h2>
                <div class="cert-divider-thin"></div>

                <!-- Course -->
                <p class="cert-body-text">
                    Por haber completado satisfactoriamente y aprobado el curso
                    <br><span class="cert-course-name">"<?= htmlspecialchars($certificado['curso_titulo']) ?>"</span>
                </p>

                <!-- Score -->
                <div class="cert-score-badge">
                    <i class="fa-solid fa-star"></i>
                    Calificación: <?= htmlspecialchars($certificado['nota']) ?>/10
                </div>

                <div class="cert-divider"></div>

                <!-- Footer -->
                <div class="cert-footer">
                    <div class="cert-footer-item">
                        <p class="cert-footer-label">Fecha de emisión</p>
                        <p class="cert-footer-value"><?= htmlspecialchars($certificado['fecha_aprobado']) ?></p>
                    </div>
                    <div class="cert-footer-item">
                        <p class="cert-footer-label">Firmado por</p>
                        <p class="cert-footer-signature">PAD</p>
                    </div>
                    <div class="cert-footer-item">
                        <p class="cert-footer-label">ID Certificado</p>
                        <p class="cert-footer-value">PAD-<?= str_pad($certificado['curso_id'], 4, '0', STR_PAD_LEFT) ?>-<?= date('Y') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="cert-actions">
            <button class="cert-btn cert-btn-primary" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
            </button>
            <a class="cert-btn cert-btn-secondary" href="/curso?id=<?= urlencode($certificado['curso_id']) ?>">
                <i class="fa-solid fa-arrow-left"></i> Volver al curso
            </a>
        </div>
    </div>

</body>
</html>
