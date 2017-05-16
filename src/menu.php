<div id='menu'>

    <?php
    if(!isset($relpath)){
        $relpath = "";
    }
    $seasonname = $_SESSION["kausi"]["nimi"];
    $nextseason = $url . "&kausi=next&";
    $prevseason = $url . "&kausi=previous&";
    ?>
    <ul>
        <li class='' title='Vaihda edelliseen tai seuraavaan kauteen'> 
            <a href="javascript:void(0);" onClick='SwitchSeason("edellinen");'>
                <i class="fa fa-backward" title="Vaihda edelliseen kauteen" aria-hidden="true"></i>
            </a> 

            <em><?php echo $seasonname; ?></em> 

            <a href="javascript:void(0);" title="Vaihda seuraavaan kauteen" onClick='SwitchSeason("seuraava");'>
                <i class="fa fa-forward" aria-hidden="true"></i>
            </a> 
        </li>
        <li class='menuli'><a href='<?php echo $relpath; ?>index.php'>Yleisnäkymä</a></li>
        <li class='menuli'><a id='songphplink' href='<?php echo $relpath; ?>songs.php'>Syötä lauluja</a></li>
        <li class='menuli'><a href='<?php echo $relpath; ?>soittajapankki/index.php'>Soittajapankki</a></li>
        <li class='menuli'><a href='<?php echo $relpath; ?>puhujapankki/index.php'>Puhujapankki</a></li>
        <li class='menuli' OnClick='ViewMaintenance(this);'>Ylläpito</li>
        <li class='menuli'><a href='<?php echo $relpath; ?>index.php?logout=Yes'>Kirjaudu ulos</a></li>
    </ul>

        <ul id='maintenancelist'>
            <li class='menuli'><a href='<?php echo $relpath; ?>insert_messudata.php'>Syötä uusia messuja</a></li>
            <li class='menuli'><a href='<?php echo $relpath; ?>uusivastuu.php'>Syötä uusia vastuutyyppejä</a></li>
        </ul>
</div>
