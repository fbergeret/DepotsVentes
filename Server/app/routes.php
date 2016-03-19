<?php
/* ------------------------------DEPOT------------------------------ */
//Recuperer le depot id ------>  OK
$app->get('/api/depots/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $depot=Depots::find($id);
    if (!empty($depot)) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($depot));
    } else {
        $response = $response->withStatus(404, 'Depot inexistant');
    }
    return $response;
});

//Recuperer tous les depots ------>  OK
$app->get('/api/depots', function ($request, $response) {
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $depots=Depots::all();
    if(count($depots)>0) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($depots));
    } else {
        $response = $response->withStatus(404, 'Aucun depot enregistre');
    }
    return $response;
});

//Creer un depot ------>  OK
$app->post('/api/depots', function ($request, $response) {
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "POST");
    if (!empty($params['nom'])
        && !empty($params['prenom'])
        && !empty($params['email'])
        && !empty($params['adresse'])
        && !empty($params['telephone'])
    ) {
        $email=Depots::where('email', $params['email'])->first();
        if(count($email)==0){
            $idDepot = Depots::addDepot($params);
            /*Depots::insert(array('nom' => $params['nom'],
                                'prenom' => $params['prenom'],
                                'email' => $params['email'],
                                'adresse' => $params['adresse'],
                                'telephone' => $params['telephone']));*/
            $response = $response->withStatus(201, 'Depot created');
            $response = $response->withHeader('Content-Type', 'application/json');
            $find=Depots::where('email', $params['email'])->first();
            // $find=Depots::find($idDepot);
            $response = $response->write(json_encode($find));
        }
        else {
            $response = $response->withStatus(400, 'email already use');
        }
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Modifier les information du deposant du dépot id ------>  NON
$app->put('/api/depots/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "PUT");
    if (!empty($params['nom'])
        && !empty($params['prenom'])
        && !empty($params['email'])
        && !empty($params['adresse'])
        && !empty($params['telephone'])
    ) {
        $findDepot = Depots::find($id);
        if (!empty($findDepot)){
            $nom = $findDepot->nom;
            $prenom = $findDepot->prenom;
            $email = $findDepot->email;
            $adresse = $findDepot->adresse;
            $telephone = $findDepot->telephone;
            if ($nom != $params['nom']){
                $findDepot->nom = $params['nom'];
            }
            if ($prenom != $params['prenom']){
                $findDepot->prenom = $params['prenom'];
            }
            if ($email != $params['email']){
                $findEmail = Depots::where('email','=',$email)->first();
                if(count($findEmail)==0){
                    $findDepot->email = $params['email'];
                }
                else{
                    $response = $response->withStatus(400, 'email already use');
                }
            }
            if ($adresse != $params['adresse']){
                $findDepot->adresse = $params['adresse'];
            }
            if ($telephone != $params['telephone']){
                $findDepot->telephone = $params['telephone'];
            }
            $findDepot->save();
            $response = $response->withStatus(201, 'Product updated');
            $response = $response->withHeader('Content-Type', 'application/json');
            $response = $response->write(json_encode($findDepot));
        }
        else{
            $response = $response->withStatus(400, 'Depot inexistant');
        }
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Supprimer le depot id (supprimer depot et produits du depot) ------>  OK
$app->delete('/api/depots/{id}', function ($request, $response, $args) {
    $idDepot = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    $depot=Depots::find($idDepot);
    if (!empty($depot)) {
        $depot->delete();
        Produits::where('id_depot', "=", $idDepot)->delete();
        $response = $response->withStatus(200, 'Depot et produits supprimees');
    } else {
        $response = $response->withStatus(404, 'Depot inexistant');
    }
    return $response;
});

//Ajouter des produits dans un depots ------>  OK
$app->post('/api/depots/{id_depot}/products', function ($request, $response, $args) {
    $idDepot = $args['id_depot'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "POST");
    if (!empty($params['reference'])
        && !empty($params['prix'])
        && !empty($params['description'])
    ) {    
        require 'app/config.php';
        require 'app/opendb.php';
        $depot = Depots::find($idDepot);
        /*mysql_query('SELECT * FROM depots WHERE id="'.$idDepot.'"');
        $obj = mysql_fetch_object($find);*/
        if(! empty($depot)){
            $produit = Produits::where('reference', '=', $params['reference'])->first();
            /*$find = mysql_query('SELECT * FROM produits WHERE reference="'.$params['reference'].'"');
            $depot = mysql_fetch_object($find);*/
            if (empty($produit)){
                /*$sql = "INSERT INTO produits (reference, prix, description, etat, id_depot)
                        VALUES ('".$params['reference']."','"
                                .$params['prix']."','"
                                .$params['description']."',
                                'En stock','"
                                .$idDepot."')";
                $insert = mysql_query($sql);*/
                $insert = Produits::addProduit($params, $idDepot);
                if($insert){
                    $response = $response->withStatus(201, 'Product created');
                    $response = $response->withHeader('Content-Type', 'application/json');
                    $find = mysql_query('SELECT * FROM produits WHERE reference="'.$params['reference'].'"');
                    $depot = mysql_fetch_object($find);
                    $response = $response->write(json_encode($depot));
                }
                else{
                    echo'error insertion';
                }
            }else{
                $response = $response->withStatus(400, 'Reference deja utilisee');
            }
        } else {
            $response = $response->withStatus(400, 'Depot inexitant');
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Supprimer le produit id du depot id_depot ------>  OK
$app->delete('/api/depots/{id_depot}/products/{reference}', function ($request, $response, $args) {
    $idDepot = $args['id_depot'];
    $refProduct = $args['reference'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    $depot = Depots::find($idDepot);
    if(!empty($depot)){
        $produit = Produits::where('reference','=',$refProduct)->where('id_depot','=',$idDepot)->get();
        if (count($produit)>0) {
            Produits::where('reference','=',$refProduct)->delete();
            $response = $response->withStatus(200, 'Product deleted');
        } else {
            $response = $response->withStatus(404, 'Reference produit inexistante dans ce depot');
        }
    } else{
        $response = $response->withStatus(400, 'Depot inexitant');    
    }
    return $response;
});

//Modifier le produit id du depot id_depot ------>  OK
$app->put('/api/depots/{id_depot}/products/{reference}', function ($request, $response, $args) {
    $idDepot = $args['id_depot'];
    $refProduct = $args['reference'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "PUT");
    if (!empty($params['prix'])
        && !empty($params['description'])
        && !empty($params['etat'])
    ) {
        require 'app/config.php';
        require 'app/opendb.php';
        /*$findDepot = mysql_query('SELECT * FROM depots WHERE id="'.$idDepot.'"');
        $res = mysql_fetch_object($findDepot);*/
        $depot = Depots::find($idDepot);
        if ($depot)
        {
            /*$findProduct = mysql_query('SELECT * FROM produits WHERE reference="'.$refProduct.'"');
            $obj = mysql_fetch_object($findProduct);*/
            $produit = Produits::where('reference', '=', $refProduct)->first();
            if($produit){
                /*$sql = "UPDATE produits SET prix='".$params['prix']."',
                                    description='".$params['description']."',
                                    etat='".$params['etat']."' 
                                    WHERE reference='".$refProduct."'"; 
                $update = mysql_query($sql);*/
                $update = Produits::updateProduit($refProduct, $params);
                if($update){
                    $response = $response->withStatus(201, 'Product updated');
                    $response = $response->withHeader('Content-Type', 'application/json');
                    /*$find = mysql_query('SELECT * FROM produits WHERE reference="'.$refProduct.'"');
                    $depot = mysql_fetch_object($find);*/
                    $produit = Produits::where('reference', '=', $refProduct)->first();
                    $response = $response->write(json_encode($produit));
                }
                else{
                    echo'error update';
                }
            }
            else {
                $response = $response->withStatus(400, 'Produit inexistant');
            }
        }
        else{
            $response = $response->withStatus(400, 'Depot inexistant');
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Recuperer les produits d un depots -----> OK
$app->get('/api/depots/{id_depot}/products', function ($request, $response, $args) {
    $idDepot = $args['id_depot'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $depot = Depots::find($idDepot);
    if(!empty($depot))
    {
        $produits = Produits::where('id_depot','=',$idDepot)->get();
        if(count($produits)>0){
            $response = $response->withHeader('Content-Type', 'application/json');
            $response = $response->write(json_encode($produits));
        }
        else{
            $response = $response->withStatus(404, 'Aucun produits');
        }
    } 
    else{
        $response = $response->withStatus(404, 'Depot inexistant');
    }
    return $response;
});

/* ------------------------------VENTE------------------------------ */
//Creer une vente ------>  NON
$app->post('/api/sales', function ($request, $response) {
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "POST");
    require 'app/config.php';
    require 'app/opendb.php';
    $vente = Ventes::addVente();
    if($vente){
        $findID = Ventes::orderBy('id', 'desc')->first();
        $response = $response->withStatus(201, 'Vente created');
        $response = $response->write(json_encode($findID));
    }
    else{
        echo'error insertion';
    } 
    require 'app/closedb.php';
    return $response;
});

//Ajouter un produits dans une vente : produit (ref) dans la vente (id) ------>  NON
$app->put('/api/sales/{id_sale}/products/{ref}', function ($request, $response, $args) {
    $idSale = $args['id_sale'];
    $ref = $args['ref'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "PUT");
    require 'app/config.php';
    require 'app/opendb.php';
    $findProduct = mysql_query("SELECT * FROM produits WHERE reference=".$ref);
    $obj = mysql_fetch_object($findProduct);
    if ($obj) {
        //requete update produit dans vente, changer etat produit de "en stock" à "en cours de vente"
        $sql = "UPDATE produits SET etat='En cours de vente',
                                    id_vente='".$idSale."' 
                                    WHERE reference='".$ref."'"; 
        $update = mysql_query($sql);
        $find = mysql_query("SELECT * FROM produits WHERE reference=".$ref);
        $obj = mysql_fetch_object($find);
        $response = $response->write(json_encode($obj));
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus(200, 'Produit ajoute de la vente');
    } else {
        $response = $response->withStatus(404, 'Reference produit inexistante');
    }
    require 'app/closedb.php';
    return $response;
});

//Recuperer les produits d'une vente ------>  NON
$app->get('/api/sales/{id_sale}/products', function ($request, $response, $args) {
    $idSale = $args['id_sale'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    require 'app/config.php';
    require 'app/opendb.php';
    $vente = mysql_query('SELECT * FROM ventes WHERE id ='.$idSale);
    $produits = mysql_query('SELECT * FROM produits WHERE id_vente='.$idSale);
    if(mysql_num_rows($produits) !== 0)
    {
        while ($row = mysql_fetch_assoc($produits)) {
            $tab[] = $row;
        }
        $response = $response->write(json_encode($tab));
    } 
    if(mysql_num_rows($vente)==0) {
        $response = $response->withStatus(404, 'Vente inexistante');
    }
    require 'app/closedb.php';
    return $response;
});

//Supprimer un produit dans une vente : produit (ref) de la vente (id) ------>  NON
$app->delete('/api/sales/{id_sale}/products/{ref}', function ($request, $response, $args) {
    $id_vente = $args['id_sale'];
    $ref = $args['ref'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    require 'app/config.php';
    require 'app/opendb.php';
    $produit = mysql_query('SELECT * FROM produits WHERE reference="'.$ref.'" AND id_vente="'.$id_vente.'"');
    $obj = mysql_fetch_object($produit);
    if ($obj) {
        // remettre le produits avec id_vente=0 et etat=en stock
        $sql = "UPDATE produits SET etat='En stock',
                                    id_vente=0 
                                    WHERE reference='".$ref."'"; 
        $update = mysql_query($sql);
        $response = $response->withStatus(200, 'Produit retiré de la vente');
    } else {
        $response = $response->withStatus(404, 'Produit inexistant');
    }
    require 'app/closedb.php';
    return $response;
});

//Recuperer la vente id : information acheteur ------>  OK
$app->get('/api/sales/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $vente = Ventes::find($id);
    if (!empty($vente)) {
        $response = $response->write(json_encode($vente));
        $response = $response->withHeader('Content-Type', 'application/json');
    } else {
        $response = $response->withStatus(404, 'Vente inexistante');
    }
    return $response;
});

//Ajouter les informations de l'acheteur à la vente id ------>  NON
$app->put('/api/sales/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "PUT");
     if (!empty($params['nom'])
        && !empty($params['prenom'])
        && !empty($params['adresse'])
        && !empty($params['ville'])
        && !empty($params['email'])
        && !empty($params['telephone'])
    ) {    
        require 'app/config.php';
        require 'app/opendb.php';
        $vente = mysql_query('SELECT * FROM ventes WHERE id='.$id);
        $obj = mysql_fetch_object($vente);
        if(! empty($obj)){
            
            $sql = "UPDATE ventes SET nom='".$params['nom']."',
                                    prenom='".$params['prenom']."',
                                    adresse='".$params['adresse']."',
                                    ville='".$params['ville']."',
                                    email='".$params['email']."',
                                    telephone='".$params['telephone']."'
                                WHERE id='".$id."'"; 
            $update = mysql_query($sql);
            if($update){
                $response = $response->withStatus(201, 'Vente updated');
                $response = $response->withHeader('Content-Type', 'application/json');
                $find = mysql_query('SELECT * FROM ventes WHERE id='.$id);
                $vente = mysql_fetch_object($find);
                $response = $response->write(json_encode($vente));
            }
            else{
                echo'error update';
            }
        } else {
            $response = $response->withStatus(400, 'Vente inexitanet');
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Recuperer toute les ventes ------>  OK 
$app->get('/api/sales', function ($request, $response) {
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $ventes = Ventes::all();
    if(count($ventes) !== 0) {
        $response = $response->write(json_encode($ventes));
        $response = $response->withHeader('Content-Type', 'application/json');
    } else {
        $response = $response->withStatus(404, 'Aucune vente enregistre');
    }
    return $response;
});

//Supprimer la vente id ------>  NON
$app->delete('/api/sales/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    require 'app/config.php';
    require 'app/opendb.php';
    $vente = mysql_query('SELECT * FROM ventes WHERE id='.$id);
    $obj = mysql_fetch_object($vente);
    if ($obj) {
        // remettre les produits avec id_vente=0 et etat=en stock
        $sql = "UPDATE produits SET etat='En stock',
                                    id_vente=0 
                                    WHERE id_vente='".$id."'"; 
        $update = mysql_query($sql);
        $v = mysql_query('DELETE FROM ventes WHERE id='.$id);
        $response = $response->withStatus(200, 'Vente deleted');
    } else {
        $response = $response->withStatus(404, 'Vente inexistante');
    }
    require 'app/closedb.php';
    return $response;
});

/* ------------------------------PAIMENT VENTE------------------------------ */
// Ajouter un paiement à une vente ------>  NON (creer table paiements)
$app->post('/api/sales/{id_sale}/payments', function ($request, $response, $args) {
    $id = $args['id_sale'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "POST");
    if (!empty($params['prix'])
    ) {    
        require 'app/config.php';
        require 'app/opendb.php';
        $Vente = mysql_query('SELECT * FROM ventes WHERE id='.$id);
        $obj = mysql_fetch_object($Vente);
        if ($obj) {
            $sql = "INSERT INTO paiements (prix)
                    VALUES ('".$params['prix']."')";
            $insert = mysql_query($sql);
            if($insert){
                $response = $response->withStatus(201, 'Mode de paiement ajoute');
            }
            else{
                echo'error insertion';
            }
        } else {
            $response = $response->withStatus(404, 'Vente inexistantd');
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

// Supprimer un paiement ------>  NON (creer table paiements)
$app->delete('/api/sales/{id_sale}/payments', function ($request, $response, $args) {
    $id = $args['id_sale'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    require 'app/config.php';
    require 'app/opendb.php';
    $produit = mysql_query('SELECT * FROM paiements WHERE id='.$id);
    $obj = mysql_fetch_object($produit);
    if ($obj) {
        $produit = mysql_query('DELETE FROM paiements WHERE id='.$id);
        $response = $response->withStatus(200, 'Paiment supprime');
    } else {
        $response = $response->withStatus(404, 'Paiment inexistant');
    }
    require 'app/closedb.php';
    return $response;
});


/* ------------------------------MODE DE PAIMENT------------------------------ */
//Recuperer les modes de paiements ------>  NON (creer table modepaiements)
$app->get('/api/payments', function ($request, $response) {
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    require 'app/config.php';
    require 'app/opendb.php';
    $modePaiment = mysql_query('SELECT * FROM modepaiements');
    if(mysql_num_rows($modePaiment) !== 0) {
        while ($row = mysql_fetch_assoc($modePaiment)) {
            $tab[] = $row;
        }
        $response = $response->write(json_encode($tab));
    } else {
        $response = $response->withStatus(404, 'Aucun mode de paiement enregistre');
    }
    require 'app/closedb.php';
    return $response;
});

//Ajouter un mode de paiement ------> NON (creer table modepaiements)
$app->post('/api/payments', function ($request, $response) {
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "POST");
    if (!empty($params['nom'])
    ) {    
        require 'app/config.php';
        require 'app/opendb.php';
        $sql = "INSERT INTO modepaiements (nom)
                    VALUES ('".$params['nom']."')";
        $insert = mysql_query($sql);
        if($insert){
            $find = mysql_query("SELECT * FROM modepaiements WHERE nom=".$params['nom']);
            $obj = mysql_fetch_object($find);
            $response = $response->withStatus(201, 'Mode de paiement ajoute');
            $response = $response->write(json_encode($obj));
        }
        else{
            echo'error insertion';
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Modifier un mode de paiement ------> NON (creer table modepaiements)
$app->put('/api/payments/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $params = $request->getParsedBody();
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Headers", "Content-Type");
    $response = $response->withHeader("Access-Control-Allow-Methods", "PUT");
    if (!empty($params['nom'])
    ) {    
        require 'app/config.php';
        require 'app/opendb.php';
        $sql = "UPDATE modepaiements SET nom='".$params['nom']."'
                                WHERE id='".$id."'"; 
        $update = mysql_query($sql);
        if($update){
            $find = mysql_query("SELECT * FROM modepaiements WHERE id=".$id);
            $obj = mysql_fetch_object($find);
            $response = $response->withStatus(201, 'Mode de paiement ajoute');
            $response = $response->write(json_encode($obj));
        }
        else{
            echo'error update';
        }
        require 'app/closedb.php';
    } else {
        $response = $response->withStatus(400, 'Invalid parameters');
    }
    return $response;
});

//Supprimer un mode de paiement ------> NON (creer table modepaiements)
$app->delete('/api/payments/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    require 'app/config.php';
    require 'app/opendb.php';
    $modePayment = mysql_query('SELECT * FROM modepaiements WHERE id='.$id);
    $obj = mysql_fetch_object($modePayment);
    if ($obj) {
        $modePayment = mysql_query('DELETE FROM modepaiements WHERE id='.$id);
        $response = $response->withStatus(200, 'Mode de paiement supprime');
    } else {
        $response = $response->withStatus(404, 'Mode de paiement inexistant');
    }
    require 'app/closedb.php';
    return $response;
});

/* ------------------------------PRODUITS------------------------------ */
//Recuperer tous les produits ------>  OK
$app->get('/api/products', function ($request, $response) {
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $produits=Produits::all();
    if(count($produits) !== 0) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($produits));
    } else {
        $response = $response->withStatus(404, 'Aucun produit enregistre');
    }
    return $response;
});

//Recuperer le produit ref ------>  OK
$app->get('/api/products/{reference}', function ($request, $response, $args) {
    $reference = $args['reference'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $produit=Produits::where('reference', $reference)->first();
    if (count($produit)>0) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($produit));
    } else {
        $response = $response->withStatus(404, 'Reference produit inexistante');
    }
    return $response;
});

//Supprimer le produit ref ------>  OK
$app->delete('/api/products/{reference}', function ($request, $response, $args) {
    $reference = $args['reference'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    $produit = Produits::where('reference', $reference)->first();
    if (count($produit)>0) {
        Produits::where('reference', $reference)->delete();
        $response = $response->withStatus(200, 'Product deleted');
    } else {
        $response = $response->withStatus(404, 'Reference produit inexistante');
    }
    return $response;
});

/* ------------------------------STAFFS------------------------------ */
//Recuperer les membres du staff ------>  A TESTER
$app->get('/api/staffs', function ($request, $response) {
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "GET");
    $staff=Staff::all();
    if(count($staff)>0) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($staff));
    } else {
        $response = $response->withStatus(404, 'Aucun staff enregistre');
    }
    return $response;
});

//Ajouter une menbre du staff ------>  A COMPLETER 
$app->post('/api/staffs', function ($request, $response) {

});

// Supprimer un membre du staff ------>  A TESTER
$app->delete('/api/staffs/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $response = $response->withHeader("Access-Control-Allow-Origin", "*");
    $response = $response->withHeader("Access-Control-Allow-Methods", "DELETE");
    $staff=Staff::find($id);
    if (!empty($staff)) {
        $staff->delete();
        $response = $response->withStatus(200, 'Staff deleted');
    } else {
        $response = $response->withStatus(404, 'Staff inexistant');
    }
    return $response;
});
