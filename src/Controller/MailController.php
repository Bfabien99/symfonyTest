<?php

namespace App\Controller;

use App\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
// use Symfony\Component\Mailer\MailerInterface;
// use Symfony\Component\Mime\Email;

#[Route('/mail')]
class MailController extends AbstractController{

    #[Route('/', name: 'mail.index')]
    public function index(Request $request){

        $form = $this->createForm(MailType::class);
        $contact = $form->handleRequest($request);

        $transport = Transport::fromDsn('smtp://localhost:1025');
        $mailer = new Mailer($transport);

        if($form->isSubmitted() && $form->isValid()){
            $email = (new Email())
                ->from('fabien@example.com')
                ->to($contact->get('email')->getData())
                ->subject('Test Envoie Email')
                ->text($contact->get('message')->getData());
    
            $mailer->send($email);
                
            $this->addFlash('message','Votre email a été envoyé');
            return $this->redirectToRoute('mail.index');
        }

        return $this->render('post/mail.html.twig', [
            'form' => $form->createView()
        ]);
    }
}