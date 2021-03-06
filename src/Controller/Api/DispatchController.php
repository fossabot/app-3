<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\DispatchRequest;
use App\Api\Response\Data\CraftsmanData;
use App\Api\Response\Data\DispatchData;
use App\Api\Transformer\Dispatch\CraftsmanTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Helper\DateTimeFormatter;
use App\Model\Craftsman\CurrentIssueState;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/dispatch")
 */
class DispatchController extends ApiController
{
    const INVALID_CRAFTSMAN = 'invalid craftsman';

    /**
     * gives the appropiate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/craftsman/list", name="api_dispatch_craftsman_list", methods={"POST"})
     *
     * @param Request $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function listAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $data = new CraftsmanData();
        $data->setCraftsmen($craftsmanTransformer->toApiMultiple($constructionSite->getCraftsmen()->toArray()));

        return $this->success($data);
    }

    /**
     * @Route("", name="api_dispatch", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailServiceInterface $emailService
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function dispatchAction(Request $request, TranslatorInterface $translator, EmailServiceInterface $emailService)
    {
        /** @var DispatchRequest $dispatchRequest */
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, DispatchRequest::class, $dispatchRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //get all craftsmen
        $craftsmanRepo = $this->getDoctrine()->getRepository(Craftsman::class);
        /** @var Craftsman[] $craftsmen */
        $craftsmen = [];
        foreach ($dispatchRequest->getCraftsmanIds() as $craftsmanId) {
            /** @var Craftsman $craftsman */
            $craftsman = $craftsmanRepo->find($craftsmanId);
            if (!$craftsman->getConstructionSite() === $constructionSite) {
                return $this->fail(self::INVALID_CRAFTSMAN);
            }
            $craftsmen[] = $craftsman;
        }

        $sentEmails = 0;
        $errorEmails = 0;
        $skippedEmails = 0;

        $now = new \DateTime();
        foreach ($craftsmen as $craftsman) {
            //count event occurrences
            $state = new CurrentIssueState($craftsman, $now);

            //only send emails if there are issues
            if ($state->getNotRespondedIssuesCount() === 0) {
                ++$skippedEmails;
                continue;
            }

            // build up base text
            if ($state->getOverdueIssuesCount() > 0) {
                $subject = $translator->transChoice('email.overdue.subject', $state->getOverdueIssuesCount(), [], 'dispatch');
                $body = $translator->transChoice('email.overdue.body', $state->getNotRespondedIssuesCount(), [], 'dispatch');
            } elseif ($state->getNotReadIssuesCount() > 0) {
                $subject = $translator->transChoice('email.unread.subject', $state->getNotReadIssuesCount(), [], 'dispatch');
                $body = $translator->transChoice('email.unread.body', $state->getNotRespondedIssuesCount(), [], 'dispatch');
            } else {
                $subject = $translator->transChoice('email.open.subject', $state->getNotRespondedIssuesCount(), [], 'dispatch');
                $body = $translator->transChoice('email.open.body', $state->getNotRespondedIssuesCount(), [], 'dispatch');
            }

            //append next limit info
            if ($state->getOverdueIssuesCount() === 0 && $state->getNextResponseLimit() !== null) {
                $body .= "\n";
                $body .= $translator->trans(
                    'email.body_limit_info',
                    ['%limit%' => $state->getNextResponseLimit()->format(DateTimeFormatter::DATE_FORMAT)],
                    'dispatch'
                );
            }

            //append closed issues info
            if ($state->getRecentlyReviewedIssuesCount() > 0) {
                $body .= "\n";
                $body .= $translator->transChoice('email.body_closed_issues_infos', $state->getRecentlyReviewedIssuesCount(), [], 'dispatch');
            }

            //append suffix
            $subject .= $translator->trans('email.subject_appendix', ['%construction_site_name%' => $constructionSite->getName()], 'dispatch');

            //send email
            $email = new Email();
            $email->setEmailType(EmailType::ACTION_EMAIL);
            $email->setReceiver($craftsman->getEmail());
            $email->setSubject($subject);
            $email->setBody($body);
            $email->setActionText($translator->trans('email.action_text', [], 'dispatch'));
            $email->setActionLink($this->generateUrl('external_view_issues', ['identifier' => null], UrlGeneratorInterface::ABSOLUTE_URL));
            $this->fastSave($email);

            if ($emailService->sendEmail($email)) {
                $email->setSentDateTime(new \DateTime());
                $this->fastSave($email);

                ++$sentEmails;
            } else {
                ++$errorEmails;
            }
        }

        $dispatchData = new DispatchData();
        $dispatchData->setErrorEmailCount($errorEmails);
        $dispatchData->setSentEmailCount($sentEmails);
        $dispatchData->setSkippedEmailCount($skippedEmails);

        return $this->success($dispatchData);
    }
}
