<?php
/**
 * CreateObjectStateGroupSignal class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateObjectStateGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class CreateObjectStateGroupSignal extends Signal
{
    /**
     * ObjectStateGroupId
     *
     * @var mixed
     */
    public $objectStateGroupId;
}

