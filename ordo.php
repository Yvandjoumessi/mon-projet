<?php
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$isWordExport = isset($_POST['action']) && $_POST['action'] === 'word';

$date_patient       = $_POST['date_patient'] ?? '';
$nom_patient        = $_POST['nom_patient'] ?? '';
$prescription       = $_POST['prescription'] ?? '';
$nom_prescripteur   = $_POST['nom_prescripteur'] ?? '';

if ($isWordExport) {
    header("Content-Type: application/msword; charset=UTF-8");
    header("Content-Disposition: attachment; filename=ordonnance_konsamba.doc");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnance - CESMAN</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 18mm 16mm 18mm 16mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.12);
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .logo-box {
            width: 120px;
            height: 120px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .center-info {
            flex: 1;
            text-align: center;
            padding-top: 5px;
        }

        .center-info h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .center-info .phone {
            margin-top: 8px;
            font-size: 18px;
            font-weight: bold;
        }

        .separator {
            border: none;
            border-top: 2px solid #000;
            margin: 14px 0 22px;
        }

        .info-block {
            margin-bottom: 10px;
        }

        .info-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 14px 0;
            font-size: 17px;
        }

        .label {
            font-weight: bold;
            min-width: 155px;
        }

        .text-input {
            flex: 1;
            border: none;
            border-bottom: 1.5px solid #000;
            outline: none;
            font-size: 17px;
            padding: 4px 2px;
            background: transparent;
        }

        .title-ordo {
            text-align: center;
            margin: 28px 0 18px;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        .prescription-box {
            margin-top: 12px;
        }

        .prescription-label {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        textarea {
            width: 100%;
            min-height: 420px;
            resize: vertical;
            border: 1.5px solid #000;
            padding: 12px;
            font-size: 16px;
            line-height: 1.8;
            outline: none;
            background:
                repeating-linear-gradient(
                    to bottom,
                    #ffffff 0px,
                    #ffffff 30px,
                    #d9d9d9 31px
                );
        }

        .prescription-print {
            min-height: 420px;
            border: 1.5px solid #000;
            padding: 14px;
            font-size: 16px;
            line-height: 1.9;
            white-space: pre-wrap;
        }

        .bottom-zone {
            margin-top: 55px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 40px;
        }

        .prescripteur-box {
            width: 35%;
        }

        .signature-box {
            width: 35%;
            text-align: center;
        }

        .bottom-title {
            font-weight: bold;
            margin-bottom: 30px;
        }

        .line {
            border-top: 1.5px solid #000;
            width: 100%;
        }

        .controls {
            width: 210mm;
            margin: 20px auto 0;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print {
            background: #0b5ed7;
            color: #fff;
        }

        .btn-word {
            background: #198754;
            color: #fff;
        }

        .btn-reset {
            background: #6c757d;
            color: #fff;
        }

        .note {
            width: 210mm;
            margin: 1px auto 0;
            font-size: 14px;
            color: #444;
            text-align: center;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .controls,
            .note {
                display: none !important;
            }

            .text-input {
                border: none;
                border-bottom: 1px solid #000;
            }

            textarea {
                border: 1px solid #000;
            }
        }
    </style>
</head>
<body>

<form method="post">
    <div class="page">
        <div class="header">
            <div class="logo-box">
                <img src="lg.jpeg" alt="Logo du centre de santé">
            </div>

            <div class="center-info">
                <h1>CENTRE DE SANTE DES MAJORS DE NKONGSAMBA</h1>
                <div class="phone">617 80 77 52</div>
            </div>
        </div>

        <hr class="separator">

        <div class="info-block">
            <div class="info-line">
                <div class="label">Date :</div>
                <?php if ($isWordExport): ?>
                    <div style="flex:1; border-bottom:1.5px solid #000; padding:4px 2px; font-size:17px;">
                        <?= e($date_patient) ?>
                    </div>
                <?php else: ?>
                    <input type="text" name="date_patient" class="text-input" value="<?= e($date_patient) ?>" placeholder="Saisir la date">
                <?php endif; ?>
            </div>

            <div class="info-line">
                <div class="label">Nom du patient :</div>
                <?php if ($isWordExport): ?>
                    <div style="flex:1; border-bottom:1.5px solid #000; padding:4px 2px; font-size:17px;">
                        <?= e($nom_patient) ?>
                    </div>
                <?php else: ?>
                    <input type="text" name="nom_patient" class="text-input" value="<?= e($nom_patient) ?>" placeholder="Saisir le nom du patient">
                <?php endif; ?>
            </div>
        </div>

        <div class="title-ordo">Ordonnance / Prescription</div>

        <div class="prescription-box">
            <div class="prescription-label">Prescription :</div>

            <?php if ($isWordExport): ?>
                <div class="prescription-print"><?= nl2br(e($prescription)) ?></div>
            <?php else: ?>
                <textarea name="prescription" placeholder="Saisir les ordonnances / prescriptions ici..."><?= e($prescription) ?></textarea>
            <?php endif; ?>
        </div>

        <div class="bottom-zone">
            <div class="prescripteur-box">
                <div class="info-line" style="margin:0;">
                    <div class="label" style="min-width:170px;">Nom du prescripteur :</div>
                    <?php if ($isWordExport): ?>
                        <div style="flex:1; border-bottom:1.5px solid #000; padding:4px 2px; font-size:17px;">
                            <?= e($nom_prescripteur) ?>
                        </div>
                    <?php else: ?>
                        <input type="text" name="prescripteur" class="text-input" value="<?= e($nom_prescripteur) ?>" placeholder="">
                    <?php endif; ?>
                </div>
            </div>

            <div class="signature-box">
                <div class="bottom-title">Signature</div>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <?php if (!$isWordExport): ?>
        <div class="controls">
            <button type="button" class="btn btn-print" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
            <button type="submit" name="action" value="word" class="btn btn-word">Exporter en Word</button>
            <button type="reset" class="btn btn-reset">Réinitialiser</button>
        </div>

        <div class="note">
            Pour obtenir le PDF, cliquez sur <strong>Imprimer / Enregistrer en PDF</strong>, puis choisissez <strong>Enregistrer au format PDF</strong>.
        </div>
    <?php endif; ?>
</form>

</body>
</html>