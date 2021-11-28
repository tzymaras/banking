<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Services\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{

    private AccountService $accountService;

    /**
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @param Request                     $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface      $entityManager
     * @param LoggerInterface             $logger
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('password')->getData())
            );

            $entityManager->persist($user);
            $entityManager->flush();

            try {
                $this->accountService->createAccount($this->createDummyAccount($user), $user);
            } catch (\Throwable $throwable) {
                $entityManager->remove($user);
                $entityManager->flush();

                $logger->warning('failed adding dummy account', ['reason' => $throwable->getMessage()]);
                $this->addFlash('notice', 'failed adding dummy account');

                return $this->redirectToRoute('app_register');
            }

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @param User $user
     * @return Account
     */
    private function createDummyAccount(User $user): Account
    {
        $dummyIBANS = $this->getParameter('dummy_ibans');
        $iban       = $dummyIBANS[\mt_rand(0, \count($dummyIBANS) - 1)]['iban'];

        $dummyAccount = new Account();
        $dummyAccount->setUser($user);
        $dummyAccount->setBalance(\mt_rand(500000, 5000000));
        $dummyAccount->setUuid(\uniqid());
        $dummyAccount->setIban($iban);

        return $dummyAccount;
    }

}
