<?php
// ╔══════════════════════════════════════════════════════════╗
//  PHARMAsoft v3 — CESMAN
//  Tables : medicaments | ventes | factures
// ╚══════════════════════════════════════════════════════════╝

// ─── Connexion BDD ──────────────────────────────────────────
$DB_HOST = 'localhost';
$DB_NAME = 'cesman';
$DB_USER = 'root';
$DB_PASS = '';

function getDB() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function sendJSON($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function str_($key, $source = 'POST') {
    $arr = $source === 'GET' ? $_GET : $_POST;
    return trim($arr[$key] ?? '');
}
function int_($key)   { return (int)   ($_POST[$key] ?? 0); }
function float_($key) { return (float) ($_POST[$key] ?? 0); }

// ─── Point d'entrée AJAX ────────────────────────────────────
// Toutes les requêtes AJAX arrivent avec ?ajax=1 dans l'URL
if (isset($_GET['ajax'])) {
    // Désactiver tout buffer de sortie résiduel
    while (ob_get_level()) ob_end_clean();

    try {
        $pdo = getDB();

        // Création automatique des tables si elles n'existent pas
        $pdo->exec("CREATE TABLE IF NOT EXISTS `medicaments` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `id_med`          VARCHAR(50),
            `categorie`       VARCHAR(100),
            `nommed`          VARCHAR(255) NOT NULL,
            `quantite`        INT          NOT NULL DEFAULT 0,
            `prix`            DECIMAL(12,2) NOT NULL DEFAULT 0,
            `emplacement`     VARCHAR(100),
            `date_expiration` DATE,
            `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `ventes` (
            `idventes`      INT AUTO_INCREMENT PRIMARY KEY,
            `nommed`        VARCHAR(255) NOT NULL,
            `qte`           INT          NOT NULL DEFAULT 1,
            `prix_unitaire` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `montant`       DECIMAL(12,2) NOT NULL DEFAULT 0,
            `date`          DATE          NOT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `factures` (
            `idfacture`     INT AUTO_INCREMENT PRIMARY KEY,
            `client`        VARCHAR(255) NOT NULL DEFAULT 'Client',
            `nommed`        VARCHAR(255) NOT NULL,
            `qte`           INT          NOT NULL DEFAULT 1,
            `prix_unitaire` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `montant_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `date_facture`  DATE          NOT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $action = str_('action', 'GET'); // toujours dans l'URL ?ajax=1&action=xxx

        switch ($action) {

            // ════════════════════════════════════
            //  MÉDICAMENTS
            // ════════════════════════════════════

            case 'med_list':
                $rows = $pdo->query("SELECT * FROM medicaments ORDER BY nommed ASC")->fetchAll();
                sendJSON(['ok' => true, 'data' => $rows]);

            case 'med_names':
                $rows = $pdo->query("SELECT nommed, prix, quantite FROM medicaments ORDER BY nommed ASC")->fetchAll();
                sendJSON(['ok' => true, 'data' => $rows]);

            case 'med_get':
                $id = (int)($_GET['id'] ?? 0);
                $st = $pdo->prepare("SELECT * FROM medicaments WHERE id = ?");
                $st->execute([$id]);
                $row = $st->fetch();
                sendJSON($row ? ['ok' => true, 'data' => $row] : ['ok' => false, 'msg' => 'Médicament introuvable.']);

            case 'med_add':
                $nommed = str_('nommed');
                if (!$nommed) sendJSON(['ok' => false, 'msg' => 'Le nom du médicament est obligatoire.']);

                $id_med = str_('id_med');
                if (!$id_med) {
                    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nommed), 0, 3));
                    $id_med = $prefix . '-' . date('ymd') . '-' . rand(100, 999);
                }
                $st = $pdo->prepare("INSERT INTO medicaments
                    (id_med, categorie, nommed, quantite, prix, emplacement, date_expiration)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $dexp = str_('date_expiration') ?: null;
                $st->execute([
                    $id_med,
                    str_('categorie'),
                    $nommed,
                    int_('quantite'),
                    float_('prix'),
                    str_('emplacement'),
                    $dexp
                ]);
                sendJSON(['ok' => true, 'msg' => "Médicament « {$nommed} » ajouté avec succès.", 'id' => $pdo->lastInsertId()]);

            case 'med_update':
                $id = int_('id');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);
                $nommed = str_('nommed');
                if (!$nommed) sendJSON(['ok' => false, 'msg' => 'Le nom est obligatoire.']);
                $dexp = str_('date_expiration') ?: null;
                $st = $pdo->prepare("UPDATE medicaments
                    SET id_med=?, categorie=?, nommed=?, quantite=?, prix=?, emplacement=?, date_expiration=?
                    WHERE id=?");
                $st->execute([
                    str_('id_med'), str_('categorie'), $nommed,
                    int_('quantite'), float_('prix'), str_('emplacement'), $dexp, $id
                ]);
                sendJSON(['ok' => true, 'msg' => "Médicament « {$nommed} » modifié."]);

            case 'med_delete':
                $id = int_('id');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);
                $pdo->prepare("DELETE FROM medicaments WHERE id=?")->execute([$id]);
                sendJSON(['ok' => true, 'msg' => 'Médicament supprimé.']);

            // ════════════════════════════════════
            //  VENTES
            // ════════════════════════════════════

            case 'vente_list':
                $rows = $pdo->query("SELECT * FROM ventes ORDER BY date DESC, idventes DESC")->fetchAll();
                sendJSON(['ok' => true, 'data' => $rows]);

            case 'vente_get':
                $id = (int)($_GET['id'] ?? 0);
                $st = $pdo->prepare("SELECT * FROM ventes WHERE idventes=?");
                $st->execute([$id]);
                $row = $st->fetch();
                sendJSON($row ? ['ok' => true, 'data' => $row] : ['ok' => false, 'msg' => 'Vente introuvable.']);

            case 'vente_add':
                $nommed = str_('nommed');
                $qte    = int_('qte');
                $pu     = float_('prix_unitaire');
                $date   = str_('date') ?: date('Y-m-d');

                if (!$nommed) sendJSON(['ok' => false, 'msg' => 'Le nom du médicament est obligatoire.']);
                if ($qte < 1) sendJSON(['ok' => false, 'msg' => 'La quantité doit être au moins 1.']);

                $montant = $qte * $pu;

                // Vérification et décrémentation du stock
                $chk = $pdo->prepare("SELECT id, quantite FROM medicaments WHERE nommed = ? LIMIT 1");
                $chk->execute([$nommed]);
                $med = $chk->fetch();
                if ($med) {
                    if ((int)$med['quantite'] < $qte) {
                        sendJSON(['ok' => false, 'msg' => "Stock insuffisant pour « {$nommed} ». Disponible : {$med['quantite']}."]);
                    }
                    $pdo->prepare("UPDATE medicaments SET quantite = quantite - ? WHERE id = ?")
                        ->execute([$qte, $med['id']]);
                }

                $st = $pdo->prepare("INSERT INTO ventes (nommed, qte, prix_unitaire, montant, date) VALUES (?,?,?,?,?)");
                $st->execute([$nommed, $qte, $pu, $montant, $date]);
                sendJSON(['ok' => true, 'msg' => "Vente enregistrée — Montant : " . number_format($montant, 0, ',', ' ') . " FCFA.", 'id' => $pdo->lastInsertId()]);

            case 'vente_update':
                $id     = int_('id');
                $nommed = str_('nommed');
                $qte    = int_('qte');
                $pu     = float_('prix_unitaire');
                $date   = str_('date') ?: date('Y-m-d');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);

                // Restituer l'ancien stock
                $old = $pdo->prepare("SELECT nommed, qte FROM ventes WHERE idventes=?");
                $old->execute([$id]);
                $ov = $old->fetch();
                if ($ov) {
                    $pdo->prepare("UPDATE medicaments SET quantite = quantite + ? WHERE nommed = ?")
                        ->execute([$ov['qte'], $ov['nommed']]);
                }

                // Vérifier le nouveau stock
                $chk = $pdo->prepare("SELECT id, quantite FROM medicaments WHERE nommed=? LIMIT 1");
                $chk->execute([$nommed]);
                $med = $chk->fetch();
                if ($med && (int)$med['quantite'] < $qte) {
                    // Remettre l'ancien stock si on annule
                    if ($ov) {
                        $pdo->prepare("UPDATE medicaments SET quantite = quantite - ? WHERE nommed=?")
                            ->execute([$ov['qte'], $ov['nommed']]);
                    }
                    sendJSON(['ok' => false, 'msg' => "Stock insuffisant (disponible : {$med['quantite']})."]);
                }
                if ($med) {
                    $pdo->prepare("UPDATE medicaments SET quantite = quantite - ? WHERE id=?")->execute([$qte, $med['id']]);
                }

                $montant = $qte * $pu;
                $pdo->prepare("UPDATE ventes SET nommed=?, qte=?, prix_unitaire=?, montant=?, date=? WHERE idventes=?")
                    ->execute([$nommed, $qte, $pu, $montant, $date, $id]);
                sendJSON(['ok' => true, 'msg' => 'Vente modifiée avec succès.']);

            case 'vente_delete':
                $id = int_('id');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);
                // Restituer stock
                $old = $pdo->prepare("SELECT nommed, qte FROM ventes WHERE idventes=?");
                $old->execute([$id]);
                $ov = $old->fetch();
                if ($ov) {
                    $pdo->prepare("UPDATE medicaments SET quantite = quantite + ? WHERE nommed=?")
                        ->execute([$ov['qte'], $ov['nommed']]);
                }
                $pdo->prepare("DELETE FROM ventes WHERE idventes=?")->execute([$id]);
                sendJSON(['ok' => true, 'msg' => 'Vente supprimée. Stock restitué.']);

            // ════════════════════════════════════
            //  FACTURES
            // ════════════════════════════════════

            case 'fact_list':
                $rows = $pdo->query("SELECT * FROM factures ORDER BY date_facture DESC, idfacture DESC")->fetchAll();
                sendJSON(['ok' => true, 'data' => $rows]);

            case 'fact_get':
                $id = (int)($_GET['id'] ?? 0);
                $st = $pdo->prepare("SELECT * FROM factures WHERE idfacture=?");
                $st->execute([$id]);
                $row = $st->fetch();
                sendJSON($row ? ['ok' => true, 'data' => $row] : ['ok' => false, 'msg' => 'Facture introuvable.']);

            case 'fact_add':
                $client = str_('client') ?: 'Client';
                $nommed = str_('nommed');
                $qte    = int_('qte');
                $pu     = float_('prix_unitaire');
                $date   = str_('date_facture') ?: date('Y-m-d');
                if (!$nommed) sendJSON(['ok' => false, 'msg' => 'Le médicament est obligatoire.']);
                if ($qte < 1) sendJSON(['ok' => false, 'msg' => 'La quantité doit être au moins 1.']);
                $mt = $qte * $pu;
                $st = $pdo->prepare("INSERT INTO factures (client, nommed, qte, prix_unitaire, montant_total, date_facture) VALUES (?,?,?,?,?,?)");
                $st->execute([$client, $nommed, $qte, $pu, $mt, $date]);
                sendJSON(['ok' => true, 'msg' => "Facture créée pour « {$client} » — " . number_format($mt, 0, ',', ' ') . " FCFA.", 'id' => $pdo->lastInsertId()]);

            case 'fact_update':
                $id     = int_('id');
                $client = str_('client') ?: 'Client';
                $nommed = str_('nommed');
                $qte    = int_('qte');
                $pu     = float_('prix_unitaire');
                $date   = str_('date_facture') ?: date('Y-m-d');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);
                $mt = $qte * $pu;
                $pdo->prepare("UPDATE factures SET client=?, nommed=?, qte=?, prix_unitaire=?, montant_total=?, date_facture=? WHERE idfacture=?")
                    ->execute([$client, $nommed, $qte, $pu, $mt, $date, $id]);
                sendJSON(['ok' => true, 'msg' => 'Facture modifiée.']);

            case 'fact_delete':
                $id = int_('id');
                if (!$id) sendJSON(['ok' => false, 'msg' => 'ID manquant.']);
                $pdo->prepare("DELETE FROM factures WHERE idfacture=?")->execute([$id]);
                sendJSON(['ok' => true, 'msg' => 'Facture supprimée.']);

            default:
                sendJSON(['ok' => false, 'msg' => "Action inconnue : {$action}"]);
        }

    } catch (PDOException $e) {
        sendJSON(['ok' => false, 'msg' => 'Erreur base de données : ' . $e->getMessage()]);
    } catch (Exception $e) {
        sendJSON(['ok' => false, 'msg' => 'Erreur serveur : ' . $e->getMessage()]);
    }
}

