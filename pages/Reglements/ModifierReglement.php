<?php
include 'class/Reglements.class.php';
include 'class/client.class.php';
include 'class/Factures.class.php';
$clients=$clt->getAllClients();
$factures=$facture->AfficherFactures();
// verfif Facture a reglement ou Non
//@$reglementFacture=$reglement->getReglementByFacture($_GET['Reglement']);
@$detailReglement=$reglement->getReglementByFacture($_GET['Facture']);
//var_dump($detailReglement);
//echo '<h1> TypeReglement '.$detailReglement['TypeReglement'].'</h1>';
$typeReglement=explode(',',$detailReglement['TypeReglement']);
$NumCheques=explode(',',$detailReglement['num_cheque']);
$DateCheques=explode(',',$detailReglement['date_cheque']);
$Montants=explode(',',$detailReglement['montant']);
$DateReglements=explode(',',$detailReglement['dateReglement']);
//echo '<h1> Cheque  1 '.@$NumCheques[0].'</h1>';
@$detailFacture=$facture->detailFacture($_GET['Facture']);
if(isset($detailFacture)){
	$date=@$detailFacture['date'];
	$nbCommandeClient=@$detailFacture['numboncommande'];
	//echo '<h1> Nb commande client '.$nbCommandeClient.'</h1>';
}
/****************************************/
if(isset($_GET['Facture'])){
	// Get Infos Client 
	  $nomClient=$detailFacture['nom_client'];
	  $detailClient=$clt->getClientByNom($nomClient);
	  $idClient=$detailClient['id'];
	 // echo '<h1> Id Client '.$idClient.'</h1>';
	$numBonCommandeFacture=$_GET['Facture'];
	}
else{$numBonCommandeFacture=null;}


 /********* Modfiier Reglement **********/
  if(isset($_REQUEST['btnSubmitModifier'])){
     /*$typeReglement=@implode(",",array_filter($_POST['typeReglement'], 'strlen'));
	
	  $numCheque=@implode(",",array_filter($_POST['numCheque'], 'strlen'));
	 $etatReglement=$_POST['etatReglement'];
	  $dateCheque=@implode(",",array_filter($_POST['dateCheque'], 'strlen'));
	  $retenueCheque=$_POST['retenueCheque'];
      $montant=@implode(",",array_filter($_POST['montant'], 'strlen'));
	  $dateReglement=@implode(",",array_filter($_POST['dateReglement'], 'strlen'));
	  */
	   $typeReglement=@implode(",",$_POST['typeReglement']);
		$numCheque=@implode(",",$_POST['numCheque']);
	  $etatReglement=$_POST['etatReglement'];
	  $dateCheque=@implode(",",$_POST['dateCheque']);
	  $retenueCheque=$_POST['retenueCheque'];
      $montant=@implode(",",$_POST['montant']);
	  $dateReglement=@implode(",",$_POST['dateReglement']);
     if($reglement->Modifier(@$_POST['facture'],@$_POST['prixTTC'],@$etatReglement,@$numCheque,@$dateCheque,@$retenueCheque,@$typeReglement,@$montant,@$dateReglement,str_replace("'","\'",@$_FILES['pieceRs']['name'])))		 {
		    if($_FILES['pieceRs']['name']!=''){
	@copy($_FILES['pieceRs']['tmp_name'],'pages/Reglements/PiecesRS/'.$_FILES['pieceRs']['name']);
		}
			echo "<script>document.location.href='main.php?Reglements'</script>";	
			
	 
	  }
 }
?>
<!--  Détail ****-->



 <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center;margin-bottom:20px" class="col-12">
                            <a href="?Reglements" class="btn btn-primary active " style="position:relative; top:20px;" >Afficher  Reglements</a>
							</div>
		

                          <div class="card-body">
						  <!-- ************ Scrolling *****************************---->
