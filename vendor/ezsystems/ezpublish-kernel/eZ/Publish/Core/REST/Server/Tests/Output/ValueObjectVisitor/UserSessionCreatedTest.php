<?php
/**
 * File containing the SessionTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version 2014.07.0
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common;

class UserSessionCreatedTest extends UserSessionTest
{
    /**
     * Test the Session visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $session = new Values\UserSession(
            $this->getUserMock(),
            "sessionName",
            "sessionId",
            "csrfToken",
            true
        );

        $this->getVisitorMock()->expects( $this->any() )
            ->method( 'setStatus' )
            ->with( $this->equalTo( 201  ) );

        $this->getVisitorMock()->expects( $this->at( 1 ) )
            ->method( 'setHeader' )
            ->with( $this->equalTo( 'Content-Type' ), $this->equalTo( 'application/vnd.ez.api.Session+xml' ) );

        $this->addRouteExpectation(
            'ezpublish_rest_deleteSession',
            array(
                'sessionId' => $session->sessionId
            ),
            "/user/sessions/{$session->sessionId}"
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadUser',
            array( 'userId' => $session->user->id ),
            "/user/users/{$session->user->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $session
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }
}
