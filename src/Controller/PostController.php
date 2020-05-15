<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/post", name="post.")
 */

class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param PostRepository $postRepository
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();

        //dump($posts);
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request) {
        // create a new post with title
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        
        if($form->isSubmitted()) {
            // entity manager
            $em = $this->getDoctrine()->getManager();

            $file = $request->files->get('post')['attachment'];
            if($file) {
                $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

                $file->move(
                    $this->getParameter('uploads_dir'),
                    $filename
                );
            }
            $post->setImage($filename);
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }
        
        // return response  
        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    public function show(Post $post) {

        //$post = $postRepository->findPostWithCategory($id);

        //dump($post);
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function remove(Post $post) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        // Flashdata 
        $this->addFlash('success','Post was removed');

        return $this->redirect($this->generateUrl('post.index'));
    }
}
