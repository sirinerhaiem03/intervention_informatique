<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TicketRepository;
use App\Entity\Ticket;
use App\Enum\PrioriteEnum;
use App\Enum\StatutEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {


        return $this->render('base.html.twig', [

        ]);
    }

    #[Route('/tickets', name: 'app_tickets')]
    public function tickets(TicketRepository $ticketRepository): Response
    {
        $utilisateur = $this->getUser();
        if ($utilisateur instanceof \App\Entity\Utilisateur) {
            $tickets = $ticketRepository->findByUtilisateur($utilisateur);
        } else {
            $tickets = [];
        }

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/tickets/store', name: 'ticket_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em): Response
    {
        $ticket = new Ticket();
        $ticket->setTitre($request->request->get('titre'));
        $ticket->setDescription($request->request->get('description'));

        // Conversion string -> Enum
        $prioriteString = $request->request->get('priorite');
        $prioriteEnum = PrioriteEnum::from($prioriteString);
        $ticket->setPriorite($prioriteEnum);

        $ticket->setStatut(StatutEnum::EN_ATTENTE);
        $ticket->setDateCreation(new \DateTime());

        // Correction : associer l'utilisateur connecté
        $utilisateur = $this->getUser();
        $ticket->setUtilisateur($utilisateur);

        $em->persist($ticket);
        $em->flush();

        // Optionnel : message flash
        $this->addFlash('success', 'Le ticket a été créé avec succès !');

        return $this->redirectToRoute('app_tickets');
    }

    #[Route('/tickets/{id}', name: 'ticket_show', requirements: ['id' => '\d+'])]
    public function show(Ticket $ticket, Request $request, EntityManagerInterface $em, CommentaireRepository $commentaireRepository): Response
    {
        $utilisateur = $this->getUser();
        // Gestion édition ticket (déjà existant)
        if ($request->isMethod('POST') && $request->request->has('titre') && $ticket->getUtilisateur() === $utilisateur) {
            $ticket->setTitre($request->request->get('titre'));
            $ticket->setDescription($request->request->get('description'));
            $prioriteString = $request->request->get('priorite');
            $ticket->setPriorite(\App\Enum\PrioriteEnum::from($prioriteString));
            $em->flush();

            $this->addFlash('success', 'Le ticket a été modifié avec succès !');
            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getIdTicket()]);
        }
        // Gestion ajout commentaire
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
                $this->addFlash('success', 'Commentaire ajouté !');
                return $this->redirectToRoute('ticket_show', ['id' => $ticket->getIdTicket()]);
            }
        }
        // Récupération des commentaires du ticket (ordre chronologique)
        $commentaires = $ticket->getCommentaires();
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/tickets/{id}/delete', name: 'ticket_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Ticket $ticket, EntityManagerInterface $em): Response
    {
        $utilisateur = $this->getUser();
        if ($ticket->getStatut()?->value === 'En attente' && $ticket->getUtilisateur() === $utilisateur) {
            $em->remove($ticket);
            $em->flush();
            $this->addFlash('success', 'Le ticket a été supprimé.');
        } else {
            $this->addFlash('error', 'Suppression impossible : seul le propriétaire peut supprimer un ticket en attente.');
        }
        return $this->redirectToRoute('app_tickets');
    }


}
