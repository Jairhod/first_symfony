<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/blog")
 */
class BlogController extends Controller {

    /**
     * @Route("/{p}", name="homepage_blog",
     * defaults={"p": 1},
     * requirements={"p": "\d+"})
     */
    public function indexAction(Request $request, $p) {

//
//        $articles = [
//                ['id' => 1, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//                ['id' => 2, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//                ['id' => 3, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime],
//        ];
//

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        $articles = $ar->getArticles();


        // replace this example code with whatever you need
        return $this->render('blog/index.html.twig', ['page' => $p, 'articles' => $articles]);
    }

    /**
     * @Route("/detail/{id}", name="detail_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function detailAction($id) {

        //$article = ['id' => $id, 'titre' => 'Hello world', 'contenu' => 'Lorem <strong>lo rem</strong> rem lo', 'date' => new \DateTime];

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');

        $article = $ar->getArticleByIdWithJoin($id);

        //$url = $article->getImage()->getUrl();
//
//        $cr = $em->getRepository('AppBundle:Commentaire');
//
//        $commentaires = $cr->findBy(['article' => $article]);

        return $this->render('blog/detail.html.twig', ['article' => $article]);
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
     * @Route("/ajouter", name="ajouter_blog")
     *
     *
     */
    public function ajouterAction(Request $request) {

        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        //Traitement formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');

            try {
                $em->persist($article);
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Article ajouté');
                $session->getFlashBag()
                        ->add('succes', 'Bien, bien ajouté!');
                return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
            } catch (\Exception $e) {
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
     * @Route("/modifier/{id}", name="modifier_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function modifierAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Article')->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $session = $this->get('session');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $session = $this->get('session');

            try {
                $em->flush();
                $session->getFlashBag()
                        ->add('succes', 'Article Modifié');

                return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);
            } catch (\Exception $e) {
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
    public function tagAction($id) {

        $em = $this->getDoctrine()->getManager();

        $ar = $em->getRepository('AppBundle:Article');
        $tr = $em->getRepository('AppBundle:Tag');

        $articles = $ar->getArticlesByTagWithJoin($id);
        $tag = $tr->find($id);

        $count = $ar->getCountByTag($tag);

        return $this->render('blog/tag.html.twig', ['tag' => $tag, 'articles' => $articles, 'count' => $count]);
    }

}
