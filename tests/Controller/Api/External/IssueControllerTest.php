<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\External;

use App\Api\External\Entity\Base\BaseEntity;
use App\Api\External\Entity\Building;
use App\Api\External\Entity\Craftsman;
use App\Api\External\Entity\Issue;
use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\Map;
use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\ReadRequest;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Controller\Api\External\IssueController;
use App\Controller\Api\External\ReadController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use App\Tests\Controller\Base\FixturesTestCase;
use App\Tests\Controller\ServerData;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class IssueControllerTest extends ApiController
{
    /**
     * tests the create issue method.
     */
    public function testCreateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/issue/create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $issue = new Issue();
        $issue->setWasAddedWithClient(true);
        $issue->setIsMarked(true);
        $issue->setDescription('description');
        $issue->setMap($serverData->getMaps()[0]->getMeta()->getId());

        $issue->setStatus(new IssueStatus());

        $meta = new ObjectMeta();
        $meta->setId($this->getNewGuid());
        $meta->setLastChangeTime((new \DateTime())->format('c'));
        $issue->setMeta($meta);

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_GUID_ALREADY_IN_USE);

        //check issue without position
        $issue->setPosition(null);
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->verifyIssue($issueResponse->data->issue, $issue);
    }

    /**
     * tests the create issue method.
     */
    public function testUpdateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, 'json') . '}';
            $client->request(
                'POST',
                '/api/external/issue/update',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $imageFilename = $this->getNewGuid() . '.jpg';

        /** @var Issue $issue */
        $issue = $serverData->getIssues()[0];
        $issue->setWasAddedWithClient(false);
        $issue->setIsMarked(false);
        $issue->setDescription('description 2');
        $issue->setMap($serverData->getMaps()[0]->getMeta()->getId());

        $issue->setStatus(new IssueStatus());

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        //check with non-existing
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_NOT_FOUND);
    }

    /**
     * tests upload/download functionality.
     */
    public function testIssueActions()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get('serializer');
        $doRequest = function ($issueId, $action) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issueID":"' . $issueId . '"}';
            $client->request(
                'POST',
                '/api/external/issue/' . $action,
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);
        $issue = $serverData->getIssues()[0];

        $response = $doRequest($issue->getMeta()->getId(), 'mark');
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        //check response issue updated
        $issue->setIsMarked(!$issue->getIsMarked());
        $this->verifyIssue($issueResponse->data->issue, $issue);
        $issue = $serializer->deserialize(json_encode($issueResponse->data->issue), Issue::class, 'json');

        /* @var Issue[] $newIssues */
        /* @var Issue[] $registeredIssues */
        /* @var Issue[] $respondedIssues */
        /* @var Issue[] $reviewedIssues */
        $this->categorizeIssues($serverData->getIssues(), $newIssues, $registeredIssues, $respondedIssues, $reviewedIssues);

        //delete
        $response = $doRequest($newIssues[0]->getMeta()->getId(), 'delete');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($newIssues[0]->getMeta()->getId(), 'delete');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_NOT_FOUND);

        //review registered
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($registeredIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);

        //review responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'review');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);

        //revert reviewed
        $response = $doRequest($reviewedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::SUCCESS);

        //revert responded
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::SUCCESS);
        //revert twice because of earlier actions
        $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $response = $doRequest($respondedIssues[0]->getMeta()->getId(), 'revert');
        $this->checkResponse($response, ApiStatus::FAIL, IssueController::ISSUE_ACTION_NOT_ALLOWED);
    }
}
