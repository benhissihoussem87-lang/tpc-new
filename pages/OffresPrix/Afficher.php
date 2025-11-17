<?php
include_once 'class/OffresPrix.class.php';
include_once 'class/client.class.php';
$clients=$clt->getAllClients();
$offres=$offre->AfficherOffres();
// Générer le num Offre
$anne=date('Y');
		if($offres){
			$nb=count($offres);
			
		$numOffre=intval($nb+1).'/'.$anne;
		}
		else {$numOffre='1/'.$anne;}
// Suppression
 if(isset($_GET['deleteOffre'])){
	if($offre->deleteOffre($_GET['deleteOffre']))
	{echo "<script>document.location.href='main.php?Offres_Prix'</script>";}
 }
 /*** Detail Client ***/
/*********** Modifier Adresse Facture ***************/
 if(isset($_POST['btnModifierAdresse'])){
	 if($offre->ModifierAdresseOffre($_POST['adresseExiste'],$_POST['adresseUpdate'],$_POST['numOffre'])){
		 echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	 }
 } 
 if(isset($_POST['btnSupprimerOffreAdresse'])){
	 if($offre->deleteOffreByAdresse($_POST['numOffre'],$_POST['adresseExiste'])){
		 echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	 }
 } 
 // Ajout
 if(isset($_REQUEST['btnSubmitAjout'])){

	 if($offre->Ajout(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],@$_POST['projet'],@$_POST['prix_unit_htv'],@$_POST['qte'],@$_POST['tva'],@$_POST['prix_ttc']))
	{
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }
 // Modifier
 if(isset($_REQUEST['btnSubmitModifier'])){

	 if($offre->Modifier(@$_POST['type'],@$_POST['convention'],@$_POST['nom'],@$_POST['code'],@$_POST['adresse'],@$_POST['matriculeFiscale'],@$_POST['exonoration'],@$_FILES['pieceExonoration']['name'],@$_POST['tel'],@$_POST['email'],@$_POST['idClient']))
	{
	
		if($_FILES['pieceExonoration']['name']!=''){
	@copy($_FILES['pieceExonoration']['tmp_name'],'pages/clients/pieceExonorationClients/'.$_FILES['pieceExonoration']['name']);
		}
		echo "<script>document.location.href='main.php?Gestion_Clients'</script>";}
else {echo "<script>alert('Erreur !!! ')</script>";}
 }

?>
<!--  Détail ****-->

 

		
<!-- Modal Add Offre -->
<div class="modal fade"  id="ModalAddOffre" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" >
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Offre de Prix :
		<b><?=$numOffre?></b></h1>
        
      </div>
	  
		  <form method="post"  id="formClient" >
		  <div class="modal-body">
			<div class="mb-3">
			
				<label for="num_offre " class="col-form-label">Num Offre:</label>
				<input type="text" value="<?=$numOffre?>" readOnly class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			  <div class="mb-3">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3">
				<label for="projet" class="col-form-label">Projet:</label>
			
				<select class="form-control" id="projet" name="projet">
				 <?php if(!empty($projets)){
						foreach($projets as $cle){?>
				 <option value="<?=$cle['id']?>"><?=$cle['classement']?></option>
				 <?php }} ?>				 
										
				</select>
			  </div>
			  <div class="mb-3">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=date('Y-m-d')?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			  
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
		
	
	
    </div>
  </div>
