<?php
$instanceDb = new PDO(
    "mysql:host=localhost;dbname=penzion;charset=utf8",
    "root",
    "",
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
);


    //vytvorili jsme si classu
    class Stranka {
        protected $id;
        protected $titulek;
        protected $menu;
        protected $obrazek;
        protected $oldId = "";

        function __construct($argId, $argTitulek, $argMenu, $argObrazek)
        {
            $this->id = $argId;
            $this->titulek = $argTitulek;
            $this->menu = $argMenu;
            $this->obrazek = $argObrazek;
        }

        //toto je staticka funkce
        static public function aktualizujPoradiDb ($argPoleId) {
            foreach ($argPoleId AS $klic => $id) {
                $prikaz = $GLOBALS["instanceDb"]->prepare("UPDATE stranka SET poradi=? WHERE id=?");
                $prikaz->execute([$klic, $id]);
            }
        }
 
        //zde jsou gettery protoze nase vlastnosti jsou protected
        public function getId() {
            return $this->id;
        }

        public function getTitulek () {
            return $this->titulek;
        }

        public function getMenu() {
            return $this->menu;
        }

        public function getObrazek() {
            return $this->obrazek;
        }

        public function getObsah() {
            //jsme uvnitr classy a porot nemame pristup k promennym mmimo ni
            //$instanceDb->prepare("SELECT * FROM stranka WHERE id=?");
            //musime to napsat skrz pole $GLOBALS, pomoci totho pole muzeme pouzivat uvnitr classy promene ktere jsou venku
            $prikaz = $GLOBALS["instanceDb"]->prepare("SELECT * FROM stranka WHERE id=?");
            $prikaz->execute([$this->id]);
            $stranka = $prikaz->fetch();
            if ($stranka) {
                return $stranka["obsah"];
            }else{
                return ""; //vratime prazdny stirng pokud stranka v DB neexistuje
            }
            
            

            /*toto je stary zpusob pres html soubory
            $obsahSouboru = file_get_contents("./{$this->id}.html");
            return $obsahSouboru;
            */
        }

        public function setId($argNoveId) {
            //musime si pamatovat stare id stranky
            $this->oldId = $this->id;
            $this->id = $argNoveId;
        }

        public function setTitulek($argNovyTitulek) {
            $this->titulek = $argNovyTitulek;
        }

        public function setMenu($argNoveMenu) {
            $this->menu = $argNoveMenu;
        }

        public function setObrazek($argNovyObrazek) {
            $this->obrazek = $argNovyObrazek;
        }

        public function ulozDoDb () {
            //pokud je oldId "", tak to znamena ze tam ta stranka jeste neni a musime udelat insert mito update
            if ($this->oldId != "") {
                $prikaz = $GLOBALS["instanceDb"]->prepare("UPDATE stranka SET id=?, titulek=?, menu=?, obrazek=? WHERE id=?");
                $prikaz->execute([$this->id, $this->titulek, $this->menu, $this->obrazek, $this->oldId]);
            }else{
                //nejprve zjistime nejvyssi hodnotu v databazi
                $prikaz = $GLOBALS["instanceDb"]->prepare("SELECT MAX(poradi) AS max_poradi FROM stranka");
                $prikaz->execute([]);
                $vysledek = $prikaz->fetch();
                $maxPoradi = $vysledek["max_poradi"];
                $maxPoradi++;

                $prikaz = $GLOBALS["instanceDb"]->prepare("INSERT stranka SET id=?, titulek=?, menu=?, obrazek=?, poradi=?");
                $prikaz->execute([$this->id, $this->titulek, $this->menu, $this->obrazek, $maxPoradi]);
            }
        }


        public function setObsah ($argNovyObsah) {
            $prikaz = $GLOBALS["instanceDb"]->prepare("UPDATE stranka SET obsah=? WHERE id=?");
            $prikaz->execute([$argNovyObsah, $this->id]);

            //uz nemeche pouzivat soubory
            //file_put_contents("./{$this->id}.html", $argNovyObsah);
        }

        public function smazSE() {
            $prikaz = $GLOBALS["instanceDb"]->prepare("DELETE FROM stranka WHERE id=?");
            $prikaz->execute([$this->id]);
        }





    }

    //nyni vytvorime seznam stranek z datbaze
    $seznamStranek = [];
    //pripojime se do databaze a vytahnee si vsechny stranky
    $prikaz = $instanceDb->prepare("SELECT * FROM stranka ORDER BY poradi ASC");
    $prikaz->execute([]);
    //fetchne vsechny vysledky
    $poleStranek = $prikaz->fetchAll();

    //udela for each a pro kazdou strnaku yvtvorime instanci
    foreach ($poleStranek AS $stranka) {
        $seznamStranek[$stranka["id"]] = new Stranka($stranka["id"], $stranka["titulek"], $stranka["menu"], $stranka["obrazek"]);
    }


    //zde vytvarime pole instanci
    /*
    $seznamStranek = [
        "domu" => new Stranka("domu", "PrimaPenzion", "Domů", "primapenzion-main.jpg"),
        "galerie" => new Stranka("galerie", "Fotogalerie", "Fotky", "primapenzion-room2.jpg"),
        "rezervace" => new Stranka("rezervace", "Rezervace", "Chci pokoj", "primapenzion-room.jpg"),
        "kontakt" => new Stranka("kontakt", "Kontakt", "Napište nám", "primapenzion-pool-min.jpg"),
        "404" => new Stranka("404", "Chyba 404", "", "primapenzion-pool-min.jpg")
    ];
    */

    /*
    $seznamStranek = [
        "domu" => [
            "id" => "domu",
            "titulek" => "PrimaPenzion",
            "menu" => "Domů",
            "obrazek" => "primapenzion-main.jpg" 
        ],
        "galerie" => [
            "id" => "galerie",
            "titulek" => "Fotogalerie",
            "menu" => "Fotky",
            "obrazek" => "primapenzion-room2.jpg"
        ],
        "rezervace" => [
            "id" => "rezervace",
            "titulek" => "Rezervace",
            "menu" => "Chci pokoj",
            "obrazek" => "primapenzion-room.jpg"
        ],
        "kontakt" => [
            "id" => "kontakt",
            "titulek" => "Kontakt",
            "menu" => "Napište nám",
            "obrazek" => "primapenzion-pool-min.jpg"
        ],
        "404" => [
            "id" => "404",
            "titulek" => "Chyba 404",
            "menu" => "",
            "obrazek" => "primapenzion-pool-min.jpg"
        ]
    ];
    */
?>