<?php
AddHeader();
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
    </form>
</article>

</body>
</html>
