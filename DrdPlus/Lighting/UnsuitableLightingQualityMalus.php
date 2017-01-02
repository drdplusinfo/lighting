<?php
namespace DrdPlus\Lighting;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Lighting\Partials\WithInsufficientLightingBonus;
use Granam\Integer\NegativeInteger;
use Granam\Strict\Object\StrictObject;

/**
 * See PPH page 128
 */
class UnsuitableLightingQualityMalus extends StrictObject implements NegativeInteger
{
    /**
     * @var int
     */
    private $malus;

    /**
     * @param LightingQuality $currentLightingQuality
     * @param Opacity $barrierOpacity
     * @param RaceCode $raceCode
     * @param WithInsufficientLightingBonus $duskSight
     * @param bool $infravisionCanBeUsed Allows current situation to use infravision?
     */
    public function __construct(
        LightingQuality $currentLightingQuality,
        Opacity $barrierOpacity,
        RaceCode $raceCode,
        WithInsufficientLightingBonus $duskSight,
        $infravisionCanBeUsed
    )
    {
        $this->malus = 0;
        if ($barrierOpacity->getValue() > 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $currentLightingQuality = new LightingQuality($currentLightingQuality->getValue() - $barrierOpacity->getValue());
        }
        if ($currentLightingQuality->getValue() < -10) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $contrast = Contrast::createBySimplifiedRules(new LightingQuality(0), $currentLightingQuality);
            $possibleMalus = -$contrast->getValue();
            if (in_array($raceCode->getValue(), [RaceCode::DWARF, RaceCode::ORC], true)) {
                $possibleMalus += 4; // lowering malus
            } else if ($raceCode->getValue() === RaceCode::KROLL) {
                $possibleMalus += 2; // lowering malus
            }
            if ($infravisionCanBeUsed && $currentLightingQuality->getValue() <= -90 // like star night
                && in_array($raceCode->getValue(), [RaceCode::DWARF, RaceCode::ORC], true)
            ) {
                /** lowering malus by infravision, see PPH page 129 right column, @link https://pph.drdplus.jaroslavtyc.com/#infravideni */
                $possibleMalus += 3;
            }
            $possibleMalus += $duskSight->getInsufficientLightingBonus(); // lowering malus
            if ($possibleMalus >= -20) {
                if ($possibleMalus < 0) {
                    $this->malus = $possibleMalus;
                }
            } else {
                $this->malus = -20; // maximal possible malus on absolute dark, see PPH page 128 right column bottom
            }
        } else if ($currentLightingQuality->getValue() >= 60 /* strong daylight */ && $raceCode->getValue() === RaceCode::ORC) {
            // see PPH page 128 right column bottom
            $this->malus = -2;
        }
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->malus;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

}