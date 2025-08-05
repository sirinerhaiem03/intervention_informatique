<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TicketRepository;
use App\Entity\Ticket;
use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/technicien')]
class TechnicienController extends AbstractController
{
    #[Route('/tickets', name: 'technicien_tickets')]
    public function index(TicketRepository $ticketRepository): Response
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur || !in_array('ROLE_TECHNICIEN', $utilisateur->getRoles())) {
            throw $this->createAccessDeniedException('Accès réservé aux techniciens.');
        }
        $tickets = $ticketRepository->findAll();
        return $this->render('technicien/tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/tickets/{id}', name: 'technicien_ticket_show', requirements: ['id' => '\d+'])]
    public function show(Ticket $ticket, Request $request, EntityManagerInterface $em, CommentaireRepository $commentaireRepository): Response
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur || !in_array('ROLE_TECHNICIEN', $utilisateur->getRoles())) {
            throw $this->createAccessDeniedException('Accès réservé aux techniciens.');
        }
        // Gestion changement de statut
        if ($request->isMethod('POST') && $request->request->has('changer_statut')) {
            $nouveauStatut = $request->request->get('statut');
            if (in_array($nouveauStatut, ['EN_ATTENTE', 'EN_COURS', 'RESOLU'])) {
                $ticket->setStatut(\App\Enum\StatutEnum::from($nouveauStatut));
                $em->flush();
                $this->addFlash('success', 'Statut du ticket mis à jour.');
                return $this->redirectToRoute('technicien_ticket_show', ['id' => $ticket->getIdTicket()]);
            }
        }
        // Gestion ajout commentaire/diagnostic
        if ($request->isMethod('POST') && $request->request->has('contenu_commentaire')) {
            $contenu = trim($request->request->get('contenu_commentaire'));
            if ($contenu && $utilisateur) {
                $commentaire = new Commentaire();
                $commentaire->setContenu($contenu);
                $commentaire->setDateCommentaire(new \DateTime());
                $commentaire->setUtilisateur($utilisateur);
                $commentaire->setTicket($ticket);
                $em->persist($commentaire);
                $em->flush();
                $this->addFlash('success', 'Commentaire/diagnostic ajouté !');
                return $this->redirectToRoute('technicien_ticket_show', ['id' => $ticket->getIdTicket()]);
            }
        }
        $commentaires = $ticket->getCommentaires();
        return $this->render('Technicien/ticket_show.html.twig', [
            'ticket' => $ticket,
            'commentaires' => $commentaires,
        ]);
    }
     #[Route('/home', name: 'home_technicien')]
    public function index_tech(): Response
    {


        return $this->render('Technicien/basetech.html.twig', [

        ]);
    }
}
