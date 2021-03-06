/*
*   Controller DepotController : 
*       - appelé sur les routes "/depot/new", "/depot/gestion" et "/solderDepot"
*       - injection des ressources "Depot" et "DepotProducts"
*/
angular.module("DepotVente").controller('DepotController', ['$scope', '$location', 'Depot', 'DepotProducts',
    function ($scope, $location, Depot, DepotProducts) {
        
        /*
        * Initialisation de variables utilisées dans le contrôleur
        */
        $scope.editCoord = false;
        $scope.products = [];
        $scope.totalRembourser=0;
        $scope.temp = "";
        $scope.nomR = null;
        $scope.emailR = null;
        $scope.nbrProduits=0;
        $scope.nbrDepots=0;
        $scope.produit=[];
        $scope.depotId=[];
        $scope.solde=true;
        $scope.message=false;

        /*
        *  Récupère tous les dépôts pour les solder
        */
        Depot.query(function(data) {
                        $scope.recherche = data;
                        for(i in data){
                            DepotProducts.query({idDepot: data[i].id},
                                                function(data){
                                                    for(i in data){
                                                        if(data[i].etat === "Vendu" 
                                                            || data[i].etat === "Perdu")
                                                        {
                                                            if ($scope.depotId.indexOf(data[i].id_depot) == -1) {
                                                                $scope.depotId.push(data[i].id_depot);
                                                                $scope.nbrDepots=$scope.depotId.length;
                                                            }
                                                            $scope.produit.push(data[i]);
                                                            $scope.nbrProduits+=1;
                                                            $scope.totalRembourser+=data[i].prix;
                                                            $scope.solde=false;
                                                            $scope.soldeD=true;
                                                        } 
                                                    }
                                                    if($scope.depotId.length === 0 
                                                        && $scope.produit.length === 0){
                                                        $scope.message=true;
                                                        $scope.solde=false;
                                                    } 
                                                }); 
                        }
                });

        /*
        *   Solde le dépôt
        *   @param id : id du dépôt à solder
        */
        $scope.solder = function(id){
            DepotProducts.query({idDepot: id}, function(data) {
                for(i in data){
                    if(data[i].etat === "Vendu" || data[i].etat === "Perdu"){
                        new DepotProducts({prix: data[i].prix,
                            description: data[i].description,
                            etat: "Payé"})
                            .$update({idDepot: data[i].id_depot, ref: data[i].reference},
                            function(data){
                                $scope.totalRembourser-=data.prix;
                                for(i in $scope.produit){
                                    if ($scope.produit[i].reference === data.reference){
                                        $scope.produit[i].etat = "Payé";
                                        $scope.nbrProduits-=1;
                                    }
                                }
                            });
                    }
                }
                $scope.nbrDepots-=1;
            });
        }

        /*
        *  Solde tous les dépôts
        */
        $scope.solderAll = function(){
            for (i in $scope.produit){
                DepotProducts.query({idDepot: $scope.produit[i].id_depot},
                    function(data) {
                        $scope.soldeD=false;
                        for(i in data){
                            if(data[i].etat === "Vendu" || data[i].etat === "Perdu"){
                                new DepotProducts({prix: data[i].prix,
                                    description: data[i].description,
                                    etat: "Payé"})
                                    .$update({idDepot: data[i].id_depot, ref: data[i].reference},
                                    function(data){
                                        $scope.totalRembourser-=data.prix;
                                        for(i in $scope.produit){
                                            if ($scope.produit[i].reference === data.reference){
                                                $scope.produit[i].etat = "Payé";
                                            }
                                        }
                                    });
                            }
                        }    
                });
            }
        }

        /*
        * Recherche un dépôt avec un email ou un nom
        */
        $scope.Search = function () {
            if($scope.emailR != null && $scope.emailR != ""){
                Depot.query(function(data) {
                                for (i in data){
                                    if(data[i].email === $scope.emailR){
                                        $scope.id=data[i].id;
                                        $scope.findDepot();
                                    }
                                }
                            },
                            function(err) {
                                $scope.error = err;
                            });
            }
            else if($scope.nomR != null && $scope.nomR != ""){
                Depot.query(function(data) {
                                for (i in data){
                                    var nomPrenom = data[i].nom + " " + data[i].prenom;
                                    if(nomPrenom === $scope.nomR){
                                        $scope.id=data[i].id;
                                        $scope.findDepot();
                                    }
                                }
                            },
                            function(err) {
                                $scope.error = err;
                            });
            }
        }

        /*
        * Crée un dépôt
        */
        $scope.createDepot = function () {
            $scope.depot = new Depot({nom: $scope.depot.nom, 
                            prenom: $scope.depot.prenom, 
                            email: $scope.depot.email, 
                            adresse: $scope.depot.adresse, 
                            telephone: $scope.depot.telephone});
            $scope.depot.$save(function(data) {
                                    $scope.isplay = true;
                                },
                                function(err) {
                                    $scope.error = err;
                                });
        };

        /*
        * Met à jour un dépôt
        */
        $scope.updateCoord = function () {
            new Depot({nom: $scope.depot.nom, 
                        prenom: $scope.depot.prenom, 
                        email: $scope.depot.email, 
                        adresse: $scope.depot.adresse, 
                        telephone: $scope.depot.telephone})
                .$update({id: $scope.depot.id},
                function(data){
                    $scope.editCoord = false;
                },
                function(err) {
                    $scope.error = err;
                });
        }

        /*
        *  Ajoute un objet au dépôt
        */
        $scope.addObject = function(){
            var product = new DepotProducts({reference: $scope.objet.reference,
                                    prix: $scope.objet.prix,
                                    description: $scope.objet.description, 
                                    idDepot: $scope.depot.id});
            product.$save(function(data) {
                    if (data.description.length > 20){
                        data.description = data.description.substr(0,20) + '..';
                    }
                    $scope.products.push(data);
                    $scope.objet=""; 
                }, 
                function(err){
                    $scope.error = err;
                });
        }

        /*
        *  Supprime un objet du dépôt
        *  @param obj : objet à supprimer
        */
        $scope.deleteObject = function(obj){
            DepotProducts.delete({ref: obj.reference, idDepot: obj.id_depot});
            for(i in $scope.products){
                if($scope.products[i] === obj){
                    $scope.products.splice(i, 1);
                    break;
                }
            }
        } 

        /*
        *  Affiche le formulaire de mise à jour d'objet
        *  @param objet : objet sur lequel il faut afficher le formulaire
        */
        $scope.editObject = function(objet){
            objet.isediting=true;
            $scope.temp = objet.etat;
        }

        /*
        *  Met à jour un objet
        *  @param objet : objet à mettre à jour
        */
        $scope.updObject = function(objet){
            new DepotProducts({prix: objet.prix,
                            description: objet.description,
                            etat: objet.etat})
            .$update({idDepot: objet.id_depot, ref: objet.reference},
            function(data){
                objet.isediting=false;
                if($scope.temp !== "Perdu" && data.etat === "Perdu"){
                    $scope.totalRembourser+=data.prix;
                }
                if(data.etat === "En stock" && $scope.temp === "Perdu"){
                    $scope.totalRembourser-=data.prix;
                }
                if (objet.description.length > 20){
                    objet.description = objet.description.substr(0,20) + '..';
                }
            },
            function(err) {
                $scope.error = err;
            });
        }

        /*
        *  Recherche un dépôt avec un id
        */
        $scope.findDepot = function(){
            $scope.totalRembourser=0;
            $scope.depot = Depot.get({id: $scope.id}, function() {
                                    $scope.isplay = true;
                                },
                                function(err) {
                                    $scope.error = err;
                                });
            $scope.products = DepotProducts.query({idDepot: $scope.id}, function(data) {
                                            for(i in $scope.products){
                                                if($scope.products[i].etat === "Vendu" 
                                                    || $scope.products[i].etat === "Perdu"){
                                                    $scope.totalRembourser+=$scope.products[i].prix;
                                                }
                                                if ($scope.products[i].description != undefined 
                                                    && $scope.products[i].description.length > 20){
                                                    $scope.products[i].description = $scope.products[i].description.substr(0,20) +'..';
                                                }
                                            }
                                        });
        }

        /*
        *  Supprime le dépôt
        */
        $scope.deleteDepot = function(){
            if(confirm("Voulez-vous supprimer le dépôt ainsi que ces poduits ?"))
            {
                Depot.delete({id: $scope.depot.id},
                            function() {
                                $scope.isplay = false;
                                $scope.depot = "";
                                $location.path("/depot/new");
                            },
                            function(err) {
                                $scope.error = err;
                            });
            }
        }

        /*
        *  Change l'état de tous les objets vendus du dépôt à "payé"
        */
        $scope.payer = function(){
            DepotProducts.query({idDepot: $scope.depot.id}, function(data) {
                for(i in data){
                    if(data[i].etat === "Vendu" || data[i].etat === "Perdu"){
                        new DepotProducts({prix: data[i].prix,
                            description: data[i].description,
                            etat: "Payé"})
                            .$update({idDepot: data[i].id_depot, ref: data[i].reference},
                            function(data){
                                $scope.totalRembourser-=data.prix;
                                for(i in $scope.products){
                                    if ($scope.products[i].reference === data.reference){
                                        $scope.products[i].etat = "Payé";
                                    }
                                }
                            });
                    }
                }
            });
        }

        /*
        *  Calcul la date du jour
        */
        var calculDate = function(){
            var m = [
                "Janvier", "Février", "Mars",
                "Avril", "Mai", "Juin", "Juillet",
                "Août", "Septembre", "Octobre",
                "Novembre", "Décembre"];
            var d = new Date(Date.now());
            var jour = d.getDate();
            var mois = d.getMonth();
            var annee = d.getFullYear();
            var date = (jour + ' ' + m[mois] + ' ' + annee);
            return date;
        }

        /*
        *  Edite le récépissé du dépôt
        */
        $scope.recepisseDepot = function(){
            var date = calculDate();
            var facture = new jsPDF();
            facture.setFont('Helvetica-Bold');
            facture.text(20, 20, "Identifiant déposant : " + $scope.depot.id);
            facture.text(20, 28, "Date : " + date);
            facture.text(120, 40, $scope.depot.nom + " " + $scope.depot.prenom);
            facture.text(120, 48, $scope.depot.adresse);
            facture.text(120, 56, $scope.depot.telephone);
            facture.setFontSize(22);
            facture.setFontStyle("bold");
            facture.text(20, 70, "Récapitulatif : ");
            facture.setFontSize(16);
            facture.setFontStyle("normal");
            facture.text(20, 85, "Référence");
            facture.text(60, 85, "Description");
            facture.text(170, 85, "Prix");
            for(i in $scope.products){
                hauteur = 95+5*i;
                if($scope.products[i].reference != undefined){
                    facture.text(20, hauteur, $scope.products[i].reference.toString());
                    facture.text(60, hauteur, $scope.products[i].description);
                    facture.text(170, hauteur, $scope.products[i].prix + ' €');
                }
            }
            facture.save('recepisse.pdf');
        }

        /* 
        *  Vide les champs du formulaire de création de 
        *  dépôt lors du clic sur le menu "Créer dépôt".
        */
        $scope.clearCreation = function(){
            $scope.depot.nom = "";
            $scope.depot.prenom = "";
            $scope.depot.adresse = "";
            $scope.depot.email = "";
            $scope.depot.telephone = "";
            $scope.products=[];
        }

        /*
        *  Vide les champ du formulaire de recherche de
        *  dépôt lors du clc sur le menu "Gérer dépôt".
        */
        $scope.clearEdition = function(){
            $scope.id = "";
            $scope.nomR = "";
            $scope.emailR = "";
        }
}]);
