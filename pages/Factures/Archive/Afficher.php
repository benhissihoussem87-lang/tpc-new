<?php
// ---- HARDENED AUTOLOADER (replaces include_class_file + manual includes) ----
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Resolve project root: /pages/Factures/Archive => project root is 3 levels up
$ROOT = realpath(__DIR__ . '/../../../');
if ($ROOT === false) { die('Root path resolution failed.'); }

// Try common class directories: "class" or "classes"
$CLASS_DIRS = ['class', 'classes', 'Class', 'Classes'];

spl_autoload_register(function(string $class) use ($ROOT, $CLASS_DIRS) {
    // Try multiple filename patterns and case variants
    $candidates = [];
    $names = [$class, strtolower($class), ucfirst(strtolower($class))];
    foreach ($CLASS_DIRS as $dir) {
        $base = $ROOT . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
        foreach ($names as $n) {
            $candidates[] = $base . $n . '.class.php';
            $candidates[] = $base . $n . '.php';
            $candidates[] = $base . $n . '.class';
        }
        // Last resort: scan the dir case-insensitively
        if (is_dir($base)) {
            $dh = opendir($base);
            if ($dh) {
                while (($f = readdir($dh)) !== false) {
                    if (preg_match('/^' . preg_quote($class, '/') . '\.(class\.php|php|class)$/i', $f)) {
                        require_once $base . $f;
                        closedir($dh);
                        return;
                    }
                }
                closedir($dh);
            }
        }
    }
    // If not found, let PHP continue (class_exists() below will show a helpful error)
});

// ---- DIAGNOSTICS (only when you add ?debug=1 to the URL) ----
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ROOT = {$ROOT}\n";
    echo "Class dirs checked: " . implode(', ', array_map(fn($d)=>$ROOT.DIRECTORY_SEPARATOR.$d, $CLASS_DIRS)) . "\n";
}

// ---- Create/verify instances (adjust names to your real class names!) ----
$errors = [];

// If your file declares `class Client` (singular), set $ClientClass = 'Client'.
// If it declares `class Clients` (plural), keep 'Clients'.
$ClientClass  = class_exists('Clients') ? 'Clients' : (class_exists('Client') ? 'Client' : 'Clients');
$ProjetClass  = class_exists('Projet')  ? 'Projet'  : (class_exists('Projets') ? 'Projets' : 'Projet');
$FactureClass = class_exists('Factures')? 'Factures': (class_exists('Facture') ? 'Facture' : 'Factures');

foreach ([['Clients',$ClientClass], ['Projet',$ProjetClass], ['Factures',$FactureClass]] as [$label,$real]) {
    if (!class_exists($real)) { $errors[] = "Missing class: expected {$label}, tried {$real}."; }
}

if ($errors) {
    echo "<pre>".implode("\n", $errors)."\n";
    echo "Tip: check the exact class names declared inside your files in /{$CLASS_DIRS[0]} (e.g., Projet.class.php should contain: class Projet { ... })\n";
    exit; // stop early so you see the message
}

// Make instances using the *actual* class names detected above
if (!isset($clt)     || !is_object($clt)     || !($clt instanceof $ClientClass))  { $clt     = new $ClientClass(); }
if (!isset($projet)  || !is_object($projet)  || !($projet instanceof $ProjetClass))  { $projet  = new $ProjetClass(); }
if (!isset($facture) || !is_object($facture) || !($facture instanceof $FactureClass)) { $facture = new $FactureClass(); }

// Fetch archives and handle deletion
if (isset($_GET['deleteFacture'])) {
    $toDel = $_GET['deleteFacture'];
    if ($facture->deleteArchiveFacture($toDel)) {
        echo "<script>document.location.href='main.php?ArchivesFacture&Afficher'</script>";
        exit;
    }
}

$factures = $facture->AfficherFacturesArchives();

?>
<!-- UI -->
<div class="card shadow mb-4">
  <div class="col-12 text-center">
    <a href="?ArchivesFacture&Add" class="btn btn-primary active" style="position:relative; top:20px;">
      Ajouter Archive Facture
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Num Facture</th>
            <th>Date Facture</th>
            <th>Client</th>
            <th>Règlement</th>
            <th>Type</th>
            <th>Chèque</th>
            <th>Date chèque</th>
            <th>Retenu</th>
            <th>Fichier</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($factures)): ?>
          <?php foreach ($factures as $key): ?>
            <!-- Modal: Ajouter/Modifier Projets -->
            <div class="modal fade" id="AddProjets<?= (int)$key['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ajouter / Modifier Projets</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="idFactureArchive" value="<?= htmlspecialchars($key['num_fact']) ?>"/>
                    <div class="form-group">
                      <label for="libelle" class="col-form-label">Projets:</label>
                      <textarea class="form-control" rows="12" required id="libelle" name="projets"><?= htmlspecialchars(@$key['Projets']) ?></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" name="ModifierProjetFacture" class="btn btn-primary">Valider</button>
                  </div>
                </form>
              </div>
            </div>
            <!-- /Modal -->

            <tr>
              <td><span class="badge badge-success" style="font-size:100%;"><?= htmlspecialchars($key['num_fact'] ?? '') ?></span></td>
              <td><?= htmlspecialchars($key['date'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['nom_client'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['reglement'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['typeReglement'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['numcheque'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['datecheque'] ?? '') ?></td>
              <td><?= htmlspecialchars($key['retenu'] ?? '') ?></td>
              <td>
                <?php
                  // uploaded filename stored in column "joindre" on archivefacture
                  $filename = isset($key['joindre']) ? trim((string)$key['joindre']) : '';
                  if ($filename !== ''):
                ?>
                  <a href="pages/Factures/Factures_Archive/<?= rawurlencode($filename) ?>" target="_blank">Ouvrir</a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;">
                <a href="?ArchivesFacture&Afficher&deleteFacture=<?= urlencode($key['num_fact']) ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer l\\'archive N° <?= htmlspecialchars($key['num_fact']) ?> ?');">
                  Supprimer
                </a>
                <a href="#" data-toggle="modal" data-target="#AddProjets<?= (int)$key['id'] ?>" class="btn btn-sm btn-warning">
                  Ajouter Projets
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
