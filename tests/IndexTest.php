<?php 
use PHPUnit\Framework\TestCase;


#phpunit --bootstrap phputils/essential.php tests --testdox


/**
 * @covers ShowNewsFeed
 */

final class NewsfeedTest extends TestCase
{
    public function testSimpleTest()
    {
        $nf = ShowNewsFeed();
        $this->assertTrue(is_string($nf), "Newsfeed not returning string!");

    }

}

?>
