<?php
require __DIR__.'/../class/connexion.db.php';

$cnx = connexion();
$targets = [
    ['num' => '99/2025', 'id' => 803],
    ['num' => '08/2025', 'id' => 685],
];

echo "== columns offres_projets ==\n";
var_export($cnx->query("SHOW COLUMNS FROM offres_projets")->fetchAll(PDO::FETCH_ASSOC));
echo "\n\n";

foreach ($targets as $t) {
    $num = $t['num'];
    $id  = $t['id'];
    echo "== offre_prix for num {$num} or id {$id} ==\n";
    $st = $cnx->prepare("SELECT id_offre,num_offre,client,date FROM offre_prix WHERE num_offre = ? OR id_offre = ?");
    $st->execute([$num, $id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    var_export($rows);
    echo "\n\n";

    echo "== offres_projets rows with offer match ==\n";
    $st = $cnx->prepare("
        SELECT id_Projets_Offre, offre, projet, prix_unit_htv, qte, tva, remise, prixForfitaire, prixTTC, adresseClient
        FROM offres_projets
        WHERE offre = ?
           OR TRIM(offre) = TRIM(?)
           OR REPLACE(TRIM(offre), ' ', '') = REPLACE(TRIM(?), ' ', '')
           OR REPLACE(REPLACE(TRIM(offre), ' ', ''), '/', '') = REPLACE(REPLACE(TRIM(?), ' ', ''), '/', '')
           OR offre = ?
    ");
    $n1 = $n2 = $n3 = $n4 = $num;
    $idstr = (string)$id;
    $st->execute([$n1, $n2, $n3, $n4, $idstr]);
    $rows2 = $st->fetchAll(PDO::FETCH_ASSOC);
    var_export($rows2);
    echo "\n-------------------------\n";

    echo "== facture_projets rows with facture match ==\n";
    $st = $cnx->prepare("SELECT * FROM facture_projets WHERE facture = ?");
    $st->execute([$num]);
    var_export($st->fetchAll(PDO::FETCH_ASSOC));
    echo "\n=========================\n";
}
