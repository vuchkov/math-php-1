<?php
namespace MathPHP\Tests\Statistics\Multivariate;

use MathPHP\Functions\Map\Multi;
use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;
use MathPHP\Statistics\Multivariate\PCA;
use MathPHP\Exception;
use MathPHP\Tests\Data\SampleData;

class PCATest extends \PHPUnit\Framework\TestCase
{
    /** @var array[] */
    private static $A;

    /** @var PCA */
    private static $pca;

    /** @var Matrix  */
    private static $matrix;

    /**
     * R code for expected values:
     *   library(mdatools)
     *   data = mtcars[,c(1:7,10,11)]
     *   model = pca(data, center=TRUE, scale=TRUE)
     *
     * @throws Exception\MathException
     */
    public static function setUpBeforeClass()
    {
        self::$A = SampleData::mtcars();

        // Remove top row, left column, and categorical variables
        self::$matrix = MatrixFactory::create(self::$A)->rowExclude(0)->columnExclude(9)->columnExclude(8)->columnExclude(0);
        self::$pca = new PCA(self::$matrix, true, true);
    }

    /**
     * @test         Construction
     * @dataProvider dataProviderForConstructorParameters
     * @param        bool $center
     * @param        bool $scale
     * @throws       Exception\MathException
     */
    public function testConstruction(bool $center, bool $scale)
    {
        // When
        $pca = new PCA(self::$matrix, $center, $scale);

        // Then
        $this->assertInstanceOf(PCA::class, $pca);
    }

    /**
     * @return array (center, scale)
     */
    public function dataProviderForConstructorParameters(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * @test   Test that the constructor throws an exception if the source matrix is too small
     * @throws \Exception
     */
    public function testConstructorException()
    {
        // Given
        $matrix = MatrixFactory::create([[1,2]]);

        // Then
        $this->expectException(Exception\BadDataException::class);

        // When
        $pca = new PCA($matrix, true, true);
    }

    /**
     * @test   Test that the new data must have the have the same number of columns
     * @throws \Exception
     */
    public function testNewDataException()
    {
        // Given
        $new_data = MatrixFactory::create([[1,2]]);

        // Then
        $this->expectException(Exception\BadDataException::class);

        // When
        self::$pca->getScores($new_data);
    }
}
