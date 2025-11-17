<?php
include 'class/client.class.php';
include 'class/Projet.class.php';
include 'class/Factures.class.php';
include 'class/BonsCommandes.class.php';
include 'class/OffresPrix.class.php';
$clients=$clt->getAllClients();
$projets=$projet->getAllProjets();
$infosFacture=$facture->detailFacture($_GET['modifier']);
$ProjetsFacture=$facture->get_AllProjets_ByFacture($_GET['modifier']);
$numFacture=$_GET['modifier'];
foreach($ProjetsFacture as $p);
$adresseFacture=$p['adresseClient'];

//echo '<h1> Adresse '.$adresseFacture.'</h1>';
// Get Num Bon commande Client 
$bonCommandeClient=$bonCommande->getDetailBonCommandeByNumFacture($_GET['modifier']);
//var_dump($bonCommandeClient);

?>

 <?php
 // Ajout
 if(isset($_REQUEST['btnSubmitModifier'])){
    $Numero_Facture=$_POST['num_fact'];
    $numBonCommandeClient=$_POST['numboncommande'];
    // Default address to the existing facture address if left empty, so new lines
    // are attached to the same address section and show up in the print.
    if (!isset($_POST['adresseClient']) || trim($_POST['adresseClient'])==='') {
        $_POST['adresseClient'] = isset($adresseFacture) ? $adresseFacture : '';
    }
/********************/
////// si la facture existe déja 
 $verifFactureInProjetsFacture=$facture->VerifFacturedansFacturesProjet($_POST['num_fact'],$_POST['adresseClient']);
 if($verifFactureInProjetsFacture){
	 
 // Modifier Facture
 	$facture->Modifier(@$_POST['num_fact'],@$_POST['client'],@$_POST['num_fact'],@$_POST['date'],@$_POST['reglement']);
	
    // delete All Project lines then re-insert from the form (for the selected address)
    if($facture->delete_All_Projets_By_FactureAndMultiAdress(@$_POST['num_fact'],@$_POST['adresseClient'])){
      $n = max(
        isset($_POST['projet']) ? count((array)$_POST['projet']) : 0,
        isset($_POST['idProjet']) ? count((array)$_POST['idProjet']) : 0
      );
      for($i=0; $i<$n; $i++){
        $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
        $hasPrice = (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]));
        if($idp > 0 && $hasPrice){
          $facture->ModifierProjets_Facture(
            @$_POST['num_fact'],
            @$_POST['prix_unit_htv'][$i],
            @$_POST['qte'][$i],
            @$_POST['tva'][$i],
            @$_POST['remise'][$i],
            @$_POST['prixForfitaire'][$i],
            @$_POST['prixTTC'][$i],
            $idp,
            $_POST['adresseClient']
          );
        }
      }
    }
/***************************/	
	if($_POST['reglement']=='oui')
	{
		
		echo "<script>document.location.href='main.php?Reglements&Add&Facture=$numFacture&BonCommandeClient=$numBonCommandeClient&Modifier'</script>";
		// si le bon de commmande est !=null 
		
	}
	else if(!empty($_POST['numboncommande']))
	{
		 echo "<script>document.location.href='main.php?Bons_commandes&Add&ModifierBC&Facture=$Numero_Facture&BonCommande=$numBonCommandeClient'</script>";
		
	}
	else{echo "<script>document.location.href='main.php?Factures&modifier=$numFacture'</script>";}
	
 }
 else {
	
    // Ajout Projet Facture (no previous lines under this address)
    $n = max(
      isset($_POST['projet']) ? count((array)$_POST['projet']) : 0,
      isset($_POST['idProjet']) ? count((array)$_POST['idProjet']) : 0
    );
    for($i=0; $i<$n; $i++){
      $idp = isset($_POST['idProjet'][$i]) && is_numeric($_POST['idProjet'][$i]) ? (int)$_POST['idProjet'][$i] : 0;
      $hasPrice = (!empty($_POST['prix_unit_htv'][$i]) || !empty($_POST['prixForfitaire'][$i]) || !empty($_POST['prixTTC'][$i]));
      if($idp > 0 && $hasPrice){
        // Ajout Projet Facture
        $facture->AjoutProjets_Facture(
          @$_POST['num_fact'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );
        // Ajout Projet Offre (mirror)
        $offre->AjoutProjets_Offre(
          @$_POST['num_fact'],
          @$_POST['prix_unit_htv'][$i],
          @$_POST['qte'][$i],
          @$_POST['tva'][$i],
          @$_POST['remise'][$i],
          @$_POST['prixForfitaire'][$i],
          @$_POST['prixTTC'][$i],
          $idp,
          @$_POST['adresseClient']
        );
      }
    }
		
 }
 }
