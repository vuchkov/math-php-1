<?php
namespace MathPHP\LinearAlgebra\Decomposition;

use MathPHP\Exception;
use MathPHP\LinearAlgebra\Householder;
use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;

/**
 * QR Decomposition using Householder reflections
 *
 * A = QR
 *
 * Q is an orthogonal matrix
 * R is an upper triangular matrix
 *
 * @property-read Matrix $Q orthogonal matrix
 * @property-read Matrix $R upper triangular matrix
 */
class QR implements \ArrayAccess
{
    /** @var Matrix orthogonal matrix  */
    private $Q;

    /** @var Matrix upper triangular matrix */
    private $R;

    /**
     * QR constructor
     *
     * @param Matrix $Q Orthogonal matrix
     * @param Matrix $R Upper triangular matrix
     */
    private function __construct(Matrix $Q, Matrix $R)
    {
        $this->Q = $Q;
        $this->R = $R;
    }

    /**
     * Decompose a matrix into a QR Decomposition using Householder reflections
     * Factory method to create QR objects.
     *
     * A = QR
     *
     * Q is an orthogonal matrix
     * R is an upper triangular matrix
     *
     * Algorithm notes:
     *  If the source matrix is square or wider than it is tall, the final
     *  householder matrix will be the identity matrix with a -1 in the bottom
     *  corner. The effect of this final transformation would only change signs
     *  on existing matrices. Both R and Q will already be in appropriate forms
     *  in the next to the last step. We can skip the last transformation without
     *  affecting the validity of the results. Results indicate other software
     *  behaves similarly.
     *
     *  This is because on a 1x1 matrix uuᵀ = uᵀu, so I - [[2]] = [[-1]]
     *
     * @param Matrix $A source Matrix
     *
     * @return QR
     *
     * @throws Exception\BadDataException
     * @throws Exception\IncorrectTypeException
     * @throws Exception\MathException
     * @throws Exception\MatrixException
     * @throws Exception\OutOfBoundsException
     * @throws Exception\VectorException
     */
    public static function decompose(Matrix $A): QR
    {
        $n  = $A->getN();  // columns
        $m  = $A->getM();  // rows
        $HA = $A;

        $numReflections = min($m - 1, $n);
        $FullI          = MatrixFactory::identity($m);
        $Q              = $FullI;

        for ($i = 0; $i < $numReflections; $i++) {
            // Remove the leftmost $i columns and upper $i rows
            $A = $HA->submatrix($i, $i, $m - 1, $n - 1);
            
            // Create the householder matrix
            $innerH = Householder::transform($A);
            
            // Embed the smaller matrix within a full rank Identity matrix
            $H  = $FullI->insert($innerH, $i, $i);
            $Q  = $Q->multiply($H);
            $HA = $H->multiply($HA);
        }

        $R = $HA;
        return new QR(
            $Q->submatrix(0, 0, $m - 1, min($m, $n) - 1),
            $R->submatrix(0, 0, min($m, $n) - 1, $n - 1)
        );
    }

    /**
     * Get Q or R matrix
     *
     * @param string $name
     *
     * @return Matrix
     *
     * @throws Exception\MatrixException
     */
    public function __get(string $name): Matrix
    {
        switch ($name) {
            case 'Q':
            case 'R':
                return $this->$name;

            default:
                throw new Exception\MatrixException("QR class does not have a gettable property: $name");
        }
    }

    /**************************************************************************
     * ArrayAccess INTERFACE
     **************************************************************************/

    /**
     * @param mixed $i
     * @return bool
     */
    public function offsetExists($i): bool
    {
        switch ($i) {
            case 'Q':
            case 'R':
                return true;

            default:
                return false;
        }
    }

    /**
     * @param mixed $i
     * @return mixed
     */
    public function offsetGet($i)
    {
        return $this->$i;
    }

    /**
     * @param  mixed $i
     * @param  mixed $value
     * @throws Exception\MatrixException
     */
    public function offsetSet($i, $value)
    {
        throw new Exception\MatrixException('QR class does not allow setting values');
    }

    /**
     * @param  mixed $i
     * @throws Exception\MatrixException
     */
    public function offsetUnset($i)
    {
        throw new Exception\MatrixException('QR class does not allow unsetting values');
    }
}
