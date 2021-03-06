<!DOCTYPE html>
<link rel="stylesheet" type="text/css" href="style.css">
<html>

	<head>

		<title>Mini-RPG</title>
	</head>

	<body>

    <?php   

        include("objet\heros.php"); //on prend les fichiers intéressant, à savoir le héros et l'ennemi
        include("objet\_ennemi.php");

        session_start();

        $_SESSION['id_pseudo'] = (int)$_GET['link']; //on récupère le héros choisi, son id ici

        function getHeros(){

            if ($_SESSION['heros'] == null) {

                try{
                    $bdd = new PDO('mysql:host=localhost;dbname=rpg', 'root', ''); // connexion à la bdd
                }
            
                catch(Exception $e)
                {
                        die('Erreur : '.$e->getMessage());
                }
            
                // lancement de la requête
            
                $reponse = $bdd->query('SELECT *
                                        FROM heros
                                        WHERE id_heros = '.$_SESSION["id_pseudo"].'');
            

                $heros = new heros($reponse->fetch()); //création d'une instance du héros

            }

            else{
                $heros = $_SESSION['heros'];
            }

            return $heros;
        }

       

        function ennemispawn() #creation du ennemi
        {

            if ($_SESSION['ennemi']==null or $_SESSION['ennemi']->getHP() <= 0){
                try{
                    $bdd = new PDO('mysql:host=localhost;dbname=rpg', 'root', ''); // connexion à la bdd
                }
            
                catch(Exception $e)
                {
                        die('Erreur : '.$e->getMessage());
                }

                $reponse = $bdd->query('SELECT *
                                        FROM ennemi
                                        WHERE id_ennemi = '.rand(1,3).'');//on choisi un ennemi entre 1-3 (le nombre d'ennemi présent dans la bdd)

                $ennemi = new ennemi($reponse->fetch()); //création de l'instance

                return $ennemi;//c'est une fonction, on retourne l'instance
 

            }
            else {
                return $_SESSION['ennemi']; //si il y a déjà un ennemi en cours, on le garde
            }

            
        
        }

        $heros = getHeros();
        $ennemi = ennemispawn();
        echo "</br>".$heros->getName()." a ".$heros->getHP()."PV , et ".$heros->getAttPts()." points d'attaque."; //on donne les caractéristiques du héros (a supp)
        echo '</br>'.$ennemi->getName(); //on donne le nom de l'ennemi

    ?>



<?php //que le combat commence ! 

// détection de bouton

        combat_ennemi($heros, $ennemi);


        if(array_key_exists('FIGHT', $_POST)) { //exemple pour celui là si le bouton FIGHT est cliqué
            sauvergarde($ennemi, $heros);
            combat($heros, $ennemi); // on lance le combat (l'ordre est le bon, sinon, ça plante car l'action de combat reset la page, et l'ennemi n'est pas sauvegardé)
        }

        else if(array_key_exists('ACT', $_POST)) {
            sauvergarde($ennemi, $heros);
            action();
        }

        else if(array_key_exists('ITEM', $_POST)) {
            sauvergarde($ennemi, $heros);
            item();
        }
        else if(array_key_exists('MERCY', $_POST)) {
            sauvergarde($ennemi, $heros);
            mercy();
        }
        else if(array_key_exists('check', $_POST)) {
            sauvergarde($ennemi, $heros);
            check($ennemi);
        }
        else if(array_key_exists('talk', $_POST)) {
            sauvergarde($ennemi, $heros);
            talk();
        }
        else if(array_key_exists('pun', $_POST)) {
            sauvergarde($ennemi, $heros);
            pun();
        }
        else if(array_key_exists('menu', $_POST)) {
            retour($heros);
        }

        function sauvergarde($ennemi, $heros){
            $_SESSION['ennemi'] = $ennemi; //on enregistre l'ennemi pour le prochain tour
            $_SESSION['heros'] = $heros;
        }


        function combat($heros, $ennemi) #lance un combat scripté
        {
            $heros->attaquer($ennemi);//fonction de combat, voir heros.php pour le code
            menu();//remet le menu (il y a plus efficace, je l'accorde...)
        }
        function action()//met le menu des actions (inutile, mais potentiel de developper une route "pacifiste" à savoir, finir un combat sans tuer)
        {
            echo ('<form method="post">

            <input type="submit" name="check"
                    class="bouton" value="check" />
              
            <input type="submit" name="talk"
                    class="bouton" value="talk" />
              
            <input type="submit" name="pun"
                    class="bouton" value="pun" />
        </form>');
        }
        function check($ennemi) //permet de savoir les stats basiques de l'ennemi présent
        {
            echo "</br>".$ennemi->getName()." a ".$ennemi->getHP()."PV .";
            echo  "</br>".$ennemi->getName()." va dropper ".$ennemi->getGoldDrop()." gold 彼が死ぬとき .";
            echo "</br>".$ennemi->getName()." va dropper ".$ennemi->getXpDrop()." XP 彼が死ぬとき.";
            menu();
        }
        function talk() //permet de parler (nouveau menu selon le monstre ? a approfondir, inutile mais marrant)
        {
            echo "</br>you talk to the monster, nothing changes";
            menu();
        }
        function pun() //permet de dire une blague (inutile... potentiel ?->enrager le monstre)
        {
            echo "</br>you tell a pun, it's horrible, you should stop";
            menu();
        }
        function item() //fais pop le menu des items, possibilités de les utiliser (0 item pour le moment)
        {
            echo "</br>panneau des item qui pop";
            menu();
        }
        function mercy() //fais pop le menu MERCY (pitié), à savoir, fuir, ou aider, ou autre actions pacifistes 
        {
            echo "</br>panneau MERCY qui pop";
            menu();
        }

        function menu()//fais pop le menu de choix basiques
        {
        echo ('<form method="post">

            <input type="submit" name="FIGHT"
                    class="bouton" value="FIGHT" />
              
            <input type="submit" name="ACT"
                    class="bouton" value="ACT" />
              
            <input type="submit" name="ITEM"
                    class="bouton" value="ITEM" />
    
            <input type="submit" name="MERCY"
                    class="bouton" value="MERCY" />

            <input type="submit" name="menu"
                    class="bouton" value="Retour au menu" />
        </form>');
    }

    function combat_ennemi($heros, $ennemi){
        if ($ennemi->getHP() > 0){
            $ennemi->attaquer($heros);
        }
    }

    if ($_SESSION['first_run'] == 0) //détermine si c'est la première visite 
    {
        menu(); //si oui, le menu pop (sinon, le joueur est bloqué)
        $_SESSION['first_run'] = 1; //on enlève la première visite
    }

    function retour($heros){

        try{
            $bdd = new PDO('mysql:host=localhost;dbname=rpg', 'root', ''); // connexion à la bdd
        }
    
        catch(Exception $e)
        {
                die('Erreur : '.$e->getMessage());
        }


        $sql = "UPDATE heros
                SET hp = ".$heros->getHP().", hp_max = ".$heros->getHPmax().", mana = ".$heros->getmana().", att_base = ".$heros->getAttBase().", def_base = ".$heros->getDefBase().", xp = ".$heros->getXP().", lvl = ".$heros->getLVL().", att_pts = ".$heros->getAttPts().", def_pts = ".$heros->getDefPts()."
                WHERE id_pseudo = ".$_SESSION['id_pseudo']."";


        //préparation puis execution (execution seule marche pas, obligation de préparer)
        $stmt = $bdd -> prepare($sql);
        $stmt->execute();

        header('Location: menu_main.php');


    }

    ?>

    </body>
       
</html>