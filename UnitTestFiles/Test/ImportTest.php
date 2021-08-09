<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    public function testTrueAssetsToTrue()
    {
        $condition = true;
        $this->assertTrue($condition);
    }
}
?>