<div class="accordion col-12" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         Modifier  Reglement
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
					<!--Form Add Facture-->
					<form method="post" enctype="multipart/form-data" id="formBordereau"   >
		
		  <div class="modal-body row">
			
			  <div class="mb-3 col-3">
				<label for="facture" class="col-form-label">Num Facture:</label>
				<?php if(isset($_GET['Facture'])){?>
				    <input type="text" class="form-control" value="<?=$_GET['Facture']?>" readOnly id="facture" name="facture" />
				<?php } else {?>
				<select class="form-control" id="facture" name="facture" required>
				
				 <?php if(!empty($factures)){
						foreach($factures as $key){?>
				 <option value="<?=$key['num_fact']?>"><?=$key['num_fact']?></option>
				 <?php }} } ?>				 
										
				</select>
				   
			  </div>
			  
			  <div class="mb-3 col-2" >
				<label for="prixTTC" class="col-form-label">Prix TTC :</label>
				<input type="text" autocomplete='off' value="<?=@$detailReglement['prix_ttc']?>"    class="form-control" id="prixTTC" name="prixTTC"/>
				   
			</div>
				<div class="mb-3 col-2" >
				<label for="etatReglement" class="col-form-label">Etat Reglement:</label>
				<select class="form-control" id="etatReglement" value="<?=@$detailReglement['prix_ttc']?>" name="etatReglement">
				 <option value="oui" <?php if($detailReglement['etat_reglement']=='oui'){?> selected <?php } ?> >Oui</option>
			  <option value="non" <?php if($detailReglement['etat_reglement']=='non'){?> selected <?php } ?> >Non</option>
			  <option value="Avance" <?php if($detailReglement['etat_reglement']=='Avance'){?> selected <?php } ?> >Avance</option>
				</select>
				   
			</div>
			
			 <div class="mb-3 col-3" >
				<label for="pieceRs" class="col-form-label">Piece RS :</label>
				<input type="file"  class="form-control" id="pieceRs" name="pieceRs"/>
				   
			</div>
			<div class="mb-3 col-2" >
				<label for="retenueCheque" class="col-form-label">Retenue Date:</label>
				<input type="date" class="form-control" value="<?=@$detailReglement['retenue_date']?>" id="retenueCheque" name="retenueCheque"/>
				   
			</div>
		<?php for($i=0;$i<5;$i++){?>
			<div class="mb-3 col-2" >
				<label for="typeReglement" class="col-form-label">Type Reglement :</label>
				<input list="typesReglement" value="<?=@$typeReglement[$i]?>" class="form-control"  id="typeReglement" name="typeReglement[]"  />
				<datalist id="typesReglement">
					  <option value="Chèque"></option>
					  <option value="Espèce"></option>
					  
					</datalist>
			</div>
			
			<div class="mb-3 col-3" >
				<label for="numCheque" class="col-form-label">N° Cheque :</label>
				<input type="text" value="<?=@$NumCheques[$i]?>" class="form-control" id="numCheque" name="numCheque[]"/>
				   
			</div>
			
			<div class="mb-3 col-2" >
				<label for="dateCheque" class="col-form-label">Date Cheque:</label>
				<input type="date" class="form-control" value="<?=@$DateCheques[$i]?>" id="dateCheque" name="dateCheque[]"/>
				   
			</div>
			
		<div class="mb-3 col-2" >
				<label for="montant" class="col-form-label">Montant :</label>
				<input type="text" class="form-control" <?php if($detailReglement){?>value='<?=@$Montants[$i]?>'<?php } ?> id="montant" name="montant[]"/>
				   
			</div>
			
		<div class="mb-3 col-2" >
				<label for="dateReglement" class="col-form-label">Date Reglement:</label>
				<input type="date" class="form-control" <?php if($detailReglement){?> value='<?=@$DateReglements[$i]?>'<?php } ?> id="dateReglement" name="dateReglement[]"/>
				   
			</div>
    <?php }?>
		  </div>
		

       		


		 <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
			<button type="submit" class="btn btn-primary" name="btnSubmitModifier" >Modifier</button>
		  </div>
		  </form>
		
		
					<!--Fin Form Add Facture-->
      </div>
    </div>
  </div>
  
 
</div>
		
<!-- *********** Fin Scrolling **********************--------->
					
						  </div>
                     
                    </div>
