<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Wilayah Kerja - BKK Banjarmasin</title>
    <link rel="stylesheet" href="assets/css/style-tamu.css">
    <style>
        :root {
            --navy: #1E3A8A;
            --navy-deep: #172554;
            --blue: #2563EB;
            --blue-soft: #DBEAFE;
            --sky: #0EA5E9;
            --ink: #0F172A;
            --muted: #64748B;
            --line: #E2E8F0;
            --card: #FFFFFF;
        }

        /* ===== Header ===== */
        .welcome-container .icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 6px;
        }
        .welcome-container .icon-wrapper img {
            filter: drop-shadow(0 6px 10px rgba(30, 58, 138, 0.18));
        }
        .welcome-container h1 {
            color: var(--navy);
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .welcome-container .subtitle {
            color: var(--muted);
            line-height: 1.5;
            max-width: 460px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-label {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0 14px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }
        .section-label::before,
        .section-label::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        /* ===== Grid ===== */
        .wilker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            width: 100%;
            margin-top: 0;
        }

        /* ===== Card ===== */
        .btn-wilker {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            color: var(--ink);
            text-decoration: none;
            text-align: left;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        /* garis aksen di sisi kiri */
        .btn-wilker::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--sky), var(--blue));
            opacity: 0.35;
            transition: opacity 0.25s ease, width 0.25s ease;
        }

        .btn-wilker .wk-icon {
            flex: 0 0 auto;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, #EFF6FF, var(--blue-soft));
            border: 1px solid #BFDBFE;
            transition: transform 0.25s ease, background 0.25s ease;
        }

        .btn-wilker .wk-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }
        .btn-wilker .wk-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.25;
        }
        .btn-wilker .wk-type {
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
        }

        .btn-wilker .wk-arrow {
            flex: 0 0 auto;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--blue);
            background: #F1F5F9;
            transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
        }

        .btn-wilker:hover,
        .btn-wilker:focus-visible {
            transform: translateY(-4px);
            border-color: #93C5FD;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.16);
            outline: none;
        }
        .btn-wilker:hover::before,
        .btn-wilker:focus-visible::before {
            opacity: 1;
            width: 6px;
        }
        .btn-wilker:hover .wk-icon,
        .btn-wilker:focus-visible .wk-icon {
            transform: scale(1.08) rotate(-4deg);
            background: linear-gradient(135deg, var(--blue-soft), #BAE6FD);
        }
        .btn-wilker:hover .wk-arrow,
        .btn-wilker:focus-visible .wk-arrow {
            background: var(--blue);
            color: #fff;
            transform: translateX(3px);
        }
        .btn-wilker:active {
            transform: translateY(-1px);
        }

        /* ===== Card induk (utama) ===== */
        .btn-wilker.induk {
            grid-column: 1 / -1;
            padding: 22px 24px;
            gap: 18px;
            border: none;
            background: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy) 55%, var(--blue) 100%);
            box-shadow: 0 10px 24px rgba(30, 58, 138, 0.3);
        }
        .btn-wilker.induk::before {
            display: none;
        }
        /* lingkaran dekoratif */
        .btn-wilker.induk::after {
            content: "";
            position: absolute;
            right: -40px;
            top: -50px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }
        .btn-wilker.induk .wk-icon {
            width: 58px;
            height: 58px;
            font-size: 28px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
        }
        .btn-wilker.induk .wk-name {
            color: #fff;
            font-size: 18px;
        }
        .btn-wilker.induk .wk-type {
            color: #BFDBFE;
            font-size: 13px;
        }
        .btn-wilker.induk .wk-arrow {
            width: 34px;
            height: 34px;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            position: relative;
            z-index: 1;
        }
        .btn-wilker.induk:hover,
        .btn-wilker.induk:focus-visible {
            border: none;
            box-shadow: 0 16px 32px rgba(30, 58, 138, 0.38);
        }
        .btn-wilker.induk:hover .wk-icon,
        .btn-wilker.induk:focus-visible .wk-icon {
            background: rgba(255, 255, 255, 0.26);
        }
        .btn-wilker.induk:hover .wk-arrow,
        .btn-wilker.induk:focus-visible .wk-arrow {
            background: #fff;
            color: var(--navy);
        }

        /* ===== Card bandara (aksen biru langit) ===== */
        .btn-wilker.bandara::before {
            background: linear-gradient(180deg, #38BDF8, #0284C7);
        }

        /* ===== Footer ===== */
        .wilker-footer {
            margin-top: 26px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        /* ===== Responsif ===== */
        @media (max-width: 520px) {
            .wilker-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .btn-wilker {
                padding: 14px 16px;
            }
            .btn-wilker.induk {
                padding: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn-wilker,
            .btn-wilker *,
            .btn-wilker::before {
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container" style="max-width: 720px; padding: 40px 35px;">
        <div class="icon-wrapper">
            <img src="assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 100px; height: auto; object-fit: contain; margin-bottom: 5px;">
        </div>
        <h1>PILIH WILAYAH KERJA</h1>
        <p class="subtitle" style="margin-bottom: 10px;">Buku Tamu Digital Balai Kekarantinaan Kesehatan Kelas I Banjarmasin</p>

        <div class="wilker-grid">
            <a href="welcome.php?wilker=BKK Induk Banjarmasin" class="btn-wilker induk">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">BKK Induk Banjarmasin</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Bandara Syamsudin Noor" class="btn-wilker bandara">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Bandara Syamsudin Noor</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Kintap" class="btn-wilker">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Kintap</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Sungai Puting" class="btn-wilker">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Sungai Puting</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Satui" class="btn-wilker">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Satui</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Kotabaru" class="btn-wilker">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Kotabaru</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>

            <a href="welcome.php?wilker=Batulicin" class="btn-wilker">
                <span class="wk-icon">🏢</span>
                <span class="wk-text">
                    <span class="wk-name">Batulicin</span>
                </span>
                <span class="wk-arrow">➜</span>
            </a>
        </div>

        <p class="wilker-footer">Balai Kekarantinaan Kesehatan Kelas I Banjarmasin<br>Code By : Muhammad Zailani</p>
    </div>
</body>
</html>