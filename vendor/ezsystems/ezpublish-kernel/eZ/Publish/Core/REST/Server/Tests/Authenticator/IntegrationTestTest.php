<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\REST\Server\Tests\Authenticator;

use eZ\Publish\Core\REST\Server\Tests\BaseTest;

use eZ\Publish\Core\REST\Server\Authenticator\IntegrationTest;

use Qafoo\RMF;

/**
 * IntegrationTestTest
 * @todo Remove when the REST client is refactored
 */
class IntegrationTestTest extends BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userServiceMock;

    public function testAuthenticate()
    {
        $auth = new IntegrationTest( $this->getRepositoryMock() );

        $this->getUserServiceMock()
            ->expects( $this->once() )
            ->method( 'loadUser' )
            ->with( 23 )
            ->will(
                $this->returnValue(
                    $user = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\User\\User' )
                )
            );

        $this->getRepositoryMock()
            ->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $user );

        $request = new RMF\Request();
        $request->testUser = 23;

        $auth->authenticate( $request );
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepositoryMock()
    {
        if ( !isset( $this->repositoryMock ) )
        {
            $this->repositoryMock = $this->getMock(
                '\\eZ\\Publish\\API\\Repository\\Repository',
                array(),
                array(),
                '',
                false
            );

            $userServiceMock = $this->getUserServiceMock();

            $this->repositoryMock->expects( $this->any() )
                ->method( 'getUserService' )
                ->will(
                    $this->returnCallback(
                        function () use ( $userServiceMock )
                        {
                            return $userServiceMock;
                        }
                    )
                );
        }
        return $this->repositoryMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\UserService
     */
    protected function getUserServiceMock()
    {
        if ( !isset( $this->userServiceMock ) )
        {
            $this->userServiceMock = $this->getMock(
                '\\eZ\\Publish\\API\\Repository\\UserService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->userServiceMock;
    }
}
