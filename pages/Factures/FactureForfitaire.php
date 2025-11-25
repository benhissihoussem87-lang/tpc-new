<?php
// tpc/pages/Factures/AjoutFactureForfitaire.php

include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/OffresPrix.class.php';
include 'class/Reglements.class.php';
require_once 'class/connexion.db.php';

/** Same robust generator used here too */
function getNextInvoiceNumber(PDO $pdo, ?int $year = null): string {
  // Tail-only increment: do NOT backfill mid-year gaps.
  $year = $year ?? (int)date('Y');
  $st = $pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(num_fact,'/',1) AS UNSIGNED)), 0) AS max_seq
                        FROM facture WHERE RIGHT(num_fact, 4) = :yr");
  $st->execute([':yr' => (string)$year]);
  $next = ((int)$st->fetchColumn()) + 1;
  return $next . '/' . $year;
}

$pdo      = connexion();
$clients  = $clt->getAllClients();
$projets  = $projet->getAllProjets();
$factures = $facture->AfficherFactures();
$anne     = (int)date('Y');

$num_Facture = getNextInvoiceNumber($pdo, $anne);

/*************** Facture Forfitaire  *********************/
if (isset($_REQUEST['btnSubmitAjoutFactureForfitaire'])) {
  $Numero_Facture = $_POST['num_fact'];

  // Build normalized line items (projet + adresse + prix forfaitaire)
  $rawProjets  = $_POST['projet'] ?? [];
  $rawPrix     = $_POST['prixForfitaire'] ?? [];
  $rawAdresses = $_POST['adresseClient'] ?? [];

  $lines = [];
  $totalForfait = 0.0;
  foreach ($rawProjets as $idx => $pid) {
    if (!is_numeric($pid)) {
      continue;
    }
    $adr   = isset($rawAdresses[$idx]) ? trim((string)$rawAdresses[$idx]) : '';
    $pfRaw = $rawPrix[$idx] ?? '';
    $pfVal = null;
    if ($pfRaw !== '' && $pfRaw !== null) {
      $pfVal = (float)str_replace(',', '.', (string)$pfRaw);
      $totalForfait += $pfVal;
    }
    $lines[] = [
      'projet' => (int)$pid,
      'pf'     => $pfRaw,
      'adr'    => $adr,
    ];
  }

  if (empty($lines)) {
    echo "<script>alert('Veuillez ajouter au moins un projet avec son adresse.')</script>";
  } elseif ($facture->Ajout(@$_POST['num_fact'], @$_POST['client'], @$_POST['numboncommande'], @$_POST['date'], @$_POST['reglement'])) {

    // Offer header mirror
    $offre->Ajout(@$_POST['num_fact'], @$_POST['date'], @$_POST['client']);

    // Insert all selected projects (first line keeps ENS like legacy)
    foreach ($lines as $i => $line) {
      $qte = ($i === 0) ? 'ENS' : '';
      $facture->AjoutProjets_Facture(
        @$_POST['num_fact'],
        '',
        $qte,
        '',
        '',
        $line['pf'],
        '',
        $line['projet'],
        $line['adr']
      );
      $offre->AjoutProjets_Offre(
        @$_POST['num_fact'],
        '',
        $qte,
        '',
        '',
        $line['pf'],
        '',
        $line['projet'],
        $line['adr']
      );
    }

    // Reglement line (use total forfait if available, else fall back to raw list)
    $montantReglement = $totalForfait > 0
      ? $totalForfait
      : trim(implode(' ', array_filter(array_column($lines, 'pf'), 'strlen')));
    $reglement->Ajout(@$_POST['client'], @$_POST['num_fact'], $montantReglement, @$_POST['reglement'], '', '', '', '', '', '', '');

    // Auto-archive the newly created invoice (minimal fields per schema)
    @$facture->AjoutArchive(
      $_POST['num_fact'],           // num_fact
      $_POST['client'],             // client
      $_POST['date'],               // date
      '',                           // joindre (no file at creation)
      $_POST['reglement'],          // reglement
      '',                           // typeReglement
      '',                           // numcheque
      '',                           // datecheque
      '',                           // retenu
      ''                            // Projets
    );

    echo "<script>document.location.href='main.php?Bordereaux&Add&Facture=$Numero_Facture'</script>";
  } else {
    echo "<script>alert('Erreur !!! ')</script>";
  }
}
?>

