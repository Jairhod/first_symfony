<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/ajouter/{id}", name="ajouter_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function ajouterAction(Request $request) {

        $article = new \AppBundle\Entity\Article();
        $article->setTitre('Hello World')
                ->setContenu('Lorem em rem');

        $image = new \AppBundle\Entity\Image();
        $image->setUrl('https://robohash.org/' . rand() . '.png')
                ->setAlt('robohash');

        $comm1 = new \AppBundle\Entity\Commentaire();
        $comm1->setContenu('Really good');

        $comm2 = new \AppBundle\Entity\Commentaire();
        $comm2->setContenu('Brilliant');

        $tag1 = new \AppBundle\Entity\Tag();
        $tag1->setTitre('Marvin');

        $tag2 = new \AppBundle\Entity\Tag();
        $tag2->setTitre('R2D2');

        $tag3 = new \AppBundle\Entity\Tag();
        $tag3->setTitre('Hal');

        $article->setImage($image);
        $comm1->setArticle($article);
        $comm2->setArticle($article);
        $article->addTag($tag1);
        $article->addTag($tag2);
        $article->addTag($tag3);

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        $em->persist($article);
        $em->persist($comm1);
        $em->persist($comm2);

        $em->flush();

        return $this->redirectToRoute('detail_blog', ['id' => $article->getId()]);

        // return $this->render('blog/ajouter.html.twig', ['id' => $id]);
    }

    /**
     * @Route("/modifier/{id}", name="modifier_blog",
     *
     * requirements={"id": "\d+"})
     *
     */
    public function modifierAction(Request $request, $id) {
        return $this->render('blog/modifier.html.twig', ['id' => $id]);
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

        $articles = $ar->findBy(['publication' => true], ['date' => 'DESC', 'titre' => 'ASC'], $nb);


        // replace this example code with whatever you need
        return $this->render('blog/footer.html.twig', ['nb' => $nb, 'articles' => $articles]);
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
