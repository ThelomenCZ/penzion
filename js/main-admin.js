//budeme chtit pro kliknuti na smazat nejprve ziskat od uzivatele svoleni
//zamerit vsechny odkazy smazat
let poleOdkazuSmazat = document.querySelectorAll(".tukan");
//musime kazdy odkaz deaktivovat
for (let odkaz of poleOdkazuSmazat) {
    odkaz.addEventListener("click", (e) => {
        //vypnuli jsme presmerovani smazat
        e.preventDefault();
        //confirm je dialogove okno ktere vypada jako alert, ale uzivatel mue zvolit ano/ne
        let souhlas = confirm("Opravdu chcete smazat stranku?");//confirm vraci boolean

        if(souhlas == true) {
            //musime zjistit kam puvodni odkaz smeroval
            let cilOdkazu = odkaz.getAttribute("href");

            //presmerujeme uzivatele
            window.location.href = cilOdkazu;
        }

    })
}



//pretransformovat nas ul seznam stranek na sortable
$("#sova").sortable({
    update: () => {
        //toto vrati pole id <li> v aktualnim serazeni
        let poleSerazenychStranek = $("#sova").sortable("toArray");
        console.log(poleSerazenychStranek);
        //nyni provedeme ajax
        $.ajax({
            type: "POST",
            url: "admin.php",
            data: {
                poradiSubmit: true,
                poleId: poleSerazenychStranek
            },
            dataType: "Json",
            success: function (response) {
                //zde nemusime delat nic,chceme jen poslat data na server
            }
        });
    }
});