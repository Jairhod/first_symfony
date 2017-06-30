<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Commentaire;
use AppBundle\Form\ArticleType;
use AppBundle\Form\CommentaireType;
use AppBundle\Service\Extrait;
use AppBundle\Service\ExtraitWithLink;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/blog")
 */
class BlogController extends Controller {

    /**
     * @Route("/{p}", name="homepage_blog",
     * defaults={"p": 1},
     * requirements={"p": "\d+"})
     */
    public function indexAction(Request $request, Extrait $extrait, ExtraitWithLink $extraitWithLink, $p) {

        //$page_active = $p;
        $limit = $this->getParameter('item_par_page');
        $offset = (int) ($limit * ($p - 1));

        $em = $this->getDoctrine()->getManager();
        $ar = $em->getRepository('AppBundle:Article');
        $articles = $ar->getArticlesWithPagination($offset, $limit);
        $nb_articles = $articles->count();
        $nb_pages = ceil($nb_articles / $limit);





        //       $articles = $ar->getArticles();
        //   foreach ($articles as $article)
        //     $article->setExtrait($extraitWithLink->get($article));
//            $article->setExtrait($extrait->get($article->getContenu()));


        return $this->render('blog/index.html.twig', ['pages' => $nb_pages, 'page_active' => $p, 'articles' => $articles]);
    }

    /**
     * @Route("/detail/{id}", name="detail_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function detailAction(Request $request, $id) {

        //$article = ['id' => $id, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime];

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        $article = $ar->getArticleByIdWithJoin($id);

        $commentaire = new Commentaire();
        $commentaire->setArticle($article);


        $form = $this->createForm(CommentaireType::class, $commentaire, ['action' => $this->generateUrl('ajouter_commentaire_blog', ['id' => $id])]);

        //$url = $article->getImage()->getUrl();
//
//        $cr = $em->getRepository('AppBundle:Commentaire');
//
//        $commentaires = $cr->findBy(['article' => $article]);

        return $this->render('blog/detail.html.twig', ['article' => $article, 'form' => $form->createView()]);
    }

    /**
     * @Method({"POST"})
     * @Route("/ajouter_commentaire_blog/{id}", name="ajouter_commentaire_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function ajouterCommentaireAction(Request $request, Article $article) {

        $commentaire = new Commentaire();
        $commentaire->setArticle($article);

        $form = $this->createForm(CommentaireType::class, $commentaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');
            $em->persist($commentaire);

            try {
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Commenté');
                return $this->redirectToRoute('detail_blog', ['user' => $user, 'id' => $article->getId()]);
            } catch (Exception $e) {
                $session->getFlashBag()
                        ->add('erreur', 'Non commenté ' . $e->getMessage() . $e->getFile());
            }
        }
    }

    /**
     *
     * @Route("/ajax_commentaire_blog", name="ajax_commentaire_blog")
     *
     *
     */
    public function ajouterAjaxCommentaireAction(Request $request) {

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');
            $contenu = $request->request->get('contenu');
            $user = $this->getUser();

            $em = $this->getDoctrine()->getManager();
            $ar = $em->getRepository('AppBundle:Article');
            $article = $ar->find($id);
            $commentaire = new Commentaire();
            $commentaire->setArticle($article);
            $commentaire->setContenu($contenu);
            $commentaire->setUser($user);
            $em->persist($commentaire);

            try {
                $em->flush();
                return new JsonResponse(
                        ['success' => true,
                    'commentaire' => ['id' => $commentaire->getId(),
                        'contenu' => $commentaire->getContenu(),
                        'date' => $commentaire->getDate()->format('Y-m-d'),
                        'user' => $user->getUsername(),
                    ]
                ]);
            } catch (Exception $e) {
                return new JsonResponse(['success' => false]);
            }

            return new JsonResponse();
        } else {
            throw new HttpException(403);
        }
    }

    /**
     * @Route("/supprimer/{id}", name="supprimer_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function supprimerAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $article = $em->getRepository('AppBundle:Article')->find($id);

        $em->remove($article);

        $session = $this->get('session');

        try {

            $em->flush();
            $session->getFlashBag()
                    ->add('succes', 'Article supprimé');
            $session->getFlashBag()
                    ->add('succes', 'Bien, bien supprimé!');
            return $this->redirectToRoute('homepage_blog');
        } catch (Exception $e) {
            $session->getFlashBag()
                    ->add('erreur', 'Non supprimé' . $e->getMessage() . $e->getFile());
            return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
        }
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Route("/ajouter", name="ajouter_blog")     *
     *
     */
    public function ajouterAction(Request $request) {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Bugger Off!');
        $user = $this->getUser();
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        //Traitement formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');
            $article->setUser($user);
            try {
                $em->persist($article);
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Article ajouté');
                $session->getFlashBag()
                        ->add('succes', 'Bien, bien ajouté!');
                return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
            } catch (Exception $e) {
                $session->getFlashBag()
                        ->add('erreur', 'Non ajouté' . $e->getMessage() . $e->getFile());
            }
        }

        return $this->render('blog/ajouter.html.twig', ['form' => $form->createView()]);


