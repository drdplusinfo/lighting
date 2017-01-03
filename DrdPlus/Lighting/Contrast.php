<?php
namespace DrdPlus\Lighting;

use DrdPlus\Calculations\SumAndRound;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Tables\Races\SightRangesTable;
use Granam\Integer\PositiveInteger;
use Granam\Strict\Object\StrictObject;

/**
 * see PPH page 128 left column, @link https://pph.drdplus.jaroslavtyc.com/#oslneni
 */
class Contrast extends StrictObject implements PositiveInteger
{
    /**
     * @var int
     */
    private $value;
    /**
     * @var bool
     */
    private $fromLightToDark;

    /**
     * @param LightingQuality $previousLightingQuality
     * @param LightingQuality $currentLightingQuality
     * @return Contrast
     */
    public static function createBySimplifiedRules(
        LightingQuality $previousLightingQuality,
        LightingQuality $currentLightingQuality
    )
    {
        $difference = $previousLightingQuality->getValue() - $currentLightingQuality->getValue();

        return new self($difference, 10);
    }

    /**
     * @param EyesAdaptation $eyesAdaptation
     * @param LightingQuality $currentLightingQuality
     * @param RaceCode $raceCode
     * @param SightRangesTable $sightRangesTable
     * @return Contrast
     */
    public static function createByExtendedRules(
        EyesAdaptation $eyesAdaptation,
        LightingQuality $currentLightingQuality,
        RaceCode $raceCode,
        SightRangesTable $sightRangesTable
    )
    {
        $difference = $eyesAdaptation->getValue() - $currentLightingQuality->getValue();

        return new self($difference, $sightRangesTable->getAdaptability($raceCode));
    }

    /**
     * @param int $lightsDifference
     * @param int $eyeAdaptability
     */
    private function __construct($lightsDifference, $eyeAdaptability)
    {
        $this->fromLightToDark = $lightsDifference > 0;
        $base = abs($lightsDifference) / $eyeAdaptability;
        /** see PPH page 128 left column, @link https://pph.drdplus.jaroslavtyc.com/#oslneni */
        $this->value = SumAndRound::floor($base);
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
        $asString = (string)$this->getValue();
        if ($this->isFromLightToDark()) {
            $asString .= ' (to dark)';
        } else if ($this->isFromDarkToLight()) {
            $asString .= ' (to light)';
        } // else nothing if contrast is zero

        return $asString;
    }

    /**
     * @return bool
     */
    public function isFromLightToDark()
    {
        return $this->fromLightToDark;
    }

    /**
     * @return bool
     */
    public function isFromDarkToLight()
    {
        return !$this->isFromLightToDark() && $this->getValue() !== 0;
    }
}