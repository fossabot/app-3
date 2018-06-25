<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use App\Entity\ConstructionManager;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Form\Traits\User\LoginType;
use App\Form\Traits\User\RecoverType;
use App\Form\Traits\User\SetPasswordType;
use App\Service\Interfaces\EmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/login")
 */
class LoginController extends BaseLoginController
{
    /**
     * @Route("/", name="login_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $form = $this->createForm(LoginType::class);
        $form->add('form.login', SubmitType::class);

        return $this->render('login/index.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/recover", name="login_recover")
     *
     * @param Request $request
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function recoverAction(Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $form = $this->handleForm(
            $this->createForm(RecoverType::class)
                ->add('form.recover', SubmitType::class),
            $request,
            function ($form) use ($emailService, $translator, $logger) {
                /* @var FormInterface $form */

                //display success
                $this->displaySuccess($translator->trans('recover.success.email_sent', [], 'frontend_login'));

                //check if user exists
                $exitingUser = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['email' => $form->getData()['email']]);
                if (null === $exitingUser) {
                    $logger->info("could not reset password of unknown user " . $form->getData()['email']);
                    return $form;
                }

                //create new reset hash
                $exitingUser->setResetHash();
                $this->fastSave($exitingUser);

                //create email
                $email = new Email();
                $email->setEmailType(EmailType::ACTION_EMAIL);
                $email->setReceiver($exitingUser->getEmail());
                $email->setSubject($translator->trans('recover.email.reset_password.subject', [], 'frontend_login'));
                $email->setBody($translator->trans('recover.email.reset_password.message', [], 'frontend_login'));
                $email->setActionText($translator->trans('recover.email.reset_password.action_text', [], 'frontend_login'));
                $email->setActionLink($this->generateUrl('login_reset', ['resetHash' => $exitingUser->getResetHash()], UrlGeneratorInterface::ABSOLUTE_URL));

                //send & save
                $emailService->sendEmail($email);
                $this->fastSave($email);
                $logger->info("sent password reset email to " . $email->getReceiver());

                return $form;
            }
        );

        $arr = [];
        $arr['form'] = $form->createView();

        return $this->render('login/recover.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/reset/{resetHash}", name="login_reset")
     *
     * @param Request $request
     * @param $resetHash
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function resetAction(Request $request, $resetHash, TranslatorInterface $translator)
    {
        $arr = [];

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(['resetHash' => $resetHash]);
        if (null !== $user) {
            $form = $this->handleForm(
                $this->createForm(SetPasswordType::class, $user, ['data_class' => ConstructionManager::class])
                    ->add('form.set_password', SubmitType::class),
                $request,
                function ($form) use ($user, $translator, $request) {
                    //check for valid password
                    if ($user->getPlainPassword() !== $user->getRepeatPlainPassword()) {
                        $this->displayError($translator->trans('reset.error.passwords_do_not_match', [], 'frontend_login'));
                        return $form;
                    }

                    //display success
                    $this->displaySuccess($translator->trans('reset.success.password_set', [], 'frontend_login'));

                    //set new password & save
                    $user->setPassword();
                    $user->setResetHash();
                    $this->fastSave($user);

                    //login user & redirect
                    $this->loginUser($request, $user);
                    return $this->redirectToRoute('dashboard_index');
                }
            );

            if ($form instanceof Response) {
                return $form;
            }

            $arr['form'] = $form->createView();
        } else {
            $this->displayError($translator->trans('reset.error.invalid_hash', [], 'frontend_login'));
        }

        return $this->render('login/reset.html.twig', $arr);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="login_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using form_login.logout in your security firewall configuration.');
    }
}