// ─── Création tables au chargement initial (silencieux) ─────
try {
    $pdo = getDB();
    $pdo->exec("CREATE TABLE IF NOT EXISTS `medicaments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY, `id_med` VARCHAR(50),
        `categorie` VARCHAR(100), `nommed` VARCHAR(255) NOT NULL,
        `quantite` INT NOT NULL DEFAULT 0, `prix` DECIMAL(12,2) NOT NULL DEFAULT 0,
        `emplacement` VARCHAR(100), `date_expiration` DATE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ventes` (
        `idventes` INT AUTO_INCREMENT PRIMARY KEY, `nommed` VARCHAR(255) NOT NULL,
        `qte` INT NOT NULL DEFAULT 1, `prix_unitaire` DECIMAL(12,2) NOT NULL DEFAULT 0,
        `montant` DECIMAL(12,2) NOT NULL DEFAULT 0, `date` DATE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `factures` (
        `idfacture` INT AUTO_INCREMENT PRIMARY KEY, `client` VARCHAR(255) NOT NULL DEFAULT 'Client',
        `nommed` VARCHAR(255) NOT NULL, `qte` INT NOT NULL DEFAULT 1,
        `prix_unitaire` DECIMAL(12,2) NOT NULL DEFAULT 0, `montant_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
        `date_facture` DATE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {
    // Afficher l'erreur BDD en haut de page si connexion impossible
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>PHARMAsoft — CESMAN</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,400;0,500&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:    #0b1622;
  --surf:  #101e2e;
  --card:  #152234;
  --brd:   #1a3550;
  --brd2:  #1f4060;
  --cyan:  #00d4ff;
  --grn:   #00e5a0;
  --ylw:   #ffcc44;
  --red:   #ff4d6d;
  --txt:   #c8dff0;
  --dim:   #557090;
  --r:     14px;
  --sh:    0 10px 40px rgba(0,0,0,.55);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: var(--bg);
  color: var(--txt);
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  padding: 1.5rem 1rem 3rem;
}
body::before {
  content: '';
  position: fixed; inset: 0; z-index: -1;
  background:
    radial-gradient(ellipse 80% 60% at 15% 5%,  rgba(0,212,255,.05) 0%, transparent 55%),
    radial-gradient(ellipse 60% 50% at 85% 85%,  rgba(0,229,160,.05) 0%, transparent 55%);
}

/* ─ Alerte BDD ─ */
.db-err { background:#ff4d6d22; border:1px solid var(--red); color:var(--red); border-radius:10px; padding:1rem 1.4rem; margin-bottom:1.5rem; font-size:.88rem; }

/* ─ Header ─ */
.hdr { display:flex; align-items:center; gap:1rem; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid var(--brd); }
.hdr-logo { width:46px; height:46px; background:linear-gradient(135deg,var(--cyan),var(--grn)); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:#0b1622; flex-shrink:0; }
.hdr h1 { font-size:1.35rem; font-weight:800; color:#fff; letter-spacing:-.5px; }
.hdr h1 span { color:var(--cyan); }
.hdr p { font-size:.75rem; color:var(--dim); }
.hdr-date { margin-left:auto; font-family:'DM Mono',monospace; font-size:.72rem; color:var(--dim); text-align:right; line-height:1.5; }

/* ─ Cards accueil ─ */
.home-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.2rem; max-width:880px; margin:0 auto 2rem; }
@media(max-width:680px){ .home-grid{ grid-template-columns:1fr; } }
.mc {
  background:var(--card); border:1.5px solid var(--brd); border-radius:var(--r);
  padding:1.6rem 1.4rem; cursor:pointer;
  transition:transform .22s, border-color .22s, box-shadow .22s;
  position:relative; overflow:hidden; user-select:none;
}
.mc::after { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(255,255,255,.025),transparent 60%); pointer-events:none; }
.mc:hover  { transform:translateY(-5px); box-shadow:var(--sh); }
.mc.c1:hover{ border-color:var(--cyan); }
.mc.c2:hover{ border-color:var(--grn); }
.mc.c3:hover{ border-color:var(--ylw); }
.mc-ico { width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; margin-bottom:.9rem; }
.c1 .mc-ico{ background:rgba(0,212,255,.1); color:var(--cyan); }
.c2 .mc-ico{ background:rgba(0,229,160,.1); color:var(--grn); }
.c3 .mc-ico{ background:rgba(255,204,68,.1);  color:var(--ylw); }
.mc h3 { font-size:.92rem; font-weight:700; color:#fff; margin-bottom:.28rem; }
.mc p  { font-size:.76rem; color:var(--dim); line-height:1.5; }
.mc-badge { position:absolute; top:.85rem; right:.9rem; font-size:.68rem; font-weight:700; padding:.18rem .5rem; border-radius:20px; }
.c1 .mc-badge{ background:rgba(0,212,255,.12); color:var(--cyan); }
.c2 .mc-badge{ background:rgba(0,229,160,.12); color:var(--grn); }
.c3 .mc-badge{ background:rgba(255,204,68,.12);  color:var(--ylw); }
.mc-arr { display:flex; align-items:center; gap:.3rem; margin-top:.8rem; font-size:.73rem; font-weight:600; }
.c1 .mc-arr{ color:var(--cyan); } .c2 .mc-arr{ color:var(--grn); } .c3 .mc-arr{ color:var(--ylw); }

/* ─ Panels ─ */
.panel { display:none; max-width:1060px; margin:0 auto; animation:fadeUp .28s ease; }
.panel.on { display:block; }
@keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }
.ph2 { display:flex; align-items:center; gap:.8rem; margin-bottom:1.3rem; }
.bk { background:transparent; border:1.5px solid var(--brd); color:var(--dim); border-radius:8px; padding:.38rem .85rem; cursor:pointer; font-size:.8rem; font-family:'Sora',sans-serif; transition:all .18s; }
.bk:hover { border-color:var(--cyan); color:var(--cyan); }
.ph2 h2 { font-size:1.1rem; font-weight:800; color:#fff; }

/* ─ Form box ─ */
.fbox { background:var(--surf); border:1px solid var(--brd); border-radius:var(--r); padding:1.35rem; margin-bottom:1.35rem; }
.fbox-title { display:flex; align-items:center; gap:.45rem; font-size:.85rem; font-weight:700; color:#fff; margin-bottom:1.1rem; }
label.lbl { display:block; font-size:.68rem; color:var(--dim); margin-bottom:.22rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
.fi {
  width:100%; background:var(--card); border:1.5px solid var(--brd); color:var(--txt);
  border-radius:8px; padding:.52rem .85rem; font-size:.84rem; font-family:'Sora',sans-serif;
  transition:border-color .18s, box-shadow .18s; outline:none;
}
.fi:focus { border-color:var(--cyan); box-shadow:0 0 0 3px rgba(0,212,255,.1); }
.fi::placeholder { color:var(--brd2); }
.fi[readonly] { opacity:.6; cursor:default; }
select.fi option { background:var(--card); }

/* ─ Boutons action ─ */
.btn-c, .btn-g, .btn-y, .btn-o {
  border:none; border-radius:8px; font-weight:700; font-size:.84rem;
  padding:.52rem 1.25rem; cursor:pointer; font-family:'Sora',sans-serif;
  display:inline-flex; align-items:center; gap:.4rem; transition:opacity .18s, transform .1s;
}
.btn-c { background:var(--cyan); color:#0b1622; }
.btn-g { background:var(--grn);  color:#0b1622; }
.btn-y { background:var(--ylw);  color:#0b1622; }
.btn-o { background:transparent; border:1.5px solid var(--brd); color:var(--dim); }
.btn-c:hover,.btn-g:hover,.btn-y:hover { opacity:.85; transform:translateY(-1px); }
.btn-o:hover { border-color:var(--dim); color:var(--txt); }
.btn-c:disabled,.btn-g:disabled,.btn-y:disabled { opacity:.5; cursor:not-allowed; transform:none; }

/* ─ Tableau ─ */
.tbox { background:var(--surf); border:1px solid var(--brd); border-radius:var(--r); overflow:hidden; }
.tbox-hd { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.2rem; border-bottom:1px solid var(--brd); flex-wrap:wrap; gap:.5rem; }
.tbox-hd h5 { font-size:.85rem; font-weight:700; color:#fff; margin:0; }
.stats { display:flex; gap:.45rem; flex-wrap:wrap; }
.pill { display:inline-block; padding:.18rem .6rem; border-radius:20px; font-size:.68rem; font-weight:700; }
.pc{ background:rgba(0,212,255,.1); color:var(--cyan); }
.pg{ background:rgba(0,229,160,.1); color:var(--grn); }
.py{ background:rgba(255,204,68,.1);  color:var(--ylw); }
.pr{ background:rgba(255,77,109,.1);  color:var(--red); }
.tw { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
thead th { background:var(--card); color:var(--dim); font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:.65rem 1rem; border-bottom:1px solid var(--brd); white-space:nowrap; }
tbody tr { border-bottom:1px solid rgba(26,53,80,.45); transition:background .12s; }
tbody tr:hover { background:rgba(0,212,255,.025); }
tbody tr:last-child { border-bottom:none; }
tbody td { padding:.65rem 1rem; font-size:.81rem; vertical-align:middle; }
.ab { border:none; border-radius:6px; padding:.27rem .58rem; font-size:.73rem; cursor:pointer; margin:0 2px; transition:opacity .12s; }
.ab:hover { opacity:.7; }
.ab-e{ background:rgba(0,212,255,.1); color:var(--cyan); }
.ab-d{ background:rgba(255,77,109,.1); color:var(--red); }
.ab-p{ background:rgba(255,204,68,.1);  color:var(--ylw); }
.empty { text-align:center; padding:2.5rem; color:var(--dim); font-size:.83rem; }
.empty i { font-size:1.9rem; display:block; margin-bottom:.55rem; opacity:.3; }

/* ─ Toast ─ */
#toastBox { position:fixed; top:1rem; right:1rem; z-index:9999; display:flex; flex-direction:column; gap:.45rem; min-width:260px; }
.toast-item { padding:.65rem 1.1rem; border-radius:10px; font-size:.82rem; font-weight:600; box-shadow:0 4px 20px rgba(0,0,0,.4); display:flex; align-items:center; gap:.5rem; animation:sli .28s ease; }
.toast-item.s { background:var(--grn); color:#0b1622; }
.toast-item.e { background:var(--red); color:#fff; }
@keyframes sli { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:none} }

/* ─ Spinner sur bouton ─ */
.spin { display:inline-block; animation:rot .7s linear infinite; }
@keyframes rot { to{transform:rotate(360deg)} }

/* ─ Modal ─ */
.modal-content { background:var(--surf)!important; border:1px solid var(--brd)!important; border-radius:var(--r)!important; color:var(--txt)!important; }
.modal-header,.modal-footer { border-color:var(--brd)!important; }
.modal-title { color:#fff!important; font-weight:700!important; font-family:'Sora',sans-serif; }
.btn-close { filter:invert(1)!important; }

/* ─ Print ─ */
#printZone { display:none; }
@media print {
  body > *:not(#printZone) { display:none!important; }
  #printZone { display:block!important; padding:2cm; font-family:Arial,sans-serif; color:#000; }
  .p-header { text-align:center; margin-bottom:1.2rem; }
  .p-header h2 { font-size:1.3rem; font-weight:800; }
  .p-header p { font-size:.8rem; color:#666; }
  .p-sep { border-top:2px solid #333; margin:.6rem 0; }
  .p-info { display:flex; justify-content:space-between; font-size:.82rem; margin:.3rem 0; }
  .p-table { width:100%; border-collapse:collapse; margin:1rem 0; }
  .p-table th,.p-table td { border:1px solid #bbb; padding:.4rem .7rem; font-size:.82rem; }
  .p-table th { background:#eee; }
  .p-total { text-align:right; font-size:1rem; font-weight:800; margin-top:.5rem; }
  .p-footer { text-align:center; font-size:.72rem; color:#888; margin-top:2rem; border-top:1px solid #ccc; padding-top:.7rem; }
}
</style>
</head>
<body>

<?php if (!empty($dbError)): ?>
<div class="db-err"><i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Connexion à la base de données impossible :</strong> <?= htmlspecialchars($dbError) ?><br>
  <small>Vérifiez que MySQL est actif et que les paramètres (host, nom BDD, user, mot de passe) sont corrects en haut du fichier PHP.</small>
</div>
<?php endif; ?>

<!-- Header -->
<div class="hdr no-print">
  <div class="hdr-logo"><i class="bi bi-capsule-pill"></i></div>
  <div>
    <h1>PHARMA<span>soft</span></h1>
    <p>Centre de Santé CESMAN — Pharmacie</p>
  </div>
  <div class="hdr-date" id="hdrDate">—</div>
</div>

<!-- Toasts -->
<div id="toastBox"></div>

<!-- Zone impression -->
<div id="printZone"></div>

<!-- ══ ACCUEIL ════════════════════════════════════════════════ -->
<div class="home-grid" id="homeView">
  <div class="mc c1" onclick="openModule('med')">
    <span class="mc-badge" id="badge1">—</span>
    <div class="mc-ico"><i class="bi bi-capsule-pill"></i></div>
    <h3>Médicaments</h3>
    <p>Ajouter, modifier ou supprimer des médicaments en stock.</p>
    <div class="mc-arr">Ouvrir <i class="bi bi-arrow-right-short"></i></div>
  </div>
  <div class="mc c2" onclick="openModule('vente')">
    <span class="mc-badge" id="badge2">—</span>
    <div class="mc-ico"><i class="bi bi-receipt-cutoff"></i></div>
    <h3>Compte Vente</h3>
    <p>Enregistrer les ventes et suivre les sorties de stock.</p>
    <div class="mc-arr">Ouvrir <i class="bi bi-arrow-right-short"></i></div>
  </div>
  <div class="mc c3" onclick="openModule('fact')">
    <span class="mc-badge" id="badge3">—</span>
    <div class="mc-ico"><i class="bi bi-file-earmark-text"></i></div>
    <h3>Factures</h3>
    <p>Créer et imprimer les factures pour les patients.</p>
    <div class="mc-arr">Ouvrir <i class="bi bi-arrow-right-short"></i></div>
  </div>
</div>

<!-- ══ PANEL MÉDICAMENTS ══════════════════════════════════════ -->
<div class="panel" id="panelMed">
  <div class="ph2">
    <button class="bk" onclick="goHome()"><i class="bi bi-chevron-left"></i> Accueil</button>
    <h2><i class="bi bi-capsule-pill me-2" style="color:var(--cyan)"></i>Gestion des Médicaments</h2>
  </div>

  <div class="fbox">
    <div class="fbox-title"><i class="bi bi-plus-circle" style="color:var(--cyan)"></i><span id="medFormTitle">Nouveau médicament</span></div>
    <input type="hidden" id="medId">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="lbl">Code médicament (id_med)</label>
        <input class="fi" id="medIdMed" placeholder="Auto-généré si vide">
      </div>
      <div class="col-md-5">
        <label class="lbl">Nom du médicament *</label>
        <input class="fi" id="medNom" placeholder="Ex : Paracétamol 500mg">
      </div>
      <div class="col-md-4">
        <label class="lbl">Catégorie</label>
        <select class="fi" id="medCat">
          <option value="">-- Choisir --</option>
          <option>Analgésique</option><option>Antibiotique</option><option>Antipaludéen</option>
          <option>Anti-inflammatoire</option><option>Antihypertenseur</option><option>Antidiabétique</option>
          <option>Vitamine / Supplément</option><option>Désinfectant</option><option>Contraceptif</option>
          <option>Autre</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="lbl">Quantité en stock</label>
        <input class="fi" type="number" id="medQte" placeholder="0" min="0">
      </div>
      <div class="col-md-3">
        <label class="lbl">Prix de vente (FCFA)</label>
        <input class="fi" type="number" id="medPrix" placeholder="0" min="0" step="1">
      </div>
      <div class="col-md-3">
        <label class="lbl">Emplacement (rayon)</label>
        <input class="fi" id="medEmp" placeholder="Ex : Rayon B2">
      </div>
      <div class="col-md-2">
        <label class="lbl">Date d'expiration</label>
        <input class="fi" type="date" id="medDexp">
      </div>
      <div class="col-12 d-flex gap-2 justify-content-end pt-1">
        <button class="btn-o" type="button" onclick="resetMed()"><i class="bi bi-x-lg"></i> Annuler</button>
        <button class="btn-c" type="button" id="btnSaveMed" onclick="saveMed()"><i class="bi bi-check-lg"></i> Enregistrer le médicament</button>
      </div>
    </div>
  </div>

  <div class="tbox">
    <div class="tbox-hd">
      <h5><i class="bi bi-table me-2" style="color:var(--cyan)"></i>Stock des médicaments</h5>
      <div class="stats"><span class="pill pc" id="medCount">0 article(s)</span></div>
    </div>
    <div class="tw">
      <table>
        <thead><tr>
          <th>Code</th><th>Nom médicament</th><th>Catégorie</th>
          <th>Stock</th><th>Prix (FCFA)</th><th>Emplacement</th><th>Expiration</th><th>Actions</th>
        </tr></thead>
        <tbody id="medBody"><tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i>Chargement…</div></td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ PANEL VENTES ═══════════════════════════════════════════ -->
<div class="panel" id="panelVente">
  <div class="ph2">
    <button class="bk" onclick="goHome()"><i class="bi bi-chevron-left"></i> Accueil</button>
    <h2><i class="bi bi-receipt-cutoff me-2" style="color:var(--grn)"></i>Compte Vente</h2>
  </div>

  <div class="fbox">
    <div class="fbox-title"><i class="bi bi-cart-plus" style="color:var(--grn)"></i><span id="venteFormTitle">Nouvelle vente</span></div>
    <input type="hidden" id="venteId">
    <div class="row g-3">
      <div class="col-md-5">
        <label class="lbl">Médicament (nom_med) *</label>
        <input class="fi" list="dlMed" id="venteNom" placeholder="Tapez ou sélectionnez un médicament…" oninput="onVenteMed()">
        <datalist id="dlMed"></datalist>
      </div>
      <div class="col-md-2">
        <label class="lbl">Quantité (qte) *</label>
        <input class="fi" type="number" id="venteQte" placeholder="1" min="1" oninput="calcVente()">
      </div>
      <div class="col-md-3">
        <label class="lbl">Prix unitaire (FCFA)</label>
        <input class="fi" type="number" id="ventePU" placeholder="Auto" min="0" oninput="calcVente()">
      </div>
      <div class="col-md-2">
        <label class="lbl">Stock disponible</label>
        <div class="fi" id="venteStk" style="opacity:.65">—</div>
      </div>
      <div class="col-md-4">
        <label class="lbl">Montant total (FCFA)</label>
        <input class="fi" id="venteMontant" readonly placeholder="—">
      </div>
      <div class="col-md-3">
        <label class="lbl">Date de vente *</label>
        <input class="fi" type="date" id="venteDate">
      </div>
      <div class="col-12 d-flex gap-2 justify-content-end pt-1">
        <button class="btn-o" type="button" onclick="resetVente()"><i class="bi bi-x-lg"></i> Annuler</button>
        <button class="btn-g" type="button" id="btnSaveVente" onclick="saveVente()"><i class="bi bi-check-lg"></i> Enregistrer la vente</button>
      </div>
    </div>
  </div>

  <div class="tbox">
    <div class="tbox-hd">
      <h5><i class="bi bi-table me-2" style="color:var(--grn)"></i>Historique des ventes</h5>
      <div class="stats">
        <span class="pill pg" id="venteCount">0 vente(s)</span>
        <span class="pill py" id="venteCA">CA : 0 FCFA</span>
      </div>
    </div>
    <div class="tw">
      <table>
        <thead><tr>
          <th>#</th><th>Médicament</th><th>Qté</th>
          <th>Prix unitaire</th><th>Montant</th><th>Date</th><th>Actions</th>
        </tr></thead>
        <tbody id="venteBody"><tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i>Chargement…</div></td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ PANEL FACTURES ═════════════════════════════════════════ -->
<div class="panel" id="panelFact">
  <div class="ph2">
    <button class="bk" onclick="goHome()"><i class="bi bi-chevron-left"></i> Accueil</button>
    <h2><i class="bi bi-file-earmark-text me-2" style="color:var(--ylw)"></i>Factures</h2>
  </div>

  <div class="fbox">
    <div class="fbox-title"><i class="bi bi-file-plus" style="color:var(--ylw)"></i><span id="factFormTitle">Nouvelle facture</span></div>
    <input type="hidden" id="factId">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="lbl">Client / Patient *</label>
        <input class="fi" id="factClient" placeholder="Nom complet du patient">
      </div>
      <div class="col-md-4">
        <label class="lbl">Médicament (nom_med) *</label>
        <input class="fi" list="dlMedF" id="factNom" placeholder="Tapez ou sélectionnez…" oninput="onFactMed()">
        <datalist id="dlMedF"></datalist>
      </div>
      <div class="col-md-2">
        <label class="lbl">Quantité (qte) *</label>
        <input class="fi" type="number" id="factQte" placeholder="1" min="1" oninput="calcFact()">
      </div>
      <div class="col-md-3">
        <label class="lbl">Prix unitaire (FCFA)</label>
        <input class="fi" type="number" id="factPU" placeholder="Auto" min="0" oninput="calcFact()">
      </div>
      <div class="col-md-3">
        <label class="lbl">Montant total (FCFA)</label>
        <input class="fi" id="factMontant" readonly placeholder="—">
      </div>
      <div class="col-md-3">
        <label class="lbl">Date de la facture *</label>
        <input class="fi" type="date" id="factDate">
      </div>
      <div class="col-12 d-flex gap-2 justify-content-end pt-1">
        <button class="btn-o" type="button" onclick="resetFact()"><i class="bi bi-x-lg"></i> Annuler</button>
        <button class="btn-y" type="button" id="btnSaveFact" onclick="saveFact()"><i class="bi bi-check-lg"></i> Créer la facture</button>
      </div>
    </div>
  </div>

  <div class="tbox">
    <div class="tbox-hd">
      <h5><i class="bi bi-table me-2" style="color:var(--ylw)"></i>Historique des factures</h5>
      <div class="stats">
        <span class="pill py" id="factCount">0 facture(s)</span>
        <span class="pill pg" id="factTotal">Total : 0 FCFA</span>
      </div>
    </div>
    <div class="tw">
      <table>
        <thead><tr>
          <th>N° Fact.</th><th>Client</th><th>Médicament</th>
          <th>Qté</th><th>Prix unitaire</th><th>Montant</th><th>Date</th><th>Actions</th>
        </tr></thead>
        <tbody id="factBody"><tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i>Chargement…</div></td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal confirmation suppression -->
<div class="modal fade" id="modalDel" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirmer la suppression</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:.86rem" id="modalDelMsg">Supprimer cet enregistrement ?</div>
      <div class="modal-footer">
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-sm btn-danger" id="modalDelOk"><i class="bi bi-trash me-1"></i>Supprimer</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
'use strict';

// ══════════════════════════════════════════════════════
//  CONFIG : URL de base pour les appels AJAX
// ══════════════════════════════════════════════════════
const AJAX_URL = window.location.pathname; // même fichier PHP

// ══════════════════════════════════════════════════════
//  UTILITAIRES
// ══════════════════════════════════════════════════════
const el   = id => document.getElementById(id);
const FCFA = n  => Number(n || 0).toLocaleString('fr-FR') + ' FCFA';
const fmtD = d  => d ? new Date(d.replace(' ', 'T').slice(0, 10) + 'T00:00:00').toLocaleDateString('fr-FR') : '—';
const today = () => new Date().toISOString().slice(0, 10);

// Date/heure header
(function updateClock() {
    const now = new Date();
    el('hdrDate').innerHTML =
        now.toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) +
        '&nbsp; ' + now.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
    setTimeout(updateClock, 30000);
})();

// ─ Toast ─────────────────────────────────────────────
function toast(msg, type = 's') {
    const d = document.createElement('div');
    d.className = 'toast-item ' + type;
    d.innerHTML = `<i class="bi bi-${type === 's' ? 'check-circle-fill' : 'x-circle-fill'}"></i> ${msg}`;
    el('toastBox').appendChild(d);
    setTimeout(() => d.remove(), 4200);
}

// ─ Appel AJAX central ─────────────────────────────────
// method: 'GET' ou 'POST'
// action: nom de l'action (ex: 'med_add')
// data  : objet clé/valeur (pour POST)
// params: objet clé/valeur supplémentaires dans l'URL (pour GET avec id)
async function call(method, action, data = {}, params = {}) {
    try {
        let url = AJAX_URL + '?ajax=1&action=' + encodeURIComponent(action);
        // Ajouter params GET supplémentaires (ex: id pour les get)
        Object.entries(params).forEach(([k, v]) => {
            url += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(v);
        });

        let options = { method };

        if (method === 'POST') {
            const fd = new FormData();
            // L'action est dans l'URL, pas besoin de la remettre dans le body
            Object.entries(data).forEach(([k, v]) => fd.append(k, v ?? ''));
            options.body = fd;
        }

        const res = await fetch(url, options);

        // Vérifier que c'est bien du JSON
        const contentType = res.headers.get('Content-Type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Réponse non-JSON:', text.slice(0, 300));
            return { ok: false, msg: 'Réponse serveur invalide. Voir la console pour les détails.' };
        }

        return await res.json();
    } catch (err) {
        console.error('Erreur fetch:', err);
        return { ok: false, msg: 'Erreur réseau : ' + err.message };
    }
}

// ─ Désactiver/réactiver bouton ────────────────────────
function setBtnLoading(btnId, loading) {
    const b = el(btnId);
    if (!b) return;
    b.disabled = loading;
    if (loading) {
        b._orig = b.innerHTML;
        b.innerHTML = '<span class="spin bi bi-arrow-repeat"></span> Enregistrement…';
    } else {
        b.innerHTML = b._orig || b.innerHTML;
    }
}

// ── Cache médicaments ────────────────────────────────
let medCache = {}; // nommed → { prix, quantite }

async function loadMedCache() {
    const r = await call('GET', 'med_names');
    if (!r.ok) return;
    medCache = {};
    el('dlMed').innerHTML  = '';
    el('dlMedF').innerHTML = '';
    r.data.forEach(m => {
        medCache[m.nom_med] = { prix: parseFloat(m.prix), quantite: parseInt(m.quantite) };
        const o1 = document.createElement('option'); o1.value = m.nom_med; el('dlMed').appendChild(o1);
        const o2 = document.createElement('option'); o2.value = m.nom_med; el('dlMedF').appendChild(o2);
    });
}

// ══════════════════════════════════════════════════════
//  NAVIGATION
// ══════════════════════════════════════════════════════
const panels = ['panelMed', 'panelVente', 'panelFact'];

function openModule(mod) {
    el('homeView').style.display = 'none';
    panels.forEach(p => el(p).classList.remove('on'));
    const map = { med: 'panelMed', vente: 'panelVente', fact: 'panelFact' };
    el(map[mod]).classList.add('on');
    if (mod === 'med')   { loadMed(); }
    if (mod === 'vente') { loadMedCache(); loadVentes(); }
    if (mod === 'fact')  { loadMedCache(); loadFacts(); }
}

function goHome() {
    el('homeView').style.display = 'grid';
    panels.forEach(p => el(p).classList.remove('on'));
    loadBadges();
}

async function loadBadges() {
    const [r1, r2, r3] = await Promise.all([
        call('GET', 'med_list'),
        call('GET', 'vente_list'),
        call('GET', 'fact_list'),
    ]);
    if (r1.ok) el('badge1').textContent = r1.data.length + ' réf.';
    if (r2.ok) el('badge2').textContent = r2.data.length + ' vente(s)';
    if (r3.ok) el('badge3').textContent = r3.data.length + ' facture(s)';
}

// ══════════════════════════════════════════════════════
//  MODULE : MÉDICAMENTS
// ══════════════════════════════════════════════════════
let medEditId = null;

async function loadMed() {
    const r = await call('GET', 'med_list');
    if (!r.ok) { toast(r.msg, 'e'); return; }
    el('medCount').textContent = r.data.length + ' article(s)';
    const tb = el('medBody');
    if (!r.data.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i>Aucun médicament enregistré</div></td></tr>`;
        return;
    }
    tb.innerHTML = r.data.map(m => {
        const stk = parseInt(m.quantite) <= 5
            ? `<span class="pill pr">${m.quantite}</span>`
            : `<span class="pill pc">${m.quantite}</span>`;
        const nom = m.nom_med.replace(/'/g, "\\'");
        return `<tr>
          <td><code style="color:var(--cyan);font-family:'DM Mono',monospace;font-size:.72rem">${m.id_med || '—'}</code></td>
          <td><strong>${m.nom_med}</strong></td>
          <td>${m.cathegorie_med ? `<span class="pill py">${m.cathegorie_med}</span>` : '—'}</td>
          <td>${stk}</td>
          <td><strong>${FCFA(m.prix)}</strong></td>
          <td>${m.emplacement || '—'}</td>
          <td>${fmtD(m.date_expiration)}</td>
          <td>
            <button class="ab ab-e" onclick="editMed(${m.id})" title="Modifier"><i class="bi bi-pencil"></i></button>
            <button class="ab ab-d" onclick="confirmDel('Supprimer le médicament « ${nom} » ?', ()=>deleteMed(${m.id}))" title="Supprimer"><i class="bi bi-trash"></i></button>
          </td>
        </tr>`;
    }).join('');
}

function resetMed() {
    medEditId = null;
    el('medId').value = '';
    el('medFormTitle').textContent = 'Nouveau médicament';
    ['medIdMed','medNom','medEmp'].forEach(i => el(i).value = '');
    el('medCat').value   = '';
    el('medQte').value   = '';
    el('medPrix').value  = '';
    el('medDexp').value  = '';
}

async function editMed(id) {
    const r = await call('GET', 'med_get', {}, { id });
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const m = r.data;
    medEditId = id;
    el('medId').value     = id;
    el('medIdMed').value  = m.id_med || '';
    el('medNom').value    = m.nom_med;
    el('medCat').value    = m.categorie || '';
    el('medQte').value    = m.quantite;
    el('medPrix').value   = m.prix;
    el('medEmp').value    = m.emplacement || '';
    el('medDexp').value   = m.date_expiration ? m.date_expiration.slice(0, 10) : '';
    el('medFormTitle').textContent = 'Modifier — ' + m.nommed;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveMed() {
    const nommed = el('medNom').value.trim();
    if (!nommed) { toast('Le nom du médicament est obligatoire.', 'e'); el('medNom').focus(); return; }

    setBtnLoading('btnSaveMed', true);

    const action = medEditId ? 'med_update' : 'med_add';
    const data = {
        id:              medEditId || '',
        id_med:          el('medIdMed').value.trim(),
        categorie:       el('medCat').value,
        nom_med,
        quantite:        el('medQte').value || '0',
        prix:            el('medPrix').value || '0',
        emplacement:     el('medEmp').value.trim(),
        date_expiration: el('medDexp').value || '',
    };

    const r = await call('POST', action, data);
    setBtnLoading('btnSaveMed', false);

    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) { resetMed(); loadMed(); }
}

async function deleteMed(id) {
    const r = await call('POST', 'med_delete', { id });
    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) loadMed();
}

// ══════════════════════════════════════════════════════
//  MODULE : VENTES
// ══════════════════════════════════════════════════════
let venteEditId = null;

function onVenteMed() {
    const nom = el('venteNom').value.trim();
    const info = medCache[nom];
    if (info) {
        el('ventePU').value  = info.prix;
        el('venteStk').textContent = info.quantite;
    } else {
        el('venteStk').textContent = '—';
    }
    calcVente();
}

function calcVente() {
    const q = parseFloat(el('venteQte').value) || 0;
    const p = parseFloat(el('ventePU').value)  || 0;
    el('venteMontant').value = (q && p) ? (q * p).toLocaleString('fr-FR') + ' FCFA' : '';
}

async function loadVentes() {
    const r = await call('GET', 'vente_list');
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const ca = r.data.reduce((s, v) => s + parseFloat(v.montant || 0), 0);
    el('venteCount').textContent = r.data.length + ' vente(s)';
    el('venteCA').textContent    = 'CA : ' + FCFA(ca);
    const tb = el('venteBody');
    if (!r.data.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i>Aucune vente enregistrée</div></td></tr>`;
        return;
    }
    tb.innerHTML = r.data.map(v => `<tr>
      <td><code style="color:var(--dim);font-size:.72rem">${v.idventes}</code></td>
      <td><strong>${v.nom_med}</strong></td>
      <td>${v.qte}</td>
      <td>${FCFA(v.prix_unitaire)}</td>
      <td><strong style="color:var(--grn)">${FCFA(v.montant)}</strong></td>
      <td>${fmtD(v.date)}</td>
      <td>
        <button class="ab ab-e" onclick="editVente(${v.idventes})"><i class="bi bi-pencil"></i></button>
        <button class="ab ab-d" onclick="confirmDel('Supprimer la vente #${v.idventes} ? Le stock sera restitué.', ()=>deleteVente(${v.idventes}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
}

function resetVente() {
    venteEditId = null;
    el('venteId').value = '';
    el('venteFormTitle').textContent = 'Nouvelle vente';
    ['venteNom','venteQte','ventePU','venteMontant'].forEach(i => el(i).value = '');
    el('venteDate').value      = today();
    el('venteStk').textContent = '—';
}

async function editVente(id) {
    const r = await call('GET', 'vente_get', {}, { id });
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const v = r.data;
    venteEditId = id;
    el('venteId').value    = id;
    el('venteNom').value   = v.nom_med;
    el('venteQte').value   = v.qte;
    el('ventePU').value    = v.prix_unitaire;
    el('venteDate').value  = v.date ? v.date.slice(0, 10) : today();
    el('venteFormTitle').textContent = 'Modifier la vente #' + id;
    calcVente();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveVente() {
    const nommed = el('venteNom').value.trim();
    const qte    = el('venteQte').value.trim();
    if (!nom_med) { toast('Le nom du médicament est obligatoire.', 'e'); el('venteNom').focus(); return; }
    if (!qte || parseInt(qte) < 1) { toast('La quantité doit être au moins 1.', 'e'); el('venteQte').focus(); return; }

    setBtnLoading('btnSaveVente', true);

    const action = venteEditId ? 'vente_update' : 'vente_add';
    const data = {
        id:             venteEditId || '',
        nom_med,
        qte,
        prix_unitaire:  el('ventePU').value || '0',
        date:           el('venteDate').value || today(),
    };

    const r = await call('POST', action, data);
    setBtnLoading('btnSaveVente', false);

    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) { resetVente(); loadVentes(); loadMedCache(); }
}

async function deleteVente(id) {
    const r = await call('POST', 'vente_delete', { id });
    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) { loadVentes(); loadMedCache(); }
}

// ══════════════════════════════════════════════════════
//  MODULE : FACTURES
// ══════════════════════════════════════════════════════
let factEditId = null;

function onFactMed() {
    const nom  = el('factNom').value.trim();
    const info = medCache[nom];
    if (info) el('factPU').value = info.prix;
    calcFact();
}

function calcFact() {
    const q = parseFloat(el('factQte').value) || 0;
    const p = parseFloat(el('factPU').value)  || 0;
    el('factMontant').value = (q && p) ? (q * p).toLocaleString('fr-FR') + ' FCFA' : '';
}

async function loadFacts() {
    const r = await call('GET', 'fact_list');
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const total = r.data.reduce((s, f) => s + parseFloat(f.montant_total || 0), 0);
    el('factCount').textContent = r.data.length + ' facture(s)';
    el('factTotal').textContent = 'Total : ' + FCFA(total);
    const tb = el('factBody');
    if (!r.data.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i>Aucune facture enregistrée</div></td></tr>`;
        return;
    }
    tb.innerHTML = r.data.map(f => {
        const num = 'F-' + String(f.idfacture).padStart(4, '0');
        return `<tr>
          <td><code style="color:var(--ylw);font-family:'DM Mono',monospace;font-size:.72rem">${num}</code></td>
          <td><strong>${f.client}</strong></td>
          <td>${f.nom_med}</td>
          <td>${f.qte}</td>
          <td>${FCFA(f.prix_unitaire)}</td>
          <td><strong style="color:var(--ylw)">${FCFA(f.montant_total)}</strong></td>
          <td>${fmtD(f.date_facture)}</td>
          <td>
            <button class="ab ab-p" onclick="printFact(${f.idfacture})" title="Imprimer"><i class="bi bi-printer"></i></button>
            <button class="ab ab-e" onclick="editFact(${f.idfacture})" title="Modifier"><i class="bi bi-pencil"></i></button>
            <button class="ab ab-d" onclick="confirmDel('Supprimer la facture ${num} ?', ()=>deleteFact(${f.idfacture}))" title="Supprimer"><i class="bi bi-trash"></i></button>
          </td>
        </tr>`;
    }).join('');
}

function resetFact() {
    factEditId = null;
    el('factId').value = '';
    el('factFormTitle').textContent = 'Nouvelle facture';
    ['factClient','factNom','factQte','factPU','factMontant'].forEach(i => el(i).value = '');
    el('factDate').value = today();
}

async function editFact(id) {
    const r = await call('GET', 'fact_get', {}, { id });
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const f = r.data;
    factEditId = id;
    el('factId').value     = id;
    el('factClient').value = f.client;
    el('factNom').value    = f.nommed;
    el('factQte').value    = f.qte;
    el('factPU').value     = f.prix_unitaire;
    el('factDate').value   = f.date_facture ? f.date_facture.slice(0, 10) : today();
    el('factFormTitle').textContent = 'Modifier la facture F-' + String(id).padStart(4, '0');
    calcFact();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveFact() {
    const client = el('factClient').value.trim() || 'Client';
    const nommed = el('factNom').value.trim();
    const qte    = el('factQte').value.trim();
    if (!nom_med) { toast('Le médicament est obligatoire.', 'e'); el('factNom').focus(); return; }
    if (!qte || parseInt(qte) < 1) { toast('La quantité doit être au moins 1.', 'e'); el('factQte').focus(); return; }

    setBtnLoading('btnSaveFact', true);

    const action = factEditId ? 'fact_update' : 'fact_add';
    const data = {
        id:             factEditId || '',
        client,
        nom_med,
        qte,
        prix_unitaire:  el('factPU').value || '0',
        date_facture:   el('factDate').value || today(),
    };

    const r = await call('POST', action, data);
    setBtnLoading('btnSaveFact', false);

    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) { resetFact(); loadFacts(); }
}

async function deleteFact(id) {
    const r = await call('POST', 'fact_delete', { id });
    toast(r.msg, r.ok ? 's' : 'e');
    if (r.ok) loadFacts();
}

async function printFact(id) {
    const r = await call('GET', 'fact_get', {}, { id });
    if (!r.ok) { toast(r.msg, 'e'); return; }
    const f = r.data;
    const num = 'F-' + String(f.idfacture).padStart(4, '0');
    el('printZone').innerHTML = `
      <div class="p-header">
        <h2>&#x2695; CENTRE DE SANTÉ CESMAN</h2>
        <p>Pharmacie interne — Yaoundé, Cameroun</p>
      </div>
      <div class="p-sep"></div>
      <div class="p-info"><span><strong>FACTURE N°</strong> ${num}</span><span>Date : ${fmtD(f.date_facture)}</span></div>
      <div class="p-info"><span>Client / Patient : <strong>${f.client}</strong></span></div>
      <div class="p-sep"></div>
      <table class="p-table">
        <thead><tr><th>Désignation</th><th>Qté</th><th>Prix unitaire</th><th>Montant</th></tr></thead>
        <tbody>
          <tr>
            <td>${f.nom_med}</td>
            <td style="text-align:center">${f.qte}</td>
            <td style="text-align:right">${Number(f.prix_unitaire).toLocaleString('fr-FR')} FCFA</td>
            <td style="text-align:right"><strong>${Number(f.montant_total).toLocaleString('fr-FR')} FCFA</strong></td>
          </tr>
        </tbody>
      </table>
      <div class="p-total">TOTAL : ${Number(f.montant_total).toLocaleString('fr-FR')} FCFA</div>
      <div class="p-footer">Merci de votre confiance — CESMAN Pharmacie &nbsp;|&nbsp; Document généré par PHARMAsoft</div>`;
    window.print();
}

// ══════════════════════════════════════════════════════
//  MODAL SUPPRESSION (commun)
// ══════════════════════════════════════════════════════
const _delModal = new bootstrap.Modal(el('modalDel'));
let _delCallback = null;

function confirmDel(msg, cb) {
    el('modalDelMsg').textContent = msg;
    _delCallback = cb;
    _delModal.show();
}
el('modalDelOk').addEventListener('click', () => {
    if (_delCallback) _delCallback();
    _delModal.hide();
});

// ══════════════════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    el('venteDate').value = today();
    el('factDate').value  = today();
    loadBadges();
});
</script>
</body>
</html>