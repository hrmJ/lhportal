<section id='leftbanner'>


    <form action="<?php echo $url;?>" method="GET">
        <span class='menuleft'>
            <ul>
                <li><i class="fa fa-bars" id='settings' aria-hidden="true"></i></li>
                <li id='homeli' title='Takaisin alkunäkymään'>Majakkaportaali</li>
            </ul>
        </span>
        <span class='menuright'>
            <ul>
                <li> <?php echo $vastuulist;  ?> </li>
            </ul>
        </span>
        <input name='kausi' id='kausi_input' class='hidden'>
        <input type='submit' name='seasonsubmit' class='hidden' id='seasonsubmit'>
    </form>

</section>

