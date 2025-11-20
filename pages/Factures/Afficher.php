<?php
include 'class/client.class.php';
include 'class/Reglements.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$factures=$facture->AfficherFactures();
$selectedYear = '';
if (!empty($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year'])) {
	$selectedYear = $_GET['year'];
}
$factureYears = [];
if (!empty($factures)) {
	foreach ($factures as $row) {
		$yearCandidate = isset($row['date']) ? substr((string)$row['date'], 0, 4) : '';
		if (preg_match('/^\d{4}$/', $yearCandidate)) {
			$factureYears[$yearCandidate] = true;
		}
	}
}
$factureYears = array_keys($factureYears);
rsort($factureYears, SORT_STRING);
if ($selectedYear !== '') {
	$factures = array_values(array_filter($factures, function ($row) use ($selectedYear) {
		$date = isset($row['date']) ? (string)$row['date'] : '';
		$num  = isset($row['num_fact']) ? (string)$row['num_fact'] : '';
		if (strpos($date, $selectedYear) === 0) {
			return true;
		}
		if (strpos($num, '/'.$selectedYear) !== false || strpos($num, $selectedYear.'/') === 0) {
			return true;
		}
		return false;
	}));
}

// Générer le numOffre
$anne=date('Y');
		if($factures){
			$nb=count($factures);	
		$numFacture=intval($nb+1).'/'.$anne;
		}
		else {$numFacture='1/'.$anne;}
/*********** delete Facture ***************/
 if(isset($_GET['deleteFacture'])){
	 if($facture->deleteFacture($_GET['deleteFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 }


/*********** Modifier Adresse Facture ***************/
 if(isset($_POST['btnModifierAdresse'])){
	 if($facture->ModifierAdresseFacture($_POST['adresseExiste'],$_POST['adresseUpdate'],$_POST['numFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 } 
 
 if(isset($_POST['btnSupprimerFactureAdresse'])){
	 if($facture->deleteFactureByAdresse($_POST['numFacture'],$_POST['adresseExiste'],$_POST['numFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 } 
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Factures&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajouter Facture</a>
							</div>
                        
                        <div class="card-body">
							<div class="row mb-3">
								<div class="col-md-3">
									<label for="facturesYearFilter" class="form-label">Filtrer par année</label>
									<select id="facturesYearFilter" class="form-control">
										<option value="">Toutes les années</option>
										<?php foreach ($factureYears as $year) { ?>
											<option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-2 d-flex align-items-end">
									<button type="button" class="btn btn-secondary w-100" id="facturesYearApply">Filtrer</button>
								</div>
							</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-year-filter="#facturesYearFilter" data-year-column="1">
                                    <thead>
                                        <tr>
											<th >Num Facture</th>
											<th >Date Facture</th>
											<th >Client</th>
                                            <th >Numéro Bon de commande Client</th>
											<th >Numéro Exonoration</th>
                                             <th >Etat Reglement</th>
											    <th >Type Reglement</th>
											
											<th >Supp/Mod</th>
											<th >Imprimer</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($factures)){
										foreach($factures as $key){
										$reglementFacture=$facture->GetReglementByFacture($key['num_fact']);
										// Build a numeric sort key for DataTables so invoices sort naturally
										$factureSort = 0;
										$factureYear = '';
										if (!empty($key['num_fact'])) {
											$parts = explode('/', $key['num_fact']);
											$numero = isset($parts[0]) ? (int)$parts[0] : 0;
											$annee  = isset($parts[1]) ? (int)$parts[1] : 0;
											$factureSort = ($annee * 10000) + $numero;
											if ($annee > 0) {
												$factureYear = (string)$annee;
											}
										}
										$dateYear = '';
										if (!empty($key['date'])) {
											$maybeYear = substr((string)$key['date'], 0, 4);
											if (preg_match('/^\d{4}$/', $maybeYear)) {
												$dateYear = $maybeYear;
											}
										}
										$yearTokens = array_unique(array_filter([$factureYear, $dateYear]));
										$rowYearAttr = implode(' ', $yearTokens);
					
										$bonCommandeFacture=$facture->GetBonCommandeByFacture($key['num_fact']);
						/**** Verifier si la facture est forfitaire ou non Forfitaire **********/
				@$verifTypeFacture=$facture->getTypeFacture($key['num_fact']);
                 $typeFacture=null;
                foreach($verifTypeFacture as $f){
					//echo '<h1> Type '.getType($f).'</h1>';
					if(!empty($f['prixForfitaire'])){ $typeFacture='forfitaire'; break;}
					else {$typeFacture='non forfitaire';  break;}
				}				
											?>
<!------------------------------------ Modal Modifier Adresse Facture ----------------->
<div class="modal fade"  id="ModalUpdateFacture<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Facture - <?=$key['num_fact']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
	  <?php if($typeFacture=='non forfitaire'){?>
         <a href="?Factures&modifier=<?=$key['num_fact']?>" class="btn btn-warning">Modifier Facture</a>
	  <?php } else if($typeFacture=='forfitaire'){?>
	   <a href="?Factures&modifierForfitaire=<?=$key['num_fact']?>" class="btn btn-warning">Modifier Facture</a>
	  
	  <?php }?>
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalUpdateAdresse<?=$key['num_fact']?>"   data-bs-dismiss="modal">Modifier Adresse</a>
      </div>
    </div>
  </div>
</div>
<!-------- Modal Modifier Adresse Facture ---->
<div class="modal fade"  id="ModalUpdateAdresse<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Adresses de Facture - <?=$key['num_fact']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesFactures=$facture->getAdresseFactureByNumFacture($key['num_fact']);
		//echo '<h2> NB Offre '.count($adressesFactures).'</h1>';?>
		<h1><?//=count($adressesFactures)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse a modifier:</label>
				<input type="hidden" name="numFacture" value="<?=$key['num_fact']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste">
				 <?php if(!empty($adressesFactures)){
						foreach($adressesFactures as $adr){?>
				 <option value="<?=$adr['adresseClient']?>">
				 <?php if(!empty($adr['adresseClient'])){?>
				 <?=$adr['adresseClient']?>
				 <?php } else {
				   echo 'Adresse Vide ';
				  }?>
				 </option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			   <div class="mb-3 col-6">
				<label for="adresseUpdate" class="col-form-label">Nouveau adresse:</label>
				<input type="text" class="form-control" name="adresseUpdate" placeholder="Nouveau adresse !!!" id="adresseUpdate"/>
				   
			  </div>
		</div>
		<div class="modal-footer">
         <a href="?Factures&modifier=<?=$key['num_fact']?>" class="btn btn-warning">Modifier Facture</a>
		 <button type="submit" class="btn btn-primary" name="btnModifierAdresse">Modifier Adresse</button>
      </div>
    </div>
</form>
  </div>
</div>

<!------------------------------------ Fin Modal Adresse Facture ----------------------->
<!----- Modal SupprimerFacture-->
<!------------------------------------ Modal Modifier Adresse Facture ----------------->
<div class="modal fade"  id="ModalSupprimerFacture<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Facture - <?=$key['num_fact']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
	 
         <a href="?Factures&deleteFacture=<?=$key['num_fact']?>" class="btn btn-warning">Supprimer Facture</a>
	
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalDeleteFactureByAdresse<?=$key['num_fact']?>"   data-bs-dismiss="modal">Supprimer Facture-Adresse</a>
      </div>
    </div>
  </div>
</div>

<!-- Fin Modal Supprimer Facture -->
<!--------------- Modal Supprimer Facture et Adresse Facture --------------->

<div class="modal fade"  id="ModalDeleteFactureByAdresse<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer Adresses de Facture - <?=$key['num_fact']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesFactures=$facture->getAdresseFactureByNumFacture($key['num_fact']);
		//echo '<h2> NB Offre '.count($adressesFactures).'</h1>';?>
		<h1><?//=count($adressesFactures)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse :</label>
				<input type="hidden" name="numFacture" value="<?=$key['num_fact']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste" require  >
				<option selected disabled>Choisir une adresse</option>
				 <?php if(!empty($adressesFactures)){
						foreach($adressesFactures as $adr){?>
				 <option value="<?=$adr['adresseClient']?>">
				 <?php if(!empty($adr['adresseClient'])){?>
				 <?=$adr['adresseClient']?>
				 <?php } else {
				   echo 'Adresse Vide ';
				  }?>
				 </option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			 
		</div>
		<div class="modal-footer">
         
		 <button type="submit" class="btn btn-danger" name="btnSupprimerFactureAdresse">Supprimer</button>
      </div>
    </div>
</form>
  </div>
</div>

<!--------------- Fin Modal Supprimer Facture et Adresse Facture --------------->
											 <tr<?php if (!empty($rowYearAttr)) { ?> data-year-values="<?= htmlspecialchars($rowYearAttr) ?>"<?php } ?>>
												<td data-order="<?=$factureSort?>">
												<a href="?Factures&Projets=<?=$key['num_fact']?>" class="btn btn-success"><?=$key['num_fact']?></a>
												</td>
												 <td><?php if($rowYearAttr!==''):?><span class="d-none year-marker"><?=htmlspecialchars($rowYearAttr)?></span><?php endif;?><?=$key['date']?></td>
												<td><?=$key['nom_client']?></td>
												<td>
												<?php if(empty($bonCommandeFacture)){
													echo 'Pas de Bon de commande ';
												}
												else {
													?>
                    <?= htmlspecialchars($bonCommandeFacture['numboncommande'] ?? '') ?>
												<?php } ?>
												</td>
												<td><?= htmlspecialchars($key['numexonoration'] ?? '') ?></td>
												<?php
                                                // Etat de reglement : always show Oui/Non (treat Avance/Avoir as paid)
                                                $etatReglementAffiche = 'Non';
                                                if (!empty($reglementFacture)) {
                                                    $etatReglementBrut = strtolower(trim((string)($reglementFacture['etat_reglement'] ?? '')));
                                                    if (in_array($etatReglementBrut, ['oui','avance','avoir'], true)) {
                                                        $etatReglementAffiche = 'Oui';
                                                    }
                                                } else {
                                                    $etatFactureBrut = strtolower(trim((string)($key['reglement'] ?? '')));
                                                    if ($etatFactureBrut === 'oui') {
                                                        $etatReglementAffiche = 'Oui';
                                                    }
                                                }

                                                // Types de reglement (show '-' when none)
                                                $typesAffiche = '-';
                                                if (!empty($reglementFacture) && isset($reglementFacture['TypeReglement'])) {
                                                    $reglementTypes = array_filter(array_map('trim', explode(',', (string)$reglementFacture['TypeReglement'])));
                                                    if (!empty($reglementTypes)) {
                                                        $typesAffiche = htmlspecialchars(implode(', ', $reglementTypes));
                                                    }
                                                }
                                                ?>
												<td><?=$etatReglementAffiche?></td>
												<td><?=$typesAffiche?></td>
												
												  <td>
												  <!--<a href="?Factures&deleteFacture=<?=$key['num_fact']?>" class="btn btn-danger" onclick="if(!confirm('Voulez-vous supprimer la facture <?=$key['num_fact']?>')) return false">Supp</a>-->
												  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#ModalSupprimerFacture<?=$key['num_fact']?>">Supp</button>
												    <?php if($typeFacture=='forfitaire'){?>
												  
												    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ModalUpdateFacture<?=$key['num_fact']?>">Modif</button>
												  <?php } else {?>
												  <!--<a href="?Factures&modifier=<?=$key['num_fact']?>" class="btn btn-warning"> Mod</a>-->
												  <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ModalUpdateFacture<?=$key['num_fact']?>">Modif</button>
												  
												  <?php } ?>
												   
												   
												   </td>
												   <td>
												   
												   <a href="./pages/Factures/ModeleFacture.php?facture=<?=$key['num_fact']?>">Imprimer</a></td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
<script>
(function(){
	var applyBtn=document.getElementById('facturesYearApply');
	if(applyBtn){
		applyBtn.addEventListener('click',function(){
			var select=document.getElementById('facturesYearFilter');
			var year=select ? select.value : '';
			var url=new URL(window.location.href);
			if(year){
				url.searchParams.set('year',year);
			}else{
				url.searchParams.delete('year');
			}
			window.location.href=url.toString();
		});
	}
})();
</script>
