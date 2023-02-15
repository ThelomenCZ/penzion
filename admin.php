<?php
//budeme pouzviat sessiony pro prihlaseni
session_start();

require_once "./data.php";

//zrpacujeme prihlasovaci formular
if (array_key_exists("login-submit", $_POST)) {
    $zadanyUsername = $_POST["username"];
    $zadaneHeslo = $_POST["heslo"];

    //pustime prikaz ktery vyhleda radek podle username
    $prikaz = $instanceDb->prepare("SELECT * FROM spravce WHERE username=?");
    $prikaz->execute([$zadanyUsername]);
    $spravce = $prikaz->fetch();

    //pokud hledany user name v databazi
    if ($spravce) {
        //zkontorlujeme spravnost udaju
        if ($zadanyUsername == $spravce["username"] && $zadaneHeslo == $spravce["heslo"]) {
            //pokud zadal vse spravne tka vytvorime do jeho sessiny klic "prihlasen"
            $_SESSION["prihlasen"] = true; 
        }
    }


    
}

//zoracujem logout
if (array_key_exists("logout-submit", $_GET)) {
    unset($_SESSION["prihlasen"]);
    //vycistit url
    header("Location: ?");
}

//toto jle block kodu ktery s eporvode jen kdyz je uzivtael prohlaseny
if (array_key_exists("prihlasen", $_SESSION)) {

    //uzivatel chce editovat stranku
    if (array_key_exists("edit", $_GET)) {
        //vytahneme si z URL id stranky
        $idStranky = $_GET["edit"];
        //podle id najdeme v posli $seznamStranek nasi isnatnci
        $aktualniInstance = $seznamStranek[$idStranky];
    }

    //uzivatel chce zacit editovat novou stranku
    if (array_key_exists("pridat", $_GET)) {
        $aktualniInstance = new Stranka("", "", "", "");
    }

    //uzivatel chce aktualizovat
    if (array_key_exists("aktualizovat-submit", $_POST)) {
        //vathneme si data z formulare
        $idStranky = trim($_POST["id-stranky"]); //odstranime z id prebytecne mezery
        $titulekStranky = $_POST["titulek-stranky"];
        $menuStranky = $_POST["menu-stranky"];
        $obrazekStranky = $_POST["obrazek-stranky"];

        //musime zkontorlovat jestli id neni prazdne
        if ($idStranky != "") {
            //nastavime instanci nova data
            $aktualniInstance->setId($idStranky);
            $aktualniInstance->setTitulek($titulekStranky);
            $aktualniInstance->setMenu($menuStranky);
            $aktualniInstance->setObrazek($obrazekStranky);
            //propisme instanci do DB
            $aktualniInstance->ulozDoDb();

            $novyObsahStranky = $_POST["obsah-stranky"];
            $aktualniInstance->setObsah($novyObsahStranky);

            //refreshneme stranku aby v url bylo nove id
            header("Location: ?edit=$idStranky");
        }
    }

    //uzivatek chce smazat stranku
    if (array_key_exists("smazat", $_GET)) {
        $idStrankyKeSmazani = $_GET["smazat"];
        $seznamStranek[$idStrankyKeSmazani]->smazSe();
        header("Location: ?");
    }

    //uzivatel chce preradit stranky v databazi
    if (array_key_exists("poradiSubmit", $_POST)) {
        $poleSerazenychId = $_POST["poleId"];
        //zavolame statickou funkci classy stranka
        Stranka::aktualizujPoradiDb($poleSerazenychId);
        //ukoncime script
        exit;
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrace</title>
</head>
<body>
    <h1>Administrace</h1>
    <?php

    if (array_key_exists("prihlasen", $_SESSION)) {
        echo "Jste prihlasen.<br>";
        echo "<a href='?logout-submit=yes'>Odhlasit se</a>";

        //zde vpyisujeme ul seznam vsech stranek, ktere lze editovat
        echo "<ul id='sova'>";
        foreach ($seznamStranek AS $instance) {
            echo "<li id='{$instance->getId()}'>
            <a href='?edit={$instance->getId()}'>{$instance->getId()}</a>
            <a class='tukan' href='?smazat={$instance->getId()}'>[SMAZAT]</a>
            </li>";
        }
        echo "</ul>";

        echo "<a href='?pridat=yes'>Pridat novou stranku</a>";

        //vykreslime wysiwyg pokud esituje promenna $aktualniInstance
        if (isset($aktualniInstance)) {
            ?>
            <form action="" method="post">
                <label for="">ID:</label>
                <input type="text" name="id-stranky" id="" value="<?php echo $aktualniInstance->getId();?>">
                <label for="">Titulek:</label>
                <input type="text" name="titulek-stranky" id="" value="<?php echo $aktualniInstance->getTitulek();?>">
                <label for="">Menu:</label>
                <input type="text" name="menu-stranky" id="" value="<?php echo $aktualniInstance->getMenu();?>">
                <label for="">Obrazek:</label>
                <input type="text" name="obrazek-stranky" id="" value="<?php echo $aktualniInstance->getObrazek();?>">
                <hr>
                <label for="hroch">Obsah stranky</label>
                <textarea name="obsah-stranky" id="hroch" cols="30" rows="10"><?php echo htmlspecialchars($aktualniInstance->getObsah()); ?></textarea>
                <input type="submit" name="aktualizovat-submit" value="Aktualizovat">
            </form>
            <?php
        }
    }else{
        ?>

        <form action="" method="post">
            <label for="aaa">Jmeno</label>
            <input type="text" name="username" id="aaa">
            <label for="bbb">Heslo</label>
            <input type="password" name="heslo" id="bbb">

            <input type="submit" name="login-submit" value="Prihlasit se">
        </form>

        <?php
    }

    
    ?>

    <!-- <script>debugger;</script> -->
    <!-- musime dodrzet poradi -->
    <!-- nejprve jquery az potom jqueryui -->
    <script src="./vendor/components/jquery/jquery.min.js"></script>
    <script src="./vendor/components/jqueryui/jquery-ui.min.js"></script>

    <!-- zde jsme pripojili knihovnu tinymce -->
    <script src="./vendor/tinymce/tinymce/tinymce.js"></script>
    <!-- nyni musim kinohvnu tinymce spustit -->
    <script>
        tinymce.init({
            selector: "#hroch",
            language: "cs",
            language_url: "<?php echo dirname($_SERVER["PHP_SELF"]); ?>/vendor",
            entity_encoding: "raw",
            verify_html: false,
            content_css: ["./css/style.css", "./css/all.min.css"],
            //body_id: "obsah", //zakomentovano, netusim co to dela
            plugins:["code", "responsivefilemanager", "image", "anchor", "autolink", "autoresize", "link", "media", "lists"],
            toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
            toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
            external_plugins: {
                'responsivefilemanager': '<?php echo dirname($_SERVER['PHP_SELF']); ?>/vendor/primakurzy/responsivefilemanager/tinymce/plugins/responsivefilemanager/plugin.min.js',
            },
            external_filemanager_path: "<?php echo dirname($_SERVER['PHP_SELF']); ?>/vendor/primakurzy/responsivefilemanager/filemanager/",
            filemanager_title: "File manager",
        });
    </script>
    <script src="./js/main-admin.js"></script>
</body>
</html>