</div>
<!--  Fin Modal Add Offre-->

 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Offres_Prix&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >
							Ajouter Offre de Prix</a>
							
							 <a href="?Offres_Prix&AddOffreForfitaire" class="btn btn-primary active " style="position:relative; top:20px;"  >
							Ajouter Offre de Prix Forfitaire</a>
							</div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
											<th >Num Offre</th>
											<th >Date Offre</th>
											<th >Client</th>
                                           
											<th >Supprimer</th>
											<th >Modifier</th>
											<th >Imprimer</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($offres)){
										foreach($offres as $key){
											//var_dump($key);
						/********************/
$ProjetsOffre=$offre->get_AllProjets_ByOffre($key['num_offre']);

// Verification si l'offre est forfitaire ou non 
$verifForfitaireOffre=null;
if(!empty($ProjetsOffre)){foreach($ProjetsOffre as $projet){
	if($projet['prixForfitaire']!=''){$verifForfitaireOffre='forfitaire'; break;}
	else if($projet['prix_unit_htv']!=''){$verifForfitaireOffre='Nonforfitaire'; break;}
	
}}
//echo '<h1> TypeOffre = '.$verifForfitaireOffre.'</h1>';
?>

<!------------------------------------ Modal Modifier Adresse Offre ----------------->
<div class="modal fade"  id="ModalUpdateOffre<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Offre - <?=$key['num_offre']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
	  <?php if($verifForfitaireOffre=='Nonforfitaire'){?>
         <a href="?Offres_Prix&modifier=<?=$key['num_offre']?>&idoffre=<?=$key['id_offre']?>" class="btn btn-warning">Modifier Offre</a>
	  <?php } else if($verifForfitaireOffre=='forfitaire'){?>
	   <a href="?Offres_Prix&modifierOffreForfitaire=<?=$key['num_offre']?>&idoffre=<?=$key['id_offre']?>" class="btn btn-warning">Modifier Offre</a>
	  
	  <?php }?>
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalUpdateAdresse<?=$key['num_offre']?>"   data-bs-dismiss="modal">Modifier Adresse</a>
      </div>
    </div>
  </div>
</div>
<!-------- Modal Modifier Adresse Offre ---->
<!-------- Modal Modifier Adresse Offre ---->
<div class="modal fade"  id="ModalUpdateAdresse<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier Adresses de l'offre - <?=$key['num_offre']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesOffre=$offre->getAdresseOffreProjetByOffre($key['num_offre']);
		?>
		<h1><?//=count($adressesOffre)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse a modifier:</label>
				<input type="hidden" name="numOffre" value="<?=$key['num_offre']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste">
				 <?php if(!empty($adressesOffre)){
						foreach($adressesOffre as $adr){?>
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
         
		 <button type="submit" class="btn btn-primary" name="btnModifierAdresse">Modifier Adresse</button>
      </div>
    </div>
</form>
  </div>
</div>
<!---******************** Suppression **********-------------->
<!------------------------------------ Fin Modal Adresse Facture ----------------------->
<!------------------------------------ Modal Modifier Adresse Facture ----------------->
<div class="modal fade"  id="ModalSupprimerOffre<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestion Offre - <?=$key['num_offre']?></h5>
		
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
     
      <div class="modal-footer">
	 
         <a href="?Offres_Prix&deleteOffre=<?=$key['id_offre']?>" class="btn btn-danger">Supprimer Offre</a>
	
		 <a href="#" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#ModalDeleteOffreByAdresse<?=$key['num_offre']?>"   data-bs-dismiss="modal">Supprimer Offre-Adresse</a>
      </div>
    </div>
  </div>
</div>

<!-- Fin Modal Supprimer Facture -->
<!--------------- Modal Supprimer Facture et Adresse Facture --------------->

<div class="modal fade"  id="ModalDeleteOffreByAdresse<?=$key['num_offre']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
<form method="post">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer Adresses de l'offre - <?=$key['num_offre']?></h5>
		 </div>
		<div class="modal-body row">
		<?php
		$adressesOffre=$offre->getAdresseOffreProjetByOffre($key['num_offre']);
		?>
		<h1><?//=count($adressesOffre)?></h1>
		 <div class="mb-3 col-6">
				<label for="adresseExiste" class="col-form-label">Choisir l'adresse :</label>
				<input type="hidden" name="numOffre" value="<?=$key['num_offre']?>"/>
				<select class="form-control" id="adresseExiste" name="adresseExiste" required  >
				<option selected disabled>Choisir une adresse</option>
				 <?php if(!empty($adressesOffre)){
						foreach($adressesOffre as $adr){?>
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
         
		 <button type="submit" class="btn btn-danger" name="btnSupprimerOffreAdresse">Supprimer</button>
      </div>
    </div>
</form>
  </div>
</div>

											 <tr>
												<td><a href="#">
												  <?=$key['num_offre']?>
												  </a>
												  </td>
												 <td><?=$key['date']?></td>
												<td><?=$key['nom_client']?></td>
												
												  <td>
												 <!-- <a href="?Offres_Prix&deleteOffre=<?=$key['id_offre']?>" class="btn btn-danger">Supprimer</a>
												  -->
												   <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#ModalSupprimerOffre<?=$key['num_offre']?>">Supp</button>
												  </td>
												  <td>
												 <!-- <?php if($verifForfitaireOffre=='Nonforfitaire'){?>
												  <a href="?Offres_Prix&modifier=<?=$key['num_offre']?>&idoffre=<?=$key['id_offre']?>" class="btn btn-warning"> Mod</a>
												  <?php }
												  else if($verifForfitaireOffre=='forfitaire'){?>
												   <a href="?Offres_Prix&modifierOffreForfitaire=<?=$key['num_offre']?>&idoffre=<?=$key['id_offre']?>" class="btn btn-warning"> Mod</a>
												  <?php }?> -->
												     <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ModalUpdateOffre<?=$key['num_offre']?>">Modif</button>
													 
												  </td>
												  <th><a href="./pages/OffresPrix/ModeleOffre.php?offre=<?=$key['num_offre']?>">Imprimer</a></th>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