?>
<!--  Détail ****-->
 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
					 <div class="row col-12">
                       <div style="width:40%;text-align:center;float:left" class="col-9">
                            <a href="?Factures" class="btn btn-primary active " style="position:relative; top:20px;">Afficher Les Factures</a>
							
							</div>
						<div style="width:40%;text-align:center;float:right" class="col-3">
                            <a href="./pages/Factures/ModeleFacture.php?facture=<?=$numFacture?>" class="btn btn-info active " style="position:relative; top:20px;" >Imprimer </a>
							</div>
						</div>
                          <div class="card-body">
					<!--Form Add Facture-->
					<form method="post"   >
		  <div class="modal-body row">
			<div class="mb-3 col-2">
			
				<label for="num_fact " class="col-form-label">N° Facture:</label>
				<input type="text" value="<?=@$infosFacture['num_fact']?>"  class="form-control" id="num_fact " name="num_fact"/>
				   
			</div>
			  <div class="mb-3 col-3">
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
			  <div class="mb-3 col-2">
			<label for="numboncommande  " class="col-form-label">Bon.Commande Client:</label>
				<input type="text" value="<?=@$bonCommandeClient['num_bon_commandeClient']?>"  class="form-control" id="numboncommande"
				name="numboncommande"/>
				   
			</div>
			  
			  <div class="mb-3 col-3" >
				<label for="date" class="col-form-label">Date Facture:</label>
				<input type="date" value="<?=$infosFacture['date'] ?>" required  class="form-control" id="date" name="date"/>
				   
			</div>
			<div class="mb-3 col-2">
			<label for="reglement" class="col-form-label">Reglement :</label>
			<select class="form-control" id="reglement" name="reglement">
			  <option value="oui" <?php if($infosFacture['reglement']=='oui'){?> selected <?php } ?> >Oui</option>
			  <option value="non" <?php if($infosFacture['reglement']=='non'){?> selected <?php } ?> >Non</option>
			  <option value="Avance" <?php if($infosFacture['reglement']=='Avance'){?> selected <?php } ?> >Avance</option>
			</select>
				
				   
			</div>
			 <div class="mb-3 col-12" >
				<label for="adresseClient" class="col-form-label">Adresse:</label>
				<input type="text"  class="form-control" id="adresseClient" name="adresseClient"/>
				   
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
						//Parcour Projets Factures a modifier 
		$prix_unit_htv=null;$qte=1;$tva=null;$remise=null;$forfitaire=null;$ttc=null;
		foreach($ProjetsFacture as $p){ if($p['projet']==$cle['id']){
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
	<input type="text" multiple class="form-control" value="<?=$prix_unit_htv?>"
	name="prix_unit_htv[]"/>
</div>
<div class="mb-3 col-1">
				
	<input type="number" value="<?=$qte?>" min="1" value=1 multiple class="form-control"  name="qte[]"/>
	  
</div>
<div class="mb-3 col-1">
				
	<input type="text" class="form-control" value="<?=$tva?>" value=19 multiple name="tva[]"/>
	  
			</div>
			<div class="mb-3 col-1">
				
			   <input type="text" value="<?=$remise?>" class="form-control" multiple name="remise[]"/>
	  
			</div>
  <div class="mb-3 col-2">
				
		<input type="text" class="form-control" value="<?=$forfitaire?>" multiple name="prixForfitaire[]"/>
	  
	</div>
			
  <div class="mb-3 col-2">
				
		<input type="text" class="form-control" value="<?=$ttc?>" multiple name="prixTTC[]"/>
	  </div>
</tr>				 
				 <?php }}  ?>				 
										
				
	</table>		  
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		  </form>
		
					<!--Fin Form Add Facture-->
						  </div>
                     
                    </div>
