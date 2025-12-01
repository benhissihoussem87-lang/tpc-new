<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
require_once 'class/connexion.db.php';
require_once __DIR__ . '/../Factures/forfait_form_helpers.php';

$clients = $clt->getAllClients();
$projets = $projet->getAllProjets();
$offres  = $offre->AfficherOffres();

// Générer le prochain numéro d'offre forfaitaire <seq>/<année>
$anne = (int)date('Y');
if ($offres) {
    $nb = count($offres);
    $numOffre = ((intval($nb + 1)) < 10 ? '0' : '') . intval($nb + 1) . '/' . $anne;
} else {
    $numOffre = '01/' . $anne;
}

$forfaitFormErrors   = [];
$forfaitLinesPrefill = [];

/*************** Offre Forfaitaire  *********************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['btnSubmitAjoutOffreForfitaire'])) {
    $rawLines = isset($_POST['lignes']) && is_array($_POST['lignes']) ? $_POST['lignes'] : [];
    $forfaitLinesPrefill = tpc_prepare_forfait_lines_prefill($rawLines);

    // Accept free-text projects; require projet + prix
    $validLines = [];
    foreach ($rawLines as $ln) {
        if (!is_array($ln)) {
            continue;
        }
        $proj = trim((string)($ln['projet'] ?? ''));
        $prix = trim((string)($ln['prix'] ?? ''));
        $adr  = trim((string)($ln['adresse'] ?? ''));
        if ($proj === '' || $prix === '') {
            continue;
        }
        $validLines[] = [
            'projet'    => $proj,
            'projet_id' => $proj, // free text accepted; OffresPrix will null non-numeric IDs
            'prix_raw'  => $prix,
            'adresse'   => $adr,
        ];
    }

    if (empty($validLines)) {
        $forfaitFormErrors[] = "Ajoutez au moins une ligne avec un projet et un prix forfaitaire.";
    }

    $numOffrePosted = $_POST['num_offre'] ?? $numOffre;
    $clientId       = $_POST['client'] ?? '';
    $dateOffre      = $_POST['date'] ?? date('Y-m-d');

    // Garder les valeurs saisies si on réaffiche le formulaire
    $numOffre = $numOffrePosted;

    if (empty($forfaitFormErrors)) {
        // Ajout ou mise à jour de l'en-tête d'offre
        if ($offre->Ajout($numOffrePosted, $dateOffre, $clientId)) {
            $seenAddress = [];
            foreach ($validLines as $line) {
                $adrKey = strtolower(trim($line['adresse'] ?? ''));
                $isFirstForAdr = !isset($seenAddress[$adrKey]);
                $seenAddress[$adrKey] = true;

                // Première ligne par adresse marquée ENS (même logique que facture forfaitaire)
                $qteFlag = $isFirstForAdr ? 'ENS' : '';

                $offre->AjoutProjets_Offre(
                    $numOffrePosted,
                    '',
                    $qteFlag,
                    '',
                    '',
                    $line['prix_raw'],
                    '',
                    $line['projet_id'],
                    $line['adresse']
                );
            }

            echo "<script>document.location.href='main.php?Offres_Prix'</script>";
            exit;
        } else {
            $forfaitFormErrors[] = "Erreur lors de l'enregistrement de l'offre.";
        }
    }
}

$selectedClientId = $_POST['client'] ?? '';
$postedDate       = $_POST['date'] ?? date('Y-m-d');
?>

<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Offre de Prix Forfaitaire
        </button>
      </h2>
    </div>

    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
        <!-- Form Add Offre forfaitaire -->
        <form method="post">
          <div class="modal-body row">

            <?php if (!empty($forfaitFormErrors)) { ?>
            <div class="col-12">
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($forfaitFormErrors as $err) { ?>
                  <li><?= htmlspecialchars($err) ?></li>
                  <?php } ?>
                </ul>
              </div>
            </div>
            <?php } ?>

            <div class="mb-3 col-3">
              <label for="num_offre" class="col-form-label">N&deg; Offre:</label>
              <input type="text" required value="<?= htmlspecialchars($numOffre) ?>" class="form-control" id="num_offre" name="num_offre"/>
            </div>

            <div class="mb-3 col-5">
              <label for="client" class="col-form-label">Client:</label>
              <!-- Recherche client -->
              <input type="search" id="clientSearch" class="form-control mb-2" placeholder="Rechercher un client..." autocomplete="off">
              <select class="form-control" id="client" name="client" size="8" style="overflow-y:auto;">
                <?php if (!empty($clients)) {
                      foreach ($clients as $key) {
                        $cid = (string)$key['id'];
                ?>
                  <option value="<?= htmlspecialchars($cid) ?>" <?= $selectedClientId === $cid ? 'selected' : '' ?>><?= htmlspecialchars($key['nom_client']) ?></option>
                <?php }} ?>
              </select>
            </div>

            <div class="mb-3 col-3">
              <label for="date" class="col-form-label">Date Offre:</label>
              <input type="date" value="<?= htmlspecialchars($postedDate) ?>" required class="form-control" id="date" name="date"/>
            </div>

            <div class="mb-3 col-12">
              <?php
                $forfaitLinesWidgetId = 'forfaitLinesOffre';
                include __DIR__ . '/../Factures/partials/forfait_lines_widget.php';
              ?>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary" name="btnSubmitAjoutOffreForfitaire">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- UX scripts: blocage Enter + recherche client -->
<script>
// Empêcher Enter de soumettre le formulaire pendant la saisie
document.addEventListener('keydown', function (e) {
  const tag = (e.target && e.target.tagName || '').toLowerCase();
  if (e.key === 'Enter' && (tag === 'input' || tag === 'select')) {
    e.preventDefault();
  }
});

// Recherche client dans la liste
(function(){
  const search = document.getElementById('clientSearch');
  const select = document.getElementById('client');
  if (!search || !select) return;

  function filterClients() {
    const q = (search.value || '').toLowerCase();
    let firstVisibleIndex = -1;

    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      const visible = opt.text.toLowerCase().includes(q);
      opt.hidden = !visible;
      if (visible && firstVisibleIndex === -1) firstVisibleIndex = i;
    }
    if (firstVisibleIndex !== -1) {
      select.selectedIndex = firstVisibleIndex;
      const selOpt = select.options[firstVisibleIndex];
      if (selOpt) selOpt.scrollIntoView({ block: 'nearest' });
    }
  }
  search.addEventListener('input', filterClients);
  filterClients();
})();
</script>

