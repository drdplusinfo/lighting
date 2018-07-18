<?php
declare(strict_types = 1);

namespace DrdPlus\Lighting;

use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;

/**
 * See PPH page 129 left column bottom, @link https://pph.drdplus.jaroslavtyc.com/#svetle_predmety_a_zdroje_svetla
 */
class SpotLightingQuality extends StrictObject implements Partials\LightingQualityInterface
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param LightingQuality $surroundingLightingQuality
     * @return SpotLightingQuality
     */
    public static function createForVeryDarkItem(LightingQuality $surroundingLightingQuality)
    {
        return new self($surroundingLightingQuality->getValue() - 20);
    }

    /**
     * @param LightingQuality $surroundingLightingQuality
     * @return SpotLightingQuality
     */
    public static function createForBrightWhiteItem(LightingQuality $surroundingLightingQuality)
    {
        return new self($surroundingLightingQuality->getValue() + 20);
    }

    /**
     * @param LightingQuality $surroundingLightingQuality
     * @return SpotLightingQuality
     */
    public static function createForLightSource(LightingQuality $surroundingLightingQuality)
    {
        return new self($surroundingLightingQuality->getValue() + 30);
    }

    /**
     * @param int|IntegerInterface $value
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

}