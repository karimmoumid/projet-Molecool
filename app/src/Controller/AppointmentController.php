<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentForm;
use App\Repository\AppointmentRepository;
use App\Repository\SocialSecurityNumberRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'app_appointment')]
    public function index(AppointmentRepository $appointmentRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $appointments =[];
        $patients = [];
        if($this->isGranted("ROLE_EMPLOYEE")) {
            $appointments = $appointmentRepository->findBy([], ['date_hour' => 'ASC']);
            $patients = array_filter(
                $userRepository->findAll(),
                fn($user) => in_array('ROLE_PATIENT', $user->getRoles())
            );

        }
        if($this->isGranted("ROLE_PATIENT")) {
            $appointments = $appointmentRepository->findBy(['patient' => $user], ['date_hour' => 'ASC']);
        }

        $groupedAppointement = [];
        foreach ($appointments as $appointment) {
            $day = $appointment->getDateHour()->format('d/m/Y');
            $hour = $appointment->getDateHour()->format('H:i');
            if($this->isGranted("ROLE_EMPLOYEE")) {
                $patientName = $appointment->getPatient()->getName();
                $groupedAppointement[$day][$hour][] = [
                    'appointment' => $appointment,
                    'patientName' => $patientName,
                ];
            }

            if($this->isGranted("ROLE_PATIENT")) {
                $groupedAppointement[$day][$hour] = [$appointment];
            }
        }
        return $this->render('appointment/show.html.twig', compact('groupedAppointement', 'patients'));
    }

    #[Route('/appointment/ajouter', name: 'app_appointment_add')]
    public function add(
        EmailService $emailSender,
        Request $request,
        EntityManagerInterface $em,
        SocialSecurityNumberRepository $socialSecurityNumberRepository,
        AppointmentRepository $appointmentRepository,
        UserRepository $userRepository,
        Security $security
    ): Response {
        $appointment = new Appointment();

        $isEmployee = $security->isGranted('ROLE_EMPLOYEE');
        $isPatient = $security->isGranted('ROLE_PATIENT');
        // On prépare les patients à afficher dans le select si l'admin est connecté
        $patients = $isEmployee ? $userRepository->findByRole('ROLE_PATIENT') : [];

        $form = $this->createForm(AppointmentForm::class, $appointment, [
            'available_slots' => [],
            'is_employee' => $isEmployee,
            'patients' => $patients,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isPatient) {
                $user = $this->getUser();
                $appointment->setPatient($user);

                // Vérif Sécu Sociale
                $ssnEntered = $form->get('socialSecurityNumber')->getData();
                $ssn = $socialSecurityNumberRepository->findOneBy(['number' => $ssnEntered]);

                if (!$ssn) {
                    $this->addFlash('danger', 'Numéro de sécurité sociale non valide');
                    return $this->redirectToRoute('app_appointment_add');
                }
            } elseif ($isEmployee) {
                $selectedPatient = $form->get('patient')->getData();
                $appointment->setPatient($selectedPatient);
            } else {
                $this->addFlash('danger', 'Accès refusé.');
                return $this->redirectToRoute('app_appointment_add');
            }

            // Date / Heure
            $date = $form->get('date')->getData();
            $time = $form->get('time')->getData();
            $now = new \DateTimeImmutable();

            $dateTimeString = $date . ' ' . $time;
            $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $dateTimeString);

            if (!$dateTime || $dateTime < $now) {
                $this->addFlash('danger', 'Date/heure invalide ou passée.');
                return $this->redirectToRoute('app_appointment_add');
            }

            $appointment->setDateHour($dateTime);

            // Conflits
            $existingForPatient = $appointmentRepository->findOneBy([
                'date_hour' => $dateTime,
                'patient' => $appointment->getPatient()
            ]);
            if ($existingForPatient) {
                $this->addFlash('danger', 'Ce patient a déjà un rendez-vous à cette heure.');
                return $this->redirectToRoute('app_appointment_add');
            }

            $existingAppointments = $appointmentRepository->findBy(['date_hour' => $dateTime]);
            if (count($existingAppointments) >= 2) {
                $this->addFlash('danger', 'Ce créneau est complet.');
                return $this->redirectToRoute('app_appointment_add');
            }

            $em->persist($appointment);
            $em->flush();

            $emailSender->sender(
                'no-reply@centre-medical.com',
                $appointment->getPatient()->getEmail(),
                'Confirmation de votre rendez-vous',
                'new_appointement',
                [
                    'user' => $appointment->getPatient(),
                    'appointment' => $appointment
                ]
            );

            $this->addFlash('success', 'Rendez-vous enregistré avec succès.');
            return $this->redirectToRoute('app_appointment');
        }

        return $this->render('appointment/add.html.twig', compact('form'));
    }

    #[Route('/appointment/modifier/{id}', name: 'app_appointment_modify')]
    public function modify(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $em,
        SocialSecurityNumberRepository $socialSecurityNumberRepository,
        AppointmentRepository $appointmentRepository,
        UserRepository $userRepository,
        Security $security
    ): Response {
        $now = new \DateTime('now');
        if ($now > $appointment->getDateHour()) {
            $this->addFlash('danger', 'Ce rendez-vous est déjà passé et ne peut pas être modifié');
            return $this->redirectToRoute('app_appointment');
        }

        $isEmployee = $security->isGranted('ROLE_EMPLOYEE');
        $isPatient = $security->isGranted('ROLE_PATIENT');

        $patients = $isEmployee ? $userRepository->findByRole('ROLE_PATIENT') : [];

        $form = $this->createForm(AppointmentForm::class, $appointment, [
            'is_employee' => $isEmployee,
            'disable_patient_select' => true,
            'patients' => $patients,
            'available_slots' => [],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($isPatient) {
                $user = $this->getUser();
                $appointment->setPatient($user);

                $ssnEntered = $form->get('socialSecurityNumber')->getData();
                $ssn = $socialSecurityNumberRepository->findOneBy(['number' => $ssnEntered]);

                if (!$ssn) {
                    $this->addFlash('danger', 'Numéro de sécurité sociale non valide');
                    return $this->redirectToRoute('app_appointment_modify', ['id' => $appointment->getId()]);
                }
            } elseif (!$isPatient && !$isEmployee) {
                $this->addFlash('danger', 'Accès refusé.');
                return $this->redirectToRoute('app_appointment');
            }

            $date = $form->get('date')->getData();
            $time = $form->get('time')->getData();
            $dateTimeString = $date . ' ' . $time;
            $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $dateTimeString);

            if (!$dateTime || $dateTime < $now) {
                $this->addFlash('danger', 'Date/heure invalide ou passée.');
                return $this->redirectToRoute('app_appointment_modify', ['id' => $appointment->getId()]);
            }

            $appointment->setDateHour($dateTime);

            // Vérification conflits rendez-vous
            $existingSamePatient = $appointmentRepository->findOneBy([
                'date_hour' => $dateTime,
                'patient' => $appointment->getPatient(),
            ]);

            if ($existingSamePatient && $existingSamePatient->getId() !== $appointment->getId()) {
                $this->addFlash('danger', 'Ce patient a déjà un rendez-vous à cette heure.');
                return $this->redirectToRoute('app_appointment_modify', ['id' => $appointment->getId()]);
            }

            $existingAppointments = $appointmentRepository->findBy(['date_hour' => $dateTime]);
            if (count($existingAppointments) >= 2) {
                $this->addFlash('danger', 'Ce créneau est déjà complet.');
                return $this->redirectToRoute('app_appointment_modify', ['id' => $appointment->getId()]);
            }

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Rendez-vous modifié avec succès.');
            return $this->redirectToRoute('app_appointment');
        }

        $getDate = $appointment->getDateHour()->format('d/m/Y');
        $getTime = $appointment->getDateHour()->format('H:i');

        return $this->render('appointment/modify.html.twig', compact('form', 'getDate', 'getTime'));
    }


    #[Route('/appointment/supprimer/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, Appointment $appointment): Response
    {
        // Vérifier le token CSRF envoyé dans le formulaire (nom du token = 'delete_appointment' + id)
        if ($this->isCsrfTokenValid('delete_appointment' . $appointment->getId(), $request->request->get('_token'))) {
            $em->remove($appointment);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('app_appointment');
    }



    #[Route('/api/available-slots', name: 'api_available_slots')]
    public function availableSlots(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {
        $date = $request->query->get('date'); // YYYY-MM-DD
        if (!$date) {
            return $this->json([], 400);
        }
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $now = new \DateTimeImmutable();

        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' 08:00');
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' 18:00');

        if (!$start || !$end) {
            return $this->json([], 400);
        }

        $availableSlots = [];
        for ($time = $start; $time <= $end; $time = $time->modify('+30 minutes')) {
            if ($date === $today && $time <= $now) {
                continue;
            }
            // max 2 rendez-vous par créneau
            $count = $appointmentRepository->count(['date_hour' => $time]);
            if ($count < 2) {
                $availableSlots[] = [
                    'label' => $time->format('H:i'),
                    'value' => $time->format('H:i'),
                ];
            }
        }

        return $this->json($availableSlots);
    }






}
