<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Configuration\Form\Condition\Activation;

class EmptyActivation extends AbstractActivation
{
    /**
     * @var EmptyActivation
     */
    private static $instance;

    /**
     * @return EmptyActivation
     */
    public static function get()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}