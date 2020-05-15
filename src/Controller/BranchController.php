<?php

namespace App\Controller;

use App\Entity\Branch;
use App\Form\BranchType;
use App\Repository\BranchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/branch")
 */
class BranchController extends AbstractController
{
    /**
     * @Route("/", name="branch_index", methods={"GET"})
     */
    public function index(BranchRepository $branchRepository): Response
    {
        return $this->render('branch/index.html.twig', [
            'branches' => $branchRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="branch_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $branch = new Branch();
        $form = $this->createForm(BranchType::class, $branch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $request->files->get('branch')['attachment'];
            if($file) {
                $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );
            }
           
            $entityManager = $this->getDoctrine()->getManager();
            $branch->setImage($filename);
            $entityManager->persist($branch);
            $entityManager->flush();

            return $this->redirectToRoute('branch_index');
        }

        return $this->render('branch/new.html.twig', [
            'branch' => $branch,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="branch_show", methods={"GET"})
     */
    public function show(Branch $branch): Response
    {
        return $this->render('branch/show.html.twig', [
            'branch' => $branch,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="branch_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Branch $branch): Response
    {
        $form = $this->createForm(BranchType::class, $branch);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('branch')['attachment'];
            if($file) {
                $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );
            }
           
            $branch->setImage($filename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('branch_index');
        }

        return $this->render('branch/edit.html.twig', [
            'branch' => $branch,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="branch_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Branch $branch): Response
    {
        //if ($this->isCsrfTokenValid('delete'.$branch->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($branch);
            $entityManager->flush();
        //}

        return $this->redirect($this->generateUrl('branch_index'));
        //return $this->redirectToRoute('branch_index');
    }
}
