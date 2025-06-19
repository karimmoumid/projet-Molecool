<?php

namespace App\Service;



use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService {
    private MailerInterface $mailer;
    public function __construct(MailerInterface $mailer){

        $this->mailer = $mailer;
    }
    public function sender(string $from, string $to, string $subject, string $template, array $context = [])
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('emails/' . $template . '.html.twig')
            ->context($context)
        ;
        $this->mailer->send($email);
    }



}