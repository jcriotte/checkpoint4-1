<?php

namespace App\Controller;

use Exception;
use App\Entity\Court;
use App\Form\CourtType;
use App\Repository\CourtRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/court", name="admin_")
 */
class CourtController extends AbstractController
{
    /**
     * @Route("/", name="court_index", methods={"GET"})
     */
    public function index(CourtRepository $courtRepository): Response
    {
        return $this->render('court/index.html.twig', [
            'courts' => $courtRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="court_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $court = new Court();
        $form = $this->createForm(CourtType::class, $court);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($court);
            $entityManager->flush();

            return $this->redirectToRoute('admin_court_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('court/new.html.twig', [
            'court' => $court,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="court_show", methods={"GET"})
     */
    public function show(Court $court): Response
    {
        return $this->render('court/show.html.twig', [
            'court' => $court,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="court_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Court $court, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourtType::class, $court);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_court_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('court/edit.html.twig', [
            'court' => $court,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="court_delete", methods={"POST"})
     */
    public function delete(Request $request, Court $court, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if (!is_string($token)) {
            throw new Exception('Token not valid');
        }

        if ($this->isCsrfTokenValid('delete' . $court->getId(), $token)) {
            $entityManager->remove($court);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_court_index', [], Response::HTTP_SEE_OTHER);
    }
}
