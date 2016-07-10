<section class='hidden help' id='alkuhelp'>
    <p>
        Tervetuloa rakentamaan Majakkamessuja! 

        <ul>
            <li> Siirry tutkimaan yksittäistä messua klikkaamalla jotakin päivämäärää.</li>
            <li> Tarkastele tiimin/vastuun mukaan:   </li>
        </ul>
    </p>
</section>

<section class='hidden help' id='vastuuhelp'>
    <p> 
        Tässä näkymässä voit tarkastella yksittäisen vastuun toteuttamista koko 
        messukaudella.
        <ul>
            <li> Klikkaa vain päivämäärää muuttaaksesi tietoja / lisätäksesi puuttuvan tiedon.
            <li> Muista painaa enter tai klikata Tallenna-nappia listan lopussa (vai alussa?).
        </ul>
    </p>
</section>

<section id='leftbanner'>


    <?php
    $seasonname = $_SESSION["kausi"]["nimi"];
    $nextseason = $url . "&kausi=next&";
    $prevseason = $url . "&kausi=previous&";

    ?>

    <form action="<?php echo $url;?>" method="GET">
        <span class='menuleft'>
            <ul>
                <li>Majakkaportaali</li>
                <li> &#xf059; </li>
            </ul>
        </span>
        <span class='menuright'>
            <ul>
                <li> <?php echo $vastuulist;  ?> </li>
                <li> 
                    <a href="javascript:void(0);" onClick='SwitchSeason("edellinen");'>
                         &#xf053;
                    </a> 

                    <em><?php echo $seasonname; ?></em> 

                    <a href="javascript:void(0);" onClick='SwitchSeason("seuraava");'>
                         &#xf054;
                    </a> 
                </li>
            </ul>
        </span>
        <input name='kausi' id='kausi_input' class='hidden'>
        <input type='submit' name='seasonsubmit' class='hidden' id='seasonsubmit'>
    </form>

    </p>

</section>

