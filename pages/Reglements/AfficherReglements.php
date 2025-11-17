<?php
include 'class/Reglements.class.php';


$Reglements=$reglement->getAll();
$reglementYears = [];
if (!empty($Reglements)) {
	foreach ($Reglements as $row) {
		$year = isset($row['date']) ? substr((string)$row['date'], 0, 4) : '';
		if (preg_match('/^\d{4}$/', $year)) {
			$reglementYears[$year] = true;
		}
	}
}
$reglementYears = array_keys($reglementYears);
rsort($reglementYears, SORT_STRING);
	
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                      
                        
                        <div class="card-body">
							<div class="row mb-3">
								<div class="col-md-3">
									<label for="reglementsYearFilter" class="form-label">Filtrer par ann�e</label>
									<select id="reglementsYearFilter" class="form-control">
										<option value="">Toutes les ann�es</option>
										<?php foreach ($reglementYears as $year) { ?>
											<option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-year-filter="#reglementsYearFilter" data-year-column="5">
                                    <thead>
                                        <tr> 
										<th>Facture</th>
										   
											<th >Client</th>
											<th width="10%" >Prix TTC</th>
											<th >Reglement</th>
											<th >Type Reglement</th>
											
											<th width="15%">Date Cheque</th>
											<th >Date Retenue</th>
											<th >Piece Retenue</th>
											<th>Ajouter</th>
											<th >Modifier</th>
										</tr>
                                    </thead>
                                    <tbody>
									 <?php if(!empty($Reglements)){
										foreach($Reglements as $key){
						$DetailReglements=$reglement->getAllReglement($key['num_fact']);
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
						?>
									 <tr<?php if (!empty($rowYearAttr)) { ?> data-year-values="<?= htmlspecialchars($rowYearAttr) ?>"<?php } ?>>
									<td data-order="<?=$factureSort?>"><?=$key['num_fact']?></td>
									<td><?=$key['nom_client']?></td>
									 <td align="right">
									
									 <?php foreach($DetailReglements as $cle){?>
									<?=$cle['prix_ttc']?>
									 <?php }?>
									 
									 </td>
									 <td>
									<?php foreach($DetailReglements as $cle){?>
									 <?=$cle['etat_reglement']?>
									 <?php }?>
									 
									 </td>
									 
									 <td>
									 
									 <?php foreach($DetailReglements as $cle){$typesReglements=explode(',',$cle['TypeReglement']);
					    for($i=0;$i<count($typesReglements);$i++){
							//if(!empty($typesReglements[$i])){
							echo $typesReglements[$i].'<br><br>';
							//}
						}
					 }?>
									
									 </td>
									 
									<!-- <td>
									<?php /*foreach($DetailReglements as $cle){
									$num_cheques=explode(',',$cle['num_cheque']);
									for($i=0;$i<count($num_cheques);$i++){
										if(!empty($num_cheques[$i])){
										echo $num_cheques[$i].'<hr>';
										//echo $num_cheques[$i].'<hr>';
										}
									}
								  }*/?>
									 </td>-->
									 <td>
									 <?php
										if ($rowYearAttr !== '') {
											echo '<span class="d-none year-marker">'.htmlspecialchars($rowYearAttr).'</span>';
										}
									 ?>
									 <?php foreach($DetailReglements as $cle){
										 $date_cheques=explode(',',$cle['date_cheque']);
										// echo '<b> Nb Date '.count($date_cheques).'</b><br>';
									for($i=0;$i<count($date_cheques);$i++){
										//if(!empty($date_cheques[$i])){
										echo $date_cheques[$i].'<br><br>';
										//}
									}
								  }?>
									 </td>
									  <td>
									  <?php foreach($DetailReglements as $cle){$retenue_dates=explode(',',$cle['retenue_date']);
									for($i=0;$i<count($retenue_dates);$i++){
										if(!empty($retenue_dates[$i])){
										echo $retenue_dates[$i].'<hr>';
										}
									}
								  }?>
									
									 </td>
									 <td>
												<?php if($cle['pieceRs']){?>
												<a href="./pages/Reglements/PiecesRS/<?=$cle['pieceRs']?>">Télécharger </a>
												<?php } ?>
												
												
												</td>
									<td>
									 <a href="?Reglements&Add&Facture=<?=$key['num_fact']?>" class="btn btn-primary active">Ajout </a>
									</td>
									 <td>
									  <a href="?Reglements&Modifier&Facture=<?=$key['num_fact']?>" class="btn btn-warning">Modifier</a>
									 
									 </td>
									  </tr>
									 <?php }}?>
									</tbody>


								   </table>
                            </div>
                        </div>
                    </div>
