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
use App\Repository\UtilisateurRepository;

class AdminController extends AbstractController
{


    #[Route('admin/tickets/{id}', name: 'adminticket_show', requirements: ['id' => '\d+'])]
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
        return $this->render('Responsable/adminshowtickets.html.twig', [
            'ticket' => $ticket,
            'commentaires' => $commentaires,
        ]);
    }

 #[Route('/adminhome', name: 'admin_home')]
    public function adminhome(): Response
    {
        return $this->render('Responsable/homeadmin.html.twig');
    }

#[Route('/admin/tickets', name: 'tickets_admin')]

public function ticketadmin(TicketRepository $ticketRepository): Response
{
    $tickets = $ticketRepository->findAll();

    return $this->render('Responsable/admintickets.html.twig', [
        'tickets' => $tickets,
    ]);
}

#[Route('/admin/tickets/auto-attribuer', name: 'tickets_auto_attribuer')]
public function autoAttribuer(EntityManagerInterface $em, TicketRepository $ticketRepository, UtilisateurRepository $utilisateurRepository): Response
{
   $techniciens = $utilisateurRepository->findByRole(\App\Enum\RoleEnum::TECHNICIEN->value);
    $tickets = $ticketRepository->findBy(['technicien' => null]);

    $countTech = count($techniciens);
    if ($countTech === 0) {
        $this->addFlash('error', 'Aucun technicien disponible.');
        return $this->redirectToRoute('tickets_admin');
    }

    $i = 0;
    foreach ($tickets as $ticket) {
        $ticket->setTechnicien($techniciens[$i % $countTech]);
        $i++;
    }
    $em->flush();

    $this->addFlash('success', 'Attribution automatique effectuée !');
    return $this->redirectToRoute('tickets_admin');
}
}