//        $formBuilder = $this->createFormBuilder($article);
//
//        $formBuilder->add('titre')
//                ->add('contenu')
//                ->add('date', DateType::class, array('widget' => 'single_text', 'html5' => false, 'format' => 'yyyy-MM-dd', 'attr' => ['class' => 'js-datepicker']))
//                ->add('publication', null, ['required' => false, 'label' => 'Publié ?'])
//                ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
//
//        $form = $formBuilder->getForm();
//
//
//        $article->setTitre('Hello World')
//                ->setContenu('Lorem em rem');
//
//        $image = new \AppBundle\Entity\Image();
//        $image->setUrl('https://robohash.org/' . rand() . '.png')
//                ->setAlt('robohash');
//
//        $comm1 = new \AppBundle\Entity\Commentaire();
//        $comm1->setContenu('Really good');
//
//        $comm2 = new \AppBundle\Entity\Commentaire();
//        $comm2->setContenu('Brilliant');
//
//        $tag1 = new \AppBundle\Entity\Tag();
//        $tag1->setTitre('Marvin');
//
//        $tag2 = new \AppBundle\Entity\Tag();
//        $tag2->setTitre('R2D2');
//
//        $tag3 = new \AppBundle\Entity\Tag();
//        $tag3->setTitre('Hal');
//
//        $article->setImage($image);
//        $comm1->setArticle($article);
//        $comm2->setArticle($article);
//        $article->addTag($tag1);
//        $article->addTag($tag2);
//        $article->addTag($tag3);
//
//        $doctrine = $this->getDoctrine();
//        $em = $doctrine->getManager();
//
//        $em->persist($article);
//        $em->persist($comm1);
//        $em->persist($comm2);
//
//        $em->flush();
//
//        return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
//
//
        // return $this->render('blog/ajouter.html.twig', ['id' => $id]);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN') or user == art.getUser()")
     * @Route("/modifier/{id}", name="modifier_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function modifierAction(Request $request, $id, Article $art) {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Article')->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $session = $this->get('session');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');
            $article->setUser($user);

            try {
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Article Modifié');

                return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
            } catch (Exception $e) {
                $session->getFlashBag()
                        ->add('erreur', 'Non Modifié' . $e->getMessage() . $e->getFile());
            }
        }

        return $this->render('blog/modifier.html.twig', ['form' => $form->createView()]);
    }

    public function footerAction($nb) {

//
//        $articles = [
//                ['id' => 1, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//                ['id' => 2, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//                ['id' => 3, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//        ];
//
//

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        //$articles = $ar->findBy(['publication' => true], ['date' => 'DESC', 'titre' => 'ASC'], $nb);
        $articles = $ar->getLatestArticles($nb);
        $years = $ar->getCurrentYear();


        // replace this example code with whatever you need
        return $this->render('blog/footer.html.twig', ['years' => $years, 'articles' => $articles]);
    }

    /**
     * @Route("/archive/{year}", name="year_articles",
     *
     * requirements={"year": "\d+"})
     *
     */
    public function yearAction($year) {

        $em = $this->getDoctrine()->getManager();
        $ar = $em->getRepository('AppBundle:Article');

        $articles = $ar->getArticlesByYear($year);

        return $this->render('blog/year.html.twig', ['year' => $year, 'articles' => $articles]);
    }

    /**
     * @Route("/tag/{id}", name="tag_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function tagAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        $tr = $em->getRepository('AppBundle:Tag');

        $query = $ar->getArticlesByTagWithJoin($id);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 2
        );

        $tag = $tr->find($id);

        $count = $ar->getCountByTag($tag);

        return $this->render('blog/tag.html.twig', ['tag' => $tag, 'pagination' => $pagination, 'count' => $count]);
    }

}