<style>
.address-builder { background:#f8f9fa; border:1px solid #e5e5e5; border-radius:8px; padding:12px; }
.address-builder .form-label { font-weight:600; margin-bottom:4px; }
#selectedProjectsTable th, #selectedProjectsTable td { vertical-align:middle; }
#selectedProjectsTable .empty-state td { color:#6c757d; }
</style>

<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Facture Forfitaire
        </button>
      </h2>
    </div>

    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
        <!-- Form Add Facture -->
        <form method="post" id="formFactureForfitaire" autocomplete="off">
          <div class="modal-body row">

            <div class="mb-3 col-3">
              <label for="num_fact" class="col-form-label">N&deg; Facture:</label>
              <input type="text" value="<?= htmlspecialchars($num_Facture) ?>" readonly class="form-control" id="num_fact" name="num_fact"/>
            </div>

            <div class="mb-3 col-5">
              <label for="client" class="col-form-label">Client:</label>
              <!-- Recherche client -->
              <input type="search" id="clientSearch" class="form-control mb-2" placeholder="Rechercher un client..." autocomplete="off">
              <select class="form-control" id="client" name="client" size="8" style="overflow-y:auto;">
                <?php if (!empty($clients)) { foreach ($clients as $key) { ?>
                  <option value="<?= $key['id'] ?>"><?= htmlspecialchars($key['nom_client']) ?></option>
                <?php }} ?>
              </select>
            </div>

            <div class="mb-3 col-3">
              <label for="numboncommande" class="col-form-label">N&deg; Bon de commande:</label>
              <input type="text" class="form-control" id="numboncommande" name="numboncommande"/>
            </div>

            <div class="mb-3 col-3">
              <label for="numexonoration" class="col-form-label">N&deg; exonoration:</label>
              <input type="text" class="form-control" id="numexonoration" name="numexonoration"/>
            </div>

            <div class="mb-3 col-3">
              <label for="date" class="col-form-label">Date Facture:</label>
              <input type="date" value="<?= date('Y-m-d') ?>" required class="form-control" id="date" name="date"/>
            </div>

            <div class="mb-3 col-2">
              <label for="reglement" class="col-form-label">Reglement:</label>
              <select class="form-control" id="reglement" name="reglement">
                <option value="oui">Oui</option>
                <option value="Avance">Avance</option>
                <option value="non" selected>Non</option>
              </select>
            </div>

            <!-- Projets + adresses -->
            <div class="mb-3 col-12">
              <label class="col-form-label">Adresses & projets:</label>

              <div class="address-builder mb-3">
                <div class="row g-2 align-items-end">
                  <div class="col-md-4">
                    <label class="form-label" for="adresseCurrent">Adresse du chantier</label>
                    <input type="text" class="form-control" id="adresseCurrent" placeholder="Ex: Site / Chantier / Adresse">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="projetSearch">Projet</label>
                    <input type="search" id="projetSearch" list="projetsList" class="form-control" placeholder="Rechercher un projet">
                    <datalist id="projetsList">
                      <?php if (!empty($projets)) { foreach ($projets as $cle) { ?>
                        <option value="<?= htmlspecialchars($cle['classement']) ?>" data-id="<?= (int)$cle['id'] ?>"><?= htmlspecialchars($cle['classement']) ?></option>
                      <?php }} ?>
                    </datalist>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="prixForfaitaireInput">Prix forfaitaire (H.T)</label>
                    <input type="number" step="0.001" class="form-control" id="prixForfaitaireInput" placeholder="0.000">
                  </div>
                  <div class="col-md-1 d-flex">
                    <button type="button" class="btn btn-outline-primary w-100" id="btnAddProjet" style="margin-top:28px;">Ajouter</button>
                  </div>
                </div>
                <small class="text-muted">Ajoutez plusieurs projets pour la meme adresse, puis changez d'adresse pour saisir un nouveau groupe.</small>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered table-sm" id="selectedProjectsTable">
                  <thead>
                    <tr>
                      <th style="width:30%;">Adresse</th>
                      <th style="width:45%;">Projet</th>
                      <th style="width:15%;">Prix forfaitaire</th>
                      <th style="width:10%;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="empty-state">
                      <td colspan="4" class="text-center text-muted">Aucun projet ajoute pour le moment.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="submit" class="btn btn-primary" name="btnSubmitAjoutFactureForfitaire">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Search + UX scripts -->
<?php
$projetsData = [];
if (!empty($projets)) {
  foreach ($projets as $p) {
    $projetsData[] = ['id' => (int)$p['id'], 'label' => (string)$p['classement']];
  }
}
?>
<script>
// Block Enter from submitting
document.addEventListener('keydown', function (e) {
  const tag = (e.target && e.target.tagName || '').toLowerCase();
  if (e.key === 'Enter' && (tag === 'input' || tag === 'select')) {
    e.preventDefault();
  }
});

// Client search
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

// Projet + adresse builder
(function(){
  const projetsData = <?= json_encode($projetsData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE); ?>;
  const adresseInput = document.getElementById('adresseCurrent');
  const projetInput = document.getElementById('projetSearch');
  const prixInput = document.getElementById('prixForfaitaireInput');
  const addBtn = document.getElementById('btnAddProjet');
  const tbody = document.querySelector('#selectedProjectsTable tbody');
  const form = document.getElementById('formFactureForfitaire');

  const ensureEmptyState = () => {
    if (!tbody) return;
    const hasData = !!tbody.querySelector('tr.data-line');
    if (!hasData) {
      tbody.innerHTML = '<tr class="empty-state"><td colspan="4" class="text-center text-muted">Aucun projet ajoute pour le moment.</td></tr>';
    }
  };

  const escapeHtml = (str) => (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  const escapeAttr = (str) => escapeHtml(str);

  const findProjet = (label) => {
    const q = (label || '').toLowerCase().trim();
    if (!q) return null;
    let res = projetsData.find(p => (p.label || '').toLowerCase() === q);
    if (!res) res = projetsData.find(p => (p.label || '').toLowerCase().includes(q));
    return res || null;
  };

  const addLine = () => {
    if (!tbody) return;
    const adr = (adresseInput?.value || '').trim();
    const label = (projetInput?.value || '').trim();
    const pf = (prixInput?.value || '').trim();
    const projet = findProjet(label);

    if (!adr) { alert('Ajoutez une adresse avant d\'ajouter des projets.'); return; }
    if (!projet) { alert('Selectionnez un projet dans la liste.'); return; }
    if (pf === '') { alert('Indiquez un prix forfaitaire.'); return; }

    if (tbody.querySelector('.empty-state')) tbody.innerHTML = '';

    const row = document.createElement('tr');
    row.className = 'data-line';
    row.innerHTML = `
      <td>
        <strong class="text-uppercase d-block">${escapeHtml(adr)}</strong>
        <input type="hidden" name="adresseClient[]" value="${escapeAttr(adr)}">
      </td>
      <td>
        <span>${escapeHtml(projet.label)}</span>
        <input type="hidden" name="projet[]" value="${projet.id}">
      </td>
      <td>
        <input type="number" step="0.001" class="form-control form-control-sm" name="prixForfitaire[]" value="${escapeAttr(pf)}">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveLine">&times;</button>
      </td>
    `;
    row.querySelector('.btnRemoveLine').addEventListener('click', function(){
      row.remove();
      ensureEmptyState();
    });
    tbody.appendChild(row);

    projetInput.value = '';
    prixInput.value = '';
    projetInput.focus();
  };

  addBtn?.addEventListener('click', addLine);
  [projetInput, prixInput].forEach(el => {
    el?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addLine();
      }
    });
  });

  ensureEmptyState();

  form?.addEventListener('submit', function(e){
    if (!tbody || !tbody.querySelector('tr.data-line')) {
      e.preventDefault();
      alert('Ajoutez au moins un projet avant d\'enregistrer la facture.');
    }
  });
})();
</script>
