<?php
include 'class/client.class.php';
include 'class/Reglements.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$factures=$facture->AfficherFactures();

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
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
											 <tr>
												<td>
												<a href="?Factures&Projets=<?=$key['num_fact']?>" class="btn btn-success"><?=$key['num_fact']?></a>
												</td>
												 <td><?=$key['date']?></td>
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
												<td>
												<?php if(empty($reglementFacture)){
													echo 'Pas de reglement ';
												}
												else {
													?>
												<?=$reglementFacture['etat_reglement']?>
												<?php } ?></td>
												<td>
												<?php if(empty($reglementFacture)){
													echo 'Pas de reglement ';
												}
												else {
													$reglementTypes=explode(',',$reglementFacture['TypeReglement']);
													for($i=0;$i<count($reglementTypes);$i++){
												if(!empty($reglementTypes[$i])){
													?>
												<?=$reglementTypes[$i].','?>
													<?php }} }?>
												</td>
												
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
