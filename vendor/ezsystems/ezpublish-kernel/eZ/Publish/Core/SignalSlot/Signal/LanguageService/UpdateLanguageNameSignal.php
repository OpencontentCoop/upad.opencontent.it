<?php
/**
 * UpdateLanguageNameSignal class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LanguageService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateLanguageNameSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LanguageService
 */
class UpdateLanguageNameSignal extends Signal
{
    /**
     * LanguageId
     *
     * @var mixed
     */
    public $languageId;

    /**
     * NewName
     *
     * @var mixed
     */
    public $newName;
}
