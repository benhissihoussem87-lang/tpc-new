 <?php 
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/BonsCommandes.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$factures=$facture->AfficherFactures();
$infosFacture=$facture->detailFacture($_GET['modifierForfitaire']);
//var_dump($infosFacture);
$ProjetsFacture=$facture->get_AllProjets_ByFacture($_GET['modifierForfitaire']);
$numFacture=$_GET['modifierForfitaire'];
$bonCommandeClient=$bonCommande->getDetailBonCommandeByNumFacture($_GET['modifierForfitaire']);
$arrayProjets=array();
$arrayPrixForfitaire=array();
/***** get Adresse Facture******/
$AdressesFacture=$facture->get_All_AdressesClient_ProjetsFacture($_GET['modifierForfitaire']);

//var_dump($ProjetsFacture);
foreach($ProjetsFacture as $p){
	array_push($arrayProjets,$p['projet']);
	array_push($arrayPrixForfitaire,$p['prixForfitaire']);
}

/*************** Facture Forfitaire  *********************/
 if(isset($_REQUEST['btnSubmitAjoutFactureForfitaire'])){

 ////// si la facture existe déja 
 $verifFactureInProjetsFacture=$facture->VerifFacturedansFacturesProjet($_POST['num_fact'],$_POST['adresseClient']);
  if($verifFactureInProjetsFacture){
	//echo '<h1> Facture existe avec l\'adresse '.$_POST['adresseClient'].'</h1>';
	// Modifier Facture
 	$facture->Modifier(@$_POST['num_fact'],@$_POST['client'],@$_POST['numboncommande'],@$_POST['date'],@$_POST['reglement']);
    // delete All Project By Facture de num $_POST['num_fact']
	if($facture->delete_All_Projets_By_FactureAndMultiAdress(@$_POST['num_fact'],@$_POST['adresseClient'])){
	// Ajout Projet Facture
		$facture->AjoutProjets_Facture(@$_POST['num_fact'],'',
		'ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],@$_POST['adresseClient']
	  );
	  
	   $offre->AjoutProjets_Offre(@$_POST['num_fact'],'','', '','',
	   @$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresseClient']
	  );
	 
   /************ Fin Ajout Facture Forfitaire       ********************/
	for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Facture
		$facture->AjoutProjets_Facture(@$_POST['num_fact'],'',
		'','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],@$_POST['adresseClient']
	  );
	  // Ajout Projet Offre
	  
	  $offre->AjoutProjets_Offre(@$_POST['num_fact'],'','ENS', '',
	   '',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],@$_POST['adresseClient']
	  );
		 
	}
	echo "<script>document.location.href='main.php?Factures'</script>";
 }	
	  
  }
  else {
	//  echo '<h1> Facture n\'existe pas avec l\'adresse '.$_POST['adresseClient'].'</h1>';
	  // Modifier Facture
 	$facture->Modifier(@$_POST['num_fact'],@$_POST['client'],@$_POST['numboncommande'],@$_POST['date'],@$_POST['reglement']);
	// Ajout Projet Facture
		$facture->AjoutProjets_Facture(@$_POST['num_fact'],'',
		'ENS','','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],@$_POST['adresseClient']
	  );
	  
	   $offre->AjoutProjets_Offre(@$_POST['num_fact'],'','', '','',@$_POST['prixForfitaire'][0],'',@$_POST['projet'][0],$_POST['adresseClient']
	  );
	  
	  for($i=1;$i<count($_POST['projet']);$i++){
		
	 // Ajout Projet Facture
		$facture->AjoutProjets_Facture(@$_POST['num_fact'],'',
		'','','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],@$_POST['adresseClient']
	  );
	  // Ajout Projet Offre
	  
	  $offre->AjoutProjets_Offre(@$_POST['num_fact'],'','ENS', '','',@$_POST['prixForfitaire'][$i],'',@$_POST['projet'][$i],@$_POST['adresseClient']
	  );
		 
	}
	//echo "<script>document.location.href='main.php?Factures'</script>";
	  
  }

 }
 ?>
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
       	<!--Form Add Facture-->
					<form method="post"   >
		  <div class="modal-body row">
			<div class="mb-3 col-3">
			
				<label for="num_fact " class="col-form-label">N° Facture:</label>
				<input type="text" value="<?=$infosFacture['num_fact']?>" readOnly class="form-control" id="num_fact " name="num_fact"/>
				   
			</div>
			  <div class="mb-3 col-5">
				<label for="client" class="col-form-label">Client:</label>
				
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>" <?php if($infosFacture['nom_client']==$key['nom_client']){echo 'selected';}?>>
				 <?=$key['nom_client']?>
				 </option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			  <div class="mb-3 col-3">
			<label for="numboncommande  " class="col-form-label">N° Bon de commande:</label>
				<input type="text" value="<?=$bonCommandeClient['num_bon_commandeClient']?>"  class="form-control" id="numboncommande"
				name="numboncommande"/>
				
				</div>
			  <div class="mb-3 col-3">
			<label for="numboncommande  " class="col-form-label">N° exonoration:</label>
				<input type="text"  class="form-control" id="numexonoration"
				name="numexonoration"/>
				   
			</div>
			  
			  <div class="mb-3 col-3" >
				<label for="date" class="col-form-label">Date Facture:</label>
				<input type="date" value="<?=$infosFacture['date'] ?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="reglement" class="col-form-label">Reglement :</label>
			<select class="form-control" id="reglement" name="reglement">
			  <option value="oui" <?php if($infosFacture['reglement']=='oui'){?> selected <?php } ?> >Oui</option>
			  <option value="non" <?php if($infosFacture['reglement']=='non'){?> selected <?php } ?> >Non</option>
			  <option value="Avance" <?php if($infosFacture['reglement']=='Avance'){?> selected <?php } ?> >Avance</option>
			</select>
				   
			</div>
			 <div class="mb-3 col-4">
			<label for="adresse" class="col-form-label">Adresse:</label>
			<input list="adresses" class="form-control" id="adresse" name="adresseClient">
			<datalist id="adresses">
			<?php
            if(!empty($AdressesFacture)){
			foreach($AdressesFacture as $a){?>
             <option value="<?=$a['adresseClient']?>">
			<?php }} ?>
			</datalist>
				   
			</div>
			<div class="mb-3 col-12">
				<label for="projet" class="col-form-label col-12">Projets:</label>
				<?php if(!empty($projets)){
						foreach($projets as $cle){
					 if(in_array($cle['id'],$arrayProjets)){
						 $check="checked";
					 }
					 else {
						  $check="";
					 }
                   				
						?>
					<div class="form-check form-check-inline col-3" >
				  <input class="form-check-input" name="projet[]" multiple type="checkbox" id="inlineCheckbox<?=$cle['id']?>" <?=$check ?>  value="<?=$cle['id']?>">
				  <label class="form-check-label" for="inlineCheckbox<?=$cle['id']?>"><?=$cle['classement']?></label>
				</div>
			 
				 <?php }} ?>				 
	
			</div>	
		<!-- Champs Forfitaire **************-->
		     <div class="mb-3 col-12" style="display:flex">
	<label for="projet" class="col-form-label col-2">Prix Forfitaire:</label>
	
	<div class="mb-3 col-2">
      <input type="text" class="form-control" value="<?=@$arrayPrixForfitaire[0] ?>" multiple name="prixForfitaire[]"/>
	  
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple value="<?=@$arrayPrixForfitaire[1]?>"  name="prixForfitaire[]"/> 
	</div>
	<div class="mb-3 col-2">
      <input type="text" class="form-control" multiple value="<?=@$arrayPrixForfitaire[2]?>"  name="prixForfitaire[]"/>
	 </div>
  <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	</div>
   <div class="mb-3 col-2">
      <input type="text" class="form-control" multiple value="<?=@$arrayPrixForfitaire[3]?>"  name="prixForfitaire[]"/>
	</div>	
</div>	
		<!-- End Forfitaire --------------------->
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<?php if(isset($_GET['modifierForfitaire'])){?>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjoutFactureForfitaire" >Modifier</button>
			<?php } ?>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
      </div>
    </div>
 
 
 </div>
