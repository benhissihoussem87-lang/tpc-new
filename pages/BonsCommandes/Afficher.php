<?php
include 'class/BonsCommandes.class.php';

$BonsCommandes=$bonCommande->getAll();

/*********** delete Facture ***************/
 if(isset($_GET['deleteBonCommande'])){
	 if($bonCommande->deleteBonCommande($_GET['deleteBonCommande'])){
		 echo "<script>document.location.href='main.php?Bons_commandes'</script>";
	 }
 }
		
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bons_commandes&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout Bon commande</a>
							</div>
                        
                         <div class="card-body">
							<div class="row mb-3">
								<div class="col-md-4">
									<label for="bonsSearch" class="form-label">Recherche rapide</label>
									<input type="search" class="form-control" id="bonsSearch" placeholder="Num BC, client...">
								</div>
								<div class="col-md-2 d-flex align-items-end">
									<button type="button" class="btn btn-secondary w-100" id="bonsApply">Filtrer</button>
								</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr> 
										    <th  >N° Bon Commande Facture</th>
											<th >N° Bon Commande Client</th>
											<th >Date</th>
											<th >Client</th>
											<th >Modifier</th>
											<th>Supprimer</th>
											 <th >Télécharger le Bon Commande</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($BonsCommandes)){
										foreach($BonsCommandes as $key){
										$infoFacture=$bonCommande->getInfosFactureByBonCommande($key['num_bon_commande']);
										
										foreach($infoFacture as $info);
										
											?>
											 <tr>
												<td>
												<?=$key['num_bon_commande']?>
												</td>
												 <td><?=$key['num_bon_commandeClient']?></td>
												<td><?=$key['date_bon_commande']?></td>
												<td>
												
												<?php if(!empty($key['client'])){
												@$nomClient=$bonCommande->getInfosClient($key['client']);
												if($nomClient){
											      echo @$nomClient['nom_client'];
												 
												}
												else {echo @$key['client'];}
												 } else {
													
													echo $info['nom_client'];
													} 
													?>
												</td>
												<th ><a href="?Bons_commandes&ModifierBC&BC=<?=$key['num_bon_commande']?>" class="btn btn-warning">Modifier</a></th>
												<th><a href="?Bons_commandes&deleteBonCommande=<?=$key['num_bon_commande']?>">Supprimer</th>												  
												  <td>
												   <?php if(!empty($key['piecejointe'])){?>
												   <a href="./pages/BonsCommandes/BonCommandes_piecesJointe/<?=$key['piecejointe']?>">Imprimer</a>
												   <?php } ?>
												   </td>
											 </tr>
									<?php }}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var table = document.getElementById('dataTable');
  var search = document.getElementById('bonsSearch');
  var btn = document.getElementById('bonsApply');
  function apply(){
    if(!table) return;
    var term = search ? (search.value || '').toLowerCase() : '';
    table.querySelectorAll('tbody tr').forEach(function(tr){
      var txt = (tr.innerText || '').toLowerCase();
      tr.style.display = term ? (txt.indexOf(term) !== -1 ? '' : 'none') : '';
    });
  }
  if (search) search.addEventListener('input', apply);
  if (btn) btn.addEventListener('click', apply);
});
</script>
