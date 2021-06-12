<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\MessageFormType;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class DefaultController extends AbstractController
{
    private function sendMail($message) {
        $from='stefan@stefan-sander.online';
        $to='stefan@stefan-sander.online';
        return shell_exec (
                'sendemail -f '. $from.' -t '.$to.' -u subject -m '. escapeshellarg($message) . ' -s '.$_ENV['SMTP_SERVER'].':587 -o tls=yes -xu '.$_ENV['SMTP_USER'].' -xp '.$_ENV['SMTP_USER_PASSWORD']
            );
    }

    #[Route('/', name: 'default', methods: ['GET'])]
    public function index(MailerInterface $mailer,Request $request): Response
    {


        $form = $this->createForm(MessageFormType::class);
        return $this->render('default/default.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    #[Route('/', name: 'post', methods: ['POST'])]
    public function post(Request $request): Response
    {
        $form = $this->createForm(MessageFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $message = $data->getMessage();
            $name = $data->getName();
            $email = $data->getEmail();
            $result = $this->sendMail($name.'<'.$email . '>:' . $message);
            return new JsonResponse(['response' => true,'data'=> $result]);
        }
        return new JsonResponse(['response' => false]);
    }
}
