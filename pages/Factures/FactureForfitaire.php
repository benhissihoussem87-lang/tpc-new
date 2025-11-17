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

  if ($facture->Ajout(@$_POST['num_fact'], @$_POST['client'], @$_POST['numboncommande'], @$_POST['date'], @$_POST['reglement'])) {

    // Offer header mirror
    $offre->Ajout(@$_POST['num_fact'], @$_POST['date'], @$_POST['client']);

    // 1st project (ENS block)
    $facture->AjoutProjets_Facture(
      @$_POST['num_fact'], '', 'ENS', '', '', @$_POST['prixForfitaire'][0], '', @$_POST['projet'][0], @$_POST['adresseClient']
    );
    $offre->AjoutProjets_Offre(
      @$_POST['num_fact'], '', '', '', '', @$_POST['prixForfitaire'][0], '', @$_POST['projet'][0], @$_POST['adresseClient']
    );

    // Reglement line
    $reglement->Ajout(@$_POST['client'], @$_POST['num_fact'], @implode(" ", $_POST['prixForfitaire']), @$_POST['reglement'], '', '', '', '', '', '', '');

    // Remaining projects
    for ($i = 1; $i < count($_POST['projet']); $i++) {
      $facture->AjoutProjets_Facture(
        @$_POST['num_fact'], '', '', '', '', @$_POST['prixForfitaire'][$i], '', @$_POST['projet'][$i], @$_POST['adresseClient']
      );
      $offre->AjoutProjets_Offre(
        @$_POST['num_fact'], '', 'ENS', '', '', '', @$_POST['prixForfitaire'][$i], @$_POST['projet'][$i], @$_POST['adresseClient']
      );
    }

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
/* compact responsive grid of project checkboxes */
#projectsGrid .project-item { display:inline-block; width:25%; padding:6px 8px; vertical-align:top; }
@media (max-width: 992px) { #projectsGrid .project-item { width:33.333%; } }
@media (max-width: 768px)  { #projectsGrid .project-item { width:50%; } }
@media (max-width: 480px)  { #projectsGrid .project-item { width:100%; } }
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
        <form method="post">
          <div class="modal-body row">

            <div class="mb-3 col-3">
              <label for="num_fact" class="col-form-label">NÂ° Facture:</label>
              <input type="text" value="<?= htmlspecialchars($num_Facture) ?>" readonly class="form-control" id="num_fact" name="num_fact"/>
            </div>

            <div class="mb-3 col-5">
              <label for="client" class="col-form-label">Client:</label>
              <!-- ðŸ” Client search -->
              <input type="search" id="clientSearch" class="form-control mb-2" placeholder="ðŸ” Rechercher un client..." autocomplete="off">
              <select class="form-control" id="client" name="client" size="8" style="overflow-y:auto;">
                <?php if (!empty($clients)) { foreach ($clients as $key) { ?>
                  <option value="<?= $key['id'] ?>"><?= htmlspecialchars($key['nom_client']) ?></option>
                <?php }} ?>
              </select>
            </div>

            <div class="mb-3 col-3">
              <label for="numboncommande" class="col-form-label">NÂ° Bon de commande:</label>
              <input type="text" class="form-control" id="numboncommande" name="numboncommande"/>
            </div>

            <div class="mb-3 col-3">
              <label for="numexonoration" class="col-form-label">NÂ° exonoration:</label>
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


            <div class="mb-3 col-4">
              <label for="adresse" class="col-form-label">Adresse:</label>
              <input type="text" class="form-control" id="adresse" name="adresseClient"/>
            </div>

            <!-- Projets -->
            <div class="mb-3 col-12">
              <label class="col-form-label col-12">Projets:</label>

              <!-- ðŸ” Project search -->
              <input type="search" id="projetSearch" class="form-control mb-3" placeholder="ðŸ” Rechercher un projet..." autocomplete="off">

              <div id="projectsGrid">
                <?php if (!empty($projets)) { foreach ($projets as $cle) {
                      $pid   = (int)$cle['id'];
                      $label = (string)$cle['classement'];
                ?>
                  <div class="project-item" data-name="<?= htmlspecialchars(mb_strtolower($label), ENT_QUOTES) ?>">
                    <div class="form-check">
                      <input class="form-check-input" name="projet[]" type="checkbox" id="inlineCheckbox<?= $pid ?>" value="<?= $pid ?>">
                      <label class="form-check-label" for="inlineCheckbox<?= $pid ?>"><?= htmlspecialchars($label) ?></label>
                    </div>
                  </div>
                <?php }} ?>
              </div>
            </div>

            <!-- Prix Forfitaire -->
            <div class="mb-3 col-12" style="display:flex; flex-wrap:wrap;">
              <label class="col-form-label col-12">Prix Forfitaire:</label>
              <div class="mb-3 col-2"><input type="text" class="form-control" name="prixForfitaire[]"/></div>
              <div class="mb-3 col-2"><input type="text" class="form-control" name="prixForfitaire[]"/></div>
              <div class="mb-3 col-2"><input type="text" class="form-control" name="prixForfitaire[]"/></div>
              <div class="mb-3 col-2"><input type="text" class="form-control" name="prixForfitaire[]"/></div>
              <div class="mb-3 col-2"><input type="text" class="form-control" name="prixForfitaire[]"/></div>
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

// Project search (filters checkbox tiles)
(function(){
  const input = document.getElementById('projetSearch');
  const items = Array.from(document.querySelectorAll('#projectsGrid .project-item'));
  if (!input || !items.length) return;

  function filterProjects(){
    const q = (input.value || '').toLowerCase().trim();
    items.forEach(item => {
      const name = item.getAttribute('data-name') || item.textContent.toLowerCase();
      item.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
  }
  input.addEventListener('input', filterProjects);
  filterProjects();
})();
</script>

