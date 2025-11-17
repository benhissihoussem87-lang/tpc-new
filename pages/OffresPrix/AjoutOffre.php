<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$offres=$offre->AfficherOffres();
/***** Get Num Offre *******/

$anne=date('Y');
if($offres){$nb=count($offres);
              if((intval($nb+1))<10){$numOffre='0'.intval($nb+1).'/'.$anne;}
			 else{$numOffre=intval($nb+1).'/'.$anne;}
		     }
		  else {$numOffre='01/'.$anne;}
?>

 <?php
 // Ajout
 if(isset($_REQUEST['btnSubmitAjout'])){
	
  // Ajout Offre de Prix
 	if($offre->Ajout(@$_POST['num_offre'],@$_POST['date'],@$_POST['client'])){
		
	// Ajout Projet Offres 
	for($i=0;$i<count($_POST['projet']);$i++){
	 if(!empty($_POST['prix_unit_htv'][$i])){
		$offre->AjoutProjets_Offre(@$_POST['num_offre'],@$_POST['prix_unit_htv'][$i],
		@$_POST['qte'][$i],@$_POST['tva'][$i],@$_POST['remise'][$i],
		@$_POST['prixForfitaire'][$i],@$_POST['prixTTC'][$i],@$_POST['idProjet'][$i],$_POST['adresse']
	  );
	 }
	 }
	
	echo "<script>document.location.href='main.php?Offres_Prix'</script>";
	}
else {echo "<script>alert('Erreur !!! ')</script>";}

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
				<input type="text" required  class="form-control" id="num_offre " name="num_offre"/>
				   
			</div>
			  <div class="mb-3  col-4">
				<label for="client" class="col-form-label">Client:</label>
				<select class="form-control" id="client" name="client">
				 <?php if(!empty($clients)){
						foreach($clients as $key){?>
				 <option value="<?=$key['id']?>"><?=$key['nom_client']?></option>
				 <?php }} ?>				 
										
				</select>
				   
			  </div>
			 
			  <div class="mb-3  col-3">
				<label for="date" class="col-form-label">Date Offre:</label>
				<input type="date" value="<?=date('Y-m-d')?>"  class="form-control" id="date" name="date"/>
				   
			</div>
			   <div class="mb-3  col-3">
			
				<label for="adresse " class="col-form-label">Adresse:</label>
				<input type="text"   class="form-control" id="adresse " name="adresse"/>
				   
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
						foreach($projets as $cle){?>
	<tr>					
 
<div class="mb-3 col-3">
<input type="hidden" name="idProjet[]" multiple value="<?=$cle['id']?>" readOnly size="4"  />
	<input type="text" value="<?=$cle['classement']?>" readOnly multiple class="form-control"  name="projet[]"/>
</div>
<div class="mb-3 col-2">
	<input type="text" multiple class="form-control"  name="prix_unit_htv[]"/>
</div>
<div class="mb-3 col-1">
				
	<input type="number" min="1" value=1 multiple class="form-control"  name="qte[]"/>
	  
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
			<button type="submit" class="btn btn-primary" name="btnSubmitAjout" >Ajouter</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
						  </div>
                     
                    </div>
