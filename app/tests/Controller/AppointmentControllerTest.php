<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppointmentControllerTest extends WebTestCase
{
    public function testPatientCanCreateAppointment(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);

        // On suppose que tu as un utilisateur "patient" en base
        $patient = $userRepository->findOneBy(['email' => 'karim@moumid.com']);
        $this->assertNotNull($patient, "Patient non trouvé.");

        $client->loginUser($patient);

        // Aller sur la page d'ajout de rendez-vous
        $crawler = $client->request('GET', '/appointment/ajouter');
        $this->assertResponseIsSuccessful();

        // Récupération du formulaire
        $form = $crawler->selectButton('Confirmer la réservation')->form();

        // Date future (format d/m/Y)
        $form['appointment_form[date]'] = (new \DateTime('+1 day'))->format('d/m/Y');
        $form['appointment_form[time]'] = '10:00';

        // Numéro de sécurité sociale valide existant en base
        $form['appointment_form[socialSecurityNumber]'] = '1234567891011';

        // Catégories :
        $form['appointment_form[categories][0]']->tick(); // coche la première catégorie si elle existe

        // Envoi du formulaire
        $client->submit($form);

        // Vérifie la redirection après succès
        $this->assertResponseRedirects('/appointment');
        $client->followRedirect();

        // Vérifie le message flash
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Rendez-vous enregistré');
    }
}
