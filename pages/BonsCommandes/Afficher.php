<?php
include 'class/BonsCommandes.class.php';

$BonsCommandes=$bonCommande->getAll();
$selectedYear = '';
if (!empty($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year'])) {
	$selectedYear = $_GET['year'];
}
$bonCommandeYears = [];
if (!empty($BonsCommandes)) {
	foreach ($BonsCommandes as $row) {
		$year = isset($row['date_bon_commande']) ? substr((string)$row['date_bon_commande'], 0, 4) : '';
		if (preg_match('/^\d{4}$/', $year)) {
			$bonCommandeYears[$year] = true;
		}
	}
}
$bonCommandeYears = array_keys($bonCommandeYears);
rsort($bonCommandeYears, SORT_STRING);
if ($selectedYear !== '') {
	$BonsCommandes = array_values(array_filter($BonsCommandes, function ($row) use ($selectedYear) {
		$date = isset($row['date_bon_commande']) ? (string)$row['date_bon_commande'] : '';
		$num  = isset($row['num_bon_commande']) ? (string)$row['num_bon_commande'] : '';
		if (strpos($date, $selectedYear) === 0) {
			return true;
		}
		if (strpos($num, '/'.$selectedYear) !== false || strpos($num, $selectedYear.'/') === 0) {
		 return true;
		}
		return false;
	}));
}

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
								<div class="col-md-3">
									<label for="bonCommandeYearFilter" class="form-label">Filtrer par année</label>
									<select id="bonCommandeYearFilter" class="form-control">
										<option value="">Toutes les années</option>
										<?php foreach ($bonCommandeYears as $year) { ?>
											<option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-2 d-flex align-items-end">
									<button type="button" class="btn btn-secondary w-100" id="bonCommandeYearApply">Filtrer</button>
								</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-year-filter="#bonCommandeYearFilter" data-year-column="2">
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
										$bcSort = 0;
										$bcYear = '';
										if (!empty($key['num_bon_commande'])) {
											$parts = explode('/', $key['num_bon_commande']);
											$numero = isset($parts[0]) ? (int)$parts[0] : 0;
											$annee  = isset($parts[1]) ? (int)$parts[1] : 0;
											$bcSort = ($annee * 10000) + $numero;
											if ($annee > 0) {
												$bcYear = (string)$annee;
											}
										}
										$dateYear = '';
										if (!empty($key['date_bon_commande'])) {
											$maybeYear = substr((string)$key['date_bon_commande'], 0, 4);
											if (preg_match('/^\d{4}$/', $maybeYear)) {
												$dateYear = $maybeYear;
											}
										}
										$yearTokens = array_unique(array_filter([$bcYear, $dateYear]));
										$rowYearAttr = implode(' ', $yearTokens);
										
											?>
											 <tr<?php if (!empty($rowYearAttr)) { ?> data-year-values="<?= htmlspecialchars($rowYearAttr) ?>"<?php } ?>>
												<td data-order="<?=$bcSort?>">
												<?=$key['num_bon_commande']?>
												</td>
												 <td><?=$key['num_bon_commandeClient']?></td>
												<td><?php if($rowYearAttr!==''):?><span class="d-none year-marker"><?=htmlspecialchars($rowYearAttr)?></span><?php endif;?><?=$key['date_bon_commande']?></td>
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
(function(){
	var btn=document.getElementById('bonCommandeYearApply');
	if(btn){
		btn.addEventListener('click',function(){
			var select=document.getElementById('bonCommandeYearFilter');
			var year=select?select.value:'';
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
