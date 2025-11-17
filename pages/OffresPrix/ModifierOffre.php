<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$offres=$offre->AfficherOffres();
$ProjetsOffre=$offre->get_AllProjets_ByOffre($_GET['modifier']);
//echo '<h1> Nb Offre == '.count($ProjetsOffre).'</h1>';
/**********************/
$detailOffre=$offre->detailOffre($_GET['idoffre']);
foreach($ProjetsOffre as $detail);
//echo '<h1>'.$detailOffre['num_offre'].'</h1>';
// Générer le numOffre

?>

 <?php
 // Ajout
 if(isset($_REQUEST['btnSubmitModifier'])){
 
//verifier si l'offre avec l'adresse existe ou non
    $verifOffreInProjetsOffre=$offre->VerifOffredansFacturesProjet($_POST['num_offre'],$_POST['adresse']);
// si l'offre et l'addresse existe 
 if($verifOffreInProjetsOffre){
 
	 // echo '<h1> Offre '.$_POST['num_offre'].' et Adresse '.$_POST['adresse'].' existe déja</h1>';
	  // Modifier Offre
 	$offre->Modifier(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'],$_GET['idoffre']);
	// delete All Project By Offre de num $_POST['num_offre']
	 if($offre->delete_All_Projets_By_OffreAndAdresse(@$_POST['num_offre'],$_POST['adresse'])){
		// Ajout Projet Offres 
		for($i=0;$i<count($_POST['projet']);$i++){
		 if(!empty($_POST['prix_unit_htv'][$i])){
			$offre->ModifierProjets_Offre(@$_POST['num_offre'],@$_POST['prix_unit_htv'][$i],@$_POST['qte'][$i],@$_POST['tva'][$i],@$_POST['remise'][$i],@$_POST['prixForfitaire'][$i],@$_POST['prixTTC'][$i],@$_POST['idProjet'][$i],@$_POST['adresse']
		  );
		 }
		}	 
	 }
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	
 }
 else {
	 // Ajout Projet Offres 
	for($i=0;$i<count($_POST['projet']);$i++){
	 if(!empty($_POST['prix_unit_htv'][$i])){
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],@$_POST['prix_unit_htv'][$i],
		@$_POST['qte'][$i],@$_POST['tva'][$i],@$_POST['remise'][$i],
		@$_POST['prixForfitaire'][$i],@$_POST['prixTTC'][$i],@$_POST['idProjet'][$i],$_POST['adresse']
	  );
	 }
	 }
	// echo '<h1> Offre '.$_POST['num_offre'].'</h1>';
 }

 }
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Offres_Prix" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher Offres de Prix</a>
							</div>
                          <div class="card-body">
					<!--Form Add Facture-->
					<form method="post"   >
		  <div class="modal-body row">
			   <div class="mb-3  col-2">
			
				<label for="num_offre " class="col-form-label">Num Offre:</label>
				<input type="text" value="<?=@$detailOffre['num_offre']?>"  class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			  <div class="mb-3  col-4">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>" <?php if(@$detailOffre['client']==$key['id']){echo 'selected';}?>><?=$key['nom_client']?></option>
				 <?php }} ?>				 						
				</select>  
			  </div>
			 
			  <div class="mb-3  col-3">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=$detailOffre['date']?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			  
			 
			  <div class="mb-3  col-3">
			
				<label for="adresse " class="col-form-label">Adresse:</label>
				<input type="text" value="<?=$detail['adresseClient']?>"    class="form-control" id="adresse " name="adresse"/>
				   
			</div>
			  
			
			 <div class="mb-3 col-3">
				<label for="projet" class="col-form-label">Projets:</label>
			</div>
			<div class="mb-3 col-2">
				<label for="projet" class="col-form-label">Prix Unitaire H.TVA:</label>
			</div>
			<div class="mb-3 col-1">
				<label for="projet" class="col-form-label">Qte:</label>
			</div>
			<div class="mb-3 col-1">
				<label for="projet" class="col-form-label">TVA:</label>
			</div>
			<div class="mb-3 col-1">
				<label for="projet" class="col-form-label">Remise:</label>
			</div>
			<div class="mb-3 col-2">
				<label for="projet" class="col-form-label">Prix Forfitaire:</label>
			</div>
			<div class="mb-3 col-2">
				<label for="projet" class="col-form-label">Prix TTC:</label>
			</div>
			<table width=100% id="FormAddFacture">
				
				 <?php if(!empty($projets)){
						foreach($projets as $cle){
			//Parcour Projets Offre a modifier 
		   $prix_unit_htv=null;$qte=1;$tva=null;$remise=null;$forfitaire=null;$ttc=null;
		foreach($ProjetsOffre as $p){ 
		
		if($p['projet']==$cle['id']){
			//echo '<b>'.$cle['id'].'</b><br>';
			$prix_unit_htv=$p['prix_unit_htv'];$qte=$p['qte'];$tva=$p['tva'];
			$remise=$p['remise'];$forfitaire=$p['prixForfitaire'];
			$ttc=$p['prixTTC'];
			}}
			
							?>
	<tr>					
 
<div class="mb-3 col-3">
<input type="hidden" name="idProjet[]" multiple value="<?=$cle['id']?>" readOnly size="4"  />
	<input type="text" value="<?=$cle['classement']?>" readOnly multiple class="form-control"  name="projet[]"/>
</div>
<div class="mb-3 col-2">
	<input type="text" multiple class="form-control"  name="prix_unit_htv[]" value="<?=$prix_unit_htv?>"/>
</div>
<div class="mb-3 col-1">
				
	<input type="number" min="1" value="<?=$qte?>" multiple class="form-control"  name="qte[]"/>
	  
</div>
<div class="mb-3 col-1">
				
	<input type="text" class="form-control" value=19 multiple name="tva[]"/>
	  
			</div>
			<div class="mb-3 col-1">
				
			   <input type="text" class="form-control" multiple name="remise[]"/>
	  
			</div>
  <div class="mb-3 col-2">
				
		<input type="text" class="form-control" multiple name="prixForfitaire[]"/>
	  
	</div>
			
  <div class="mb-3 col-2">
				
		<input type="text" class="form-control" multiple name="prixTTC[]"/>
	  </div>
</tr>				 
				 <?php }} ?>				 
										
				
	</table>		  
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<?php if(!isset($_GET['modifier'])){?>
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
			<?php } else {?>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
			
			<?php } ?>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
						  </div>
                     
                    </div>
