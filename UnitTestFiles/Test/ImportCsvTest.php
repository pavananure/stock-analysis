<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

class ImportCsvTest extends TestCase
{
     /**
     * @dataProvider provider
     * @group csv
     */
    public function testCsv($a,$b,$c)
    {
        //Testing for date format
        $this->assertSame($a, date('d-m-Y',strtotime($a)));

        //Testing for alphnumeric
        $this->assertTrue(ctype_alnum($b));

        //Testing for the numeric
        $this->assertTrue(is_numeric((float)$c));
    }
    

    /**
     * @return array
     */
    public function provider()
    {
        $file = file_get_contents('data/sample_stock_price_sorted_test.csv', 'r');
        foreach (explode("\n", $file, -1) as $line) {
            $data[] = explode(',', $line);
        }
        return $data;
    }
}
?>
