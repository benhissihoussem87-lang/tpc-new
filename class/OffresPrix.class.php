<?php
include_once 'connexion.db.php';
class OffresPrix{
	private $cnx;
	public function __construct(){$this->cnx=connexion();}
	
	// Ajout Offre Prix (idempotent on num_offre)
	public function Ajout($num,$date,$client){
		// Use an upsert to avoid UNIQUE(num_offre) violations when re-adding
		// existing numbers after manual deletions in related tables.
		$sql = "INSERT INTO offre_prix (`num_offre`,`date`,`client`)
				VALUES (:num,:datev,:client)
				ON DUPLICATE KEY UPDATE `date` = VALUES(`date`), `client` = VALUES(`client`)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':num' => $num,
			':datev' => $date,
			':client' => $client,
		]);
	}
	
	// Modifier Offre Prix
	public function Modifier($num,$date,$client,$idOffre){
		$sql="update `offre_prix` SET  `num_offre`='$num',`date`='$date',`client`='$client' where id_offre='$idOffre' ";
		
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
		
	}
	// Ajout Projets Offres
	public function AjoutProjets_Offre($offre,$prix_unitaire,$qte,$tva,$remise,$prixForfitaire,$prixTTC,$projet,$adresse){
		// Explicit columns; let id auto-increment
		$projVal = (isset($projet) && $projet !== '' && is_numeric($projet) && (int)$projet > 0) ? (int)$projet : null;
		$adrVal = isset($adresse) ? trim((string)$adresse) : null;
		if ($adrVal === '') { $adrVal = null; }
		$sql = "INSERT INTO offres_projets (`offre`,`prix_unit_htv`,`qte`,`tva`,`remise`,`prixForfitaire`,`prixTTC`,`projet`,`adresseClient`)
				VALUES (:offre,:pu,:qte,:tva,:remise,:pf,:pttc,:projet,:adr)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':offre'=>$offre,
			':pu'=>$prix_unitaire,
			':qte'=>$qte,
			':tva'=>$tva,
			':remise'=>$remise,
			':pf'=>$prixForfitaire,
			':pttc'=>$prixTTC,
			':projet'=>$projVal,
			':adr'=>$adrVal,
		]);
	}
	
	
	// Modifier Projets Offres
	public function ModifierProjets_Offre($offre,$prix_unitaire,$qte,$tva,$remise,$prixForfitaire,$prixTTC,$projet,$adresse){
		// Original code re-inserts; preserve behavior but with explicit columns
		$projVal = (isset($projet) && $projet !== '' && is_numeric($projet) && (int)$projet > 0) ? (int)$projet : null;
		$adrVal = isset($adresse) ? trim((string)$adresse) : null;
		if ($adrVal === '') { $adrVal = null; }
		$sql = "INSERT INTO offres_projets (`offre`,`prix_unit_htv`,`qte`,`tva`,`remise`,`prixForfitaire`,`prixTTC`,`projet`,`adresseClient`)
				VALUES (:offre,:pu,:qte,:tva,:remise,:pf,:pttc,:projet,:adr)";
		$st = $this->cnx->prepare($sql);
		return $st->execute([
			':offre'=>$offre,
			':pu'=>$prix_unitaire,
			':qte'=>$qte,
			':tva'=>$tva,
			':remise'=>$remise,
			':pf'=>$prixForfitaire,
			':pttc'=>$prixTTC,
			':projet'=>$projVal,
			':adr'=>$adrVal,
		]);
	}
	
	public function AfficherProjets_By_Offre($offre){
		 $sql="SELECT * FROM offres_projets as fO,projet as p where fO.projet=p.id and fO.offre='$offre'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	// Afficher Offres 
	public function getAllOffres(){
		$sql="SELECT * FROM offre_prix as Of,clients as clt where Of.client=clt.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	public function AfficherOffres(){
		$sql="SELECT * FROM offre_prix as Of,clients as clt where Of.client=clt.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
	// delete Offre 
	public function deleteOffre($id){
		$sql="delete FROM offre_prix where id_offre='$id'";
		 $result=$this->cnx->exec($sql);
		 if($result) return true;
		 else return false;
	}

    // Offre By Id
	public function getOffre_Prix($id){
		$sql="SELECT * FROM offre_prix where id='$id' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	
    public function VerifExistenceOffre($offre){
		  $sql="SELECT * FROM offre_prix where num_offre='$offre' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
/************************  Archive Offre  ******************************************/
	// Ajout Archive Offre Prix
	public function AjoutArchive($num,$date,$client,$facture,$joindre){
        $sql = "INSERT INTO archiveoffre (num_offre, factureArchive, client, date_archive, piece_jointe)
                VALUES (:num,:facture,:client,:datev,:joindre)";
        $st = $this->cnx->prepare($sql);
        return $st->execute([
            ':num' => $num,
            ':facture' => $facture,
            ':client' => $client,
            ':datev' => $date,
            ':joindre' => $joindre,
        ]);
	}
  // Afficher les Offres de Prix Archive 
  public function getOffres_PrixArchive(){
		 $sql="SELECT * FROM archiveoffre as OfA,clients as clt,archivefacture as FA where OfA.client=clt.id and OfA.factureArchive =FA.num_fact ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
// Afficher les Offres de Prix Archive By Client
  public function getOffres_PrixArchiveByClient($client){
		 $sql="SELECT * FROM archiveoffre as OfA,clients as clt,archivefacture as FA where OfA.client=clt.id and OfA.factureArchive =FA.num_fact and OfA.client='$client'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
//detail Offre 
public function detailOffre($idOffre){
		 $sql="SELECT * FROM offre_prix where  id_offre ='$idOffre' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}
	
public function detailOffreByNumOffre($idOffre){
		 $sql="SELECT * FROM offre_prix as ofP,clients as clt where  ofP.num_offre ='$idOffre' and ofP.client=clt.id ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
		return $resultat;
	}

	
	public function get_All_AdressesClient_ProjetsOffre($offre){
		$sql="SELECT distinct(adresseClient) FROM offres_projets as fP where  fP.offre='$offre' and fP.adresseClient!='' ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
	
public function get_All_AdressesClient_ProjetsByOffre($offre){
		 $sql="SELECT distinct(adresseClient) FROM offres_projets where  offre='$offre'  ";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
/******** Afficher les projets de l'offre selectionner***********/
public function get_AllProjets_ByOffre($offre){
		 $sql="SELECT * FROM offres_projets as fP,projet as p where  fP.offre='$offre' and fP.projet=p.id";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
		return $resultat;
	}
// Delete All Projet By Offre 

	public function delete_All_Projets_By_Offre($offre){
		// delete Offres_Projet by numFacture
		 $sql="delete from offres_projets where offre='$offre'";
		
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}	
/************ delete Offre By Num Offre et Adresse ***********/
public function delete_All_Projets_By_OffreAndAdresse($offre,$adresse){
		// delete Offres_Projet by numFacture
		 $sql="delete from offres_projets where offre='$offre' and adresseClient='$adresse'";
		
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
	}
public function getAdresseOffreProjetByOffre($offre){
		  $sql="SELECT distinct(adresseClient) FROM offres_projets  where offre='$offre'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetchAll();
	
		return $resultat;
	}

/************ Modifier Adresse Offre ***************/
public function ModifierAdresseOffre($adresse,$nouveauAdresse,$offre){
		
		 $sql="update offres_projets set `adresseClient`='$nouveauAdresse' where adresseClient='$adresse' and offre='$offre'";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
		
	}
// Delete Offre By Adresse 

	public function deleteOffreByAdresse($offre,$adresse){
		  $sql="delete from offres_projets where offre ='$offre' and adresseClient='$adresse'";
		 $result=$this->cnx->exec($sql);
		 if($result)return true;
		 else return false;
		
	}	
/************ verification existance offre & adresse  ********************/	
public function VerifOffredansFacturesProjet($offre,$adresse){
 $sql="SELECT * FROM offres_projets  where offre='$offre' and adresseClient='$adresse'";
		$req=$this->cnx->query($sql);
		$resultat=$req->fetch();
	    return $resultat;
	}
}
$offre=new OffresPrix();


