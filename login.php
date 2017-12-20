<?php
AddHeader((isset($relpath) ? $relpath : ""));
$msg = "Kirjaudu sisään";
?>
<body>

<section id='leftbanner'>
    <span class='menuleft'>
    <ul>
        <li>Majakkaportaali</li>
    </ul>
    </span>
</section>

<article id='maincontainer'>
    <?php 
    if (isset($loginfail))
        $msg="Väärä käyttäjänimi tai salasana, yritä uudestaan.";
    ?>
    <h2><?php echo $msg;?></h2>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <p>
            <label for="username">Käyttäjänimi</label>
            <input type="text" id="username" name="username" value="" maxlength="20" />
        </p>
        <p>
            <label for="password">Salasana</label>
            <input type="PASSWORD"" id="password" name="password" value="" maxlength="20" />
        </p>
        <p>
            <input type="submit" value="Kirjaudu" />
        </p>
    <?php
    if(isset($fromsongs)){
    ?>
        <p>
            Majakkamessun laululista on muuttanut osaksi Majakkaportaalia ja
            siirtynyt kirjautumisen taakse. Jos et tiedä kirjautumistunnuksia,
            tiedustele Juholta (juho.harme *at* gmail.com) /  045 136 1939. Tunnukset
            löytyvät myös majakan vastuunkantajan ABC-vihkosesta.
        </p>
    <?php
    }
    ?>
    </form>
</article>

</body>
</html>
