<?php
declare(strict_types = 1);

namespace DrdPlus\Lighting;

use DrdPlus\Tables\Measurements\Amount\AmountBonus;
use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Tables;
use Granam\Integer\IntegerInterface;
use Granam\Integer\PositiveInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;

/**
 * See PPH page 129 left column, @link https://pph.drdplus.jaroslavtyc.com/#nepruhledne_prostredi
 */
class Opacity extends StrictObject implements PositiveInteger
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param IntegerInterface $barrierDensity
     * @param Distance $barrierLength
     * @param Tables $tables
     * @return Opacity
     */
    public static function createFromBarrierDensity(
        IntegerInterface $barrierDensity,
        Distance $barrierLength,
        Tables $tables
    )
    {
        $amountBonusValue = $barrierDensity->getValue() + $barrierLength->getBonus()->getValue();
        if ($amountBonusValue < -20) { // workaround to avoid unexpected amount bonus value
            return static::createTransparent();
        }

        return new self((new AmountBonus($amountBonusValue, $tables->getAmountTable()))->getAmount()->getValue());
    }

    /**
     * @return Opacity
     */
    public static function createTransparent()
    {
        return new self(0);
    }

    /**
     * @param IntegerInterface|int $value
     */
    private function __construct($value)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->value = ToInteger::toInteger($value);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return int
     */
    public function getVisibilityMalus()
    {
        if ($this->getValue() > 0) {
            return -$this->getValue();
        }

        return 0;
    }

}