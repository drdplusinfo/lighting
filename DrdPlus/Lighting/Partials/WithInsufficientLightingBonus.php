<?php
declare(strict_types = 1);

namespace DrdPlus\Lighting\Partials;

interface WithInsufficientLightingBonus
{
    /**
     * @return int
     */
    public function getInsufficientLightingBonus();
}