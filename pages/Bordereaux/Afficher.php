<?php
include 'class/Bordereaux.class.php';

$bordereaux=$bordereau->getAll();
$selectedYear = '';
if (!empty($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year'])) {
	$selectedYear = $_GET['year'];
}
$bordereauRows = [];
$bordereauYears = [];
if (!empty($bordereaux)) {
	foreach ($bordereaux as $factureRow) {
		$lines = $bordereau->getAllFacturesBordereaux($factureRow['num_fact']);
		$rowYear = '';
		if (!empty($lines)) {
			foreach ($lines as $line) {
				$maybeYear = isset($line['date']) ? substr((string)$line['date'], 0, 4) : '';
				if (preg_match('/^\d{4}$/', $maybeYear)) {
					$rowYear = $maybeYear;
					$bordereauYears[$maybeYear] = true;
					break;
				}
			}
		}
		if ($rowYear === '' && !empty($factureRow['date'])) {
			$maybeYear = substr((string)$factureRow['date'], 0, 4);
			if (preg_match('/^\d{4}$/', $maybeYear)) {
				$rowYear = $maybeYear;
				$bordereauYears[$maybeYear] = true;
			}
		}
		$bordereauRows[] = [
			'facture' => $factureRow,
			'lines' => $lines,
			'year' => $rowYear,
		];
	}
}
$bordereauYears = array_keys($bordereauYears);
rsort($bordereauYears, SORT_STRING);
if ($selectedYear !== '') {
	$bordereauRows = array_values(array_filter($bordereauRows, function ($row) use ($selectedYear) {
		$rowYear = isset($row['year']) ? (string)$row['year'] : '';
		$factureNum = isset($row['facture']['num_fact']) ? (string)$row['facture']['num_fact'] : '';
		if ($rowYear === $selectedYear) {
			return true;
		}
		if (strpos($factureNum, '/'.$selectedYear) !== false || strpos($factureNum, $selectedYear.'/') === 0) {
			return true;
		}
		return false;
	}));
}

/*********** delete Facture ***************/
 if(isset($_GET['deleteFacture'])){
	 if($facture->deleteFacture($_GET['deleteFacture'])){
		 echo "<script>document.location.href='main.php?Factures'</script>";
	 }
 }
 
  if(isset($_REQUEST['btnSubmitUpdate'])){
@$types=$_POST['TypeBordereaux'].'<br>'.str_replace("'","\'",$_POST['TypeBordereaux']);
@$dates=$_POST['DateBordereaux'].'<br>'.$_POST['date'];
if($_FILES['bordereau']['name']!=''){
$docs=$_POST['DocsBordereaux'].'<br>'.$_FILES['bordereau']['name'];
}
else {
	$docs=$_POST['DocsBordereaux'];
}

	 if($bordereau->Modifier(@$_POST['idBordereaux'],@$_FILES['bordereau']['name'],@$_POST['DateBordereaux'],str_replace("'","\'",@$_POST['TypeBordereaux'])))
	{
	if($_FILES['bordereau']['name']!=''){
	@copy($_FILES['bordereau']['tmp_name'],'pages/Bordereaux/bordereaux_piecesJointe/'.$_FILES['bordereau']['name']);
		}
		
		echo "<script>document.location.href='main.php?Bordereaux'</script>";
		}
else {echo "<script>alert('Erreur !!! ')</script>";}

 }
		
 ?>
<!-- DataTales Example -->
                    <div class="card shadow mb-4">
                       <div style="width:100%;text-align:center" class="col-12">
                            <a href="?Bordereaux&Add" class="btn btn-primary active " style="position:relative; top:20px;"  >Ajout Bordereaux</a>
							</div>
                        
                        <div class="card-body">
							<div class="row mb-3">
								<div class="col-md-3">
									<label for="bordereauYearFilter" class="form-label">Filtrer par année</label>
									<select id="bordereauYearFilter" class="form-control">
										<option value="">Toutes les années</option>
										<?php foreach ($bordereauYears as $year) { ?>
											<option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-md-2 d-flex align-items-end">
									<button type="button" class="btn btn-secondary w-100" id="bordereauYearApply">Filtrer</button>
								</div>
							</div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" data-year-filter="#bordereauYearFilter" data-year-column="2">
                                    <thead>
                                        <tr> 
										    <th >Numéro Bordereau</th>
											<th >Num Facture</th>
											<th width="15%">Date Bordereau</th>
											<th >Type Bordereau</th>
											
											<th  width="15%" >Supp/Mod</th>
											<th >Télécharger</th>
											<th >Imprimer</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
									<?php if(!empty($bordereauRows)){
										foreach($bordereauRows as $row){
					$key = $row['facture'];
					$detailBoredereau=$bordereau->GetInfosBordereau($key['num_fact']);
					$AllBordereauxByFacture=$row['lines'];
					$rowYear = $row['year'];
					$bordereauSort = 0;
					$numFactYear = '';
					if (!empty($key['num_fact'])) {
						$parts = explode('/', $key['num_fact']);
						$numero = isset($parts[0]) ? (int)$parts[0] : 0;
						$annee  = isset($parts[1]) ? (int)$parts[1] : 0;
						$bordereauSort = ($annee * 10000) + $numero;
						if ($annee > 0) {
							$numFactYear = (string)$annee;
						}
					}
					$yearTokens = array_unique(array_filter([$rowYear, $numFactYear]));
					$rowYearAttr = implode(' ', $yearTokens);
					
											?>
<div class="modal fade" id="ModalUpdateBordereaux<?=$key['num_fact']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content"> 
	<form method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modifier Bordereaux</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       
          <div class="mb-3">
          
            <input type="text" class="form-control" name="idBordereaux" value="<?=$cle['id_bordereau']?>">
          </div>
		   <div class="mb-3 col-12" >
		   <input type="hidden" name="TypeBordereauxS" value="<?=$detailBoredereau['type']?>"/>
		   <input type="hidden" name="DateBordereauxS" value="<?=$detailBoredereau['date']?>"/>
		   <input type="hidden" name="DocsBordereauxS" value="<?=$detailBoredereau['pieces_jointe']?>"/>
				<label for="type" class="col-form-label">Type :</label>
				<input list="types" class="form-control" value="<?=$detailBoredereau['type']?>" name="TypeBordereaux" id="type" placeholder="Choisir/ecrire le type de bordereau">
				<datalist id="types">
				<option value="ATTESTATION GAZ & ATTESTATION ELECTRIQUE">
				  <option value="Attestation">
				  <option value="Rapport">
				</datalist>
			</div>
			 <div class="mb-3 col-12" >
				<label for="date" class="col-form-label">Date :</label>
				<input type="date"  required  class="form-control" value="<?=$detailBoredereau['date']?>" id="date" name="DateBordereaux"/>
				   
			</div>
			
          <div class="mb-3">
            <label for="message-text" class="col-form-label">Bordereaux</label>
            <input type="file" class="form-control" id="message-text" name="bordereau">
          </div>
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="btnSubmitUpdate">Modifier</button>
      </div>
	  </form>
    </div>
  </div>
</div>
<?php if(!empty($AllBordereauxByFacture)){?>
						<!--  Fin ModalUpdate Bordereaux-->
	                 <tr<?php if (!empty($rowYearAttr)) { ?> data-year-values="<?= htmlspecialchars($rowYearAttr) ?>"<?php } ?>>
												<td data-order="<?=$bordereauSort?>">
												
												<?=$key['num_fact']?>
												
												
												</td>
												 <td><?=$key['num_fact']?></td>
												<td>
												<?php if($rowYearAttr!==''){?><span class="d-none year-marker"><?=htmlspecialchars($rowYearAttr)?></span><?php } ?>
												<?php foreach($AllBordereauxByFacture as $cle){?>
												<?=$cle['date']?>
												<hr>
												<?php }?>
												
												</td>
												 <td>
												 <?php foreach($AllBordereauxByFacture as $cle){?>
												<?=$cle['type']?>
												<hr>
												
												<?php }?>
												 </td>
												
												  <td>
												   <?php foreach($AllBordereauxByFacture as $cle){?>
												  <a href="#" class="btn btn-danger">Supp</a>
												  
												   <a href="?Bordereaux&Update=<?=$cle['id']?>" class="btn btn-warning" > Mod</a>
												   <hr>
												   <?php } ?>
												   </td>
												   <td>
												   <?php 
												   foreach($AllBordereauxByFacture as $cle){
												   if($cle['pieces_jointe']!=''){
													 $pieces=explode('<br>', $cle['pieces_jointe']);  
													   for($i=0;$i<count($pieces);$i++){
														   if($pieces[$i]!=''){
													   ?>
												  <a href="./pages/Bordereaux/bordereaux_piecesJointe/<?=$pieces[$i]?>">Télécharger : <?=$pieces[$i]?></a><br>
													   <?php } }
												   } }
												   ?>
												   </td>
												      <td>
												   
												   <a href="./pages/Bordereaux/ModeleBordereaux.php?bordereau=<?=$key['num_fact']?>">Imprimer</a></td>
											 </tr>
									<?php }}}?>
									 </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
<script>
(function(){
	var btn=document.getElementById('bordereauYearApply');
	if(btn){
		btn.addEventListener('click',function(){
			var select=document.getElementById('bordereauYearFilter');
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
