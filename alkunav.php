<section id='leftbanner'>


    <?php
    if (isset($_GET["vastuu"])){
        echo "<h3>Rajaus: " . $_GET["vastuu"] . "</h3>";
        echo "<p> Tässä näkymässä voit tarkastella yksittäisen vastuun 
            toteuttamista koko messukaudella.
            <ul>
                <li> Klikkaa vain päivämäärää muuttaaksesi tietoja / lisätäksesi puuttuvan tiedon.
                <li> Muista painaa enter tai klikata Tallenna-nappia listan lopussa (vai alussa?).
            </ul>
            </p>";
    }
    else{
    ?>

    <h3>Majakkaportaali 0.1</h3>

    <p>
    Tervetuloa rakentamaan Majakkamessuja! 

    <ul>
        <li> Siirry tutkimaan yksittäistä messua klikkaamalla jotakin päivämäärää.</li>
        <li> Siirry suoraan seuraavaan messuun (PVM) painamalla <a href="#">tästä</a>
        <li> Tarkastele tiimin/vastuun mukaan:  <?php echo $vastuulist; } ?> </li>
        <li> Tässä näkyy [KAUSI X]. Voit tutkia myös <a href="#">edellisen</a> tai <a href="#">seuraavan</a> kauden messuja. </li>
    </ul>

    </p>

</section>
