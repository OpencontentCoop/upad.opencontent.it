<?php
/**
 * File containing the user HashGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\SPI\HashGenerator as HashGeneratorInterface;
use eZ\Publish\SPI\User\Identity as IdentityInterface;
use eZ\Publish\SPI\User\IdentityAware;

/**
 * User hash generator.
 */
class HashGenerator implements HashGeneratorInterface, IdentityAware
{
    /**
     * @var IdentityInterface
     */
    protected $userIdentity;

    /**
     * @var IdentityAware[]
     */
    protected $identityDefiners = array();

    /**
     * @param IdentityAware $identityDefiner
     */
    public function setIdentityDefiner( IdentityAware $identityDefiner )
    {
        $this->identityDefiners[] = $identityDefiner;
    }

    /**
     * @return IdentityAware[]
     */
    public function getIdentityDefiners()
    {
        return $this->identityDefiners;
    }

    /**
     * @param IdentityInterface $identity
     */
    public function setIdentity( IdentityInterface $identity )
    {
        $this->userIdentity = $identity;
    }

    /**
     * @return IdentityInterface
     */
    public function getIdentity()
    {
        return $this->userIdentity;
    }

    /**
     * Generates the user hash
     *
     * @return string
     */
    public function generate()
    {
        foreach ( $this->getIdentityDefiners() as $identityDefiner )
        {
            $identityDefiner->setIdentity( $this->userIdentity );
        }

        return $this->userIdentity->getHash();
    }
}
