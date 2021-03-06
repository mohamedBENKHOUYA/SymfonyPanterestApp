<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\User;
use App\Form\FormPinCreateType;
use App\Form\FormPinType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PinsController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerINterface $em) {
        $this->em = $em;
    }


    /**
     * @Route("/", requirements={}, name="app_home", methods={"GET"})
     */
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $pinRepo = $em->getRepository(Pin::class);
        $query = $pinRepo->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();

        $pins = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
//            $this->getParameter('app.number_pages_pagination') /*limit per page*/
            Pin::NUM_ITEMS_PER_PAGE
        );
//        $repo = $em->getRepository(Pin::class);
//        $pins = $pinRepo->findBy([], ['createdAt' => 'DESC']);
            $count = $pinRepo->count([]);
        return $this->render('/pins/index.html.twig', compact('pins', 'count'));

    }

    /**
     * @Route("/pins/create", requirements={}, name="app_pins_create", methods={"GET", "POST"})
     */
    public function create(Request $req, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        // prepopulate, and guess textareaType
        // and $pin = $form->getData(); and validation form(class and constraints validation)
        // and l'objet pass?? va ??tre modifi??(avec les setters avec les donn??es de form)
        // and correspondance will be applied for fields (mapping when creating form)
        // imageName have to be wether File or null. without $pin => null(ici null car
        // on a pas mis $pin dedans)
        // base => string image => $pin => Error
        $form = $this->createForm(FormPinType::class);
        $form->handleRequest($req);

        if ($form->isSubmitted() and $form->isValid()) {
//            dd($form->getData());

//            $repoUser = $em->getRepository('App:User');
//            $user1 = $repoUser->find(1);
            // getUser in controller, in template, in service (appExtensionTwig), in authenticator($token->getUser)...
            $user = $this->getUser();
            $pin = $form->getData();
            $pin->setUser($user);
//            $pin = new Pin($data['title'], $data['description']);
            $em->persist($pin);
            $em->flush();
            $this->addFlash('success', 'pin successfully created');

            return $this->redirectToRoute('app_home');
        }
            return $this->render('/pins/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show", methods={"GET"})
     */
    public function show(EntityManagerInterface $em, int $id)
    {
        $repo = $em->getRepository(Pin::class);
        $pin = $repo->find($id);
        if (!$pin) {
            throw $this->createNotFoundException('pin no found');
        }


        return $this->render('/pins/show.html.twig', compact('pin'));
    }

    /**
     * @param int $id
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_delete", methods={"DELETE"})
     */
    public function Delete(Request $req, int $id, EntityManagerInterface $em)
    {
        $_tokenUser = $req->request->all()['_token'];
        $repo = $em->getRepository(Pin::class);
        $pin = $repo->find($id);
        if ( $this->isCsrfTokenValid('pin_delete_'.$id, $_tokenUser) ) {
            $em->remove($pin);
            $em->flush();
            $this->addFlash('info','Pin successfully deleted');
            return $this->redirectToRoute('app_home');
        }
        else {
            throw $this->createNotFoundException('Error related to csrf');
        }


    }



    /**
     * @param Request $req
     * @param EntityManagerInterface $em
     * @param int $id
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit", methods={"GET","PUT"})
     */
    public function Edit(Request $req, EntityManagerInterface $em, int $id, SluggerInterface $slugger)
    {
        $repo = $em->getRepository(Pin::class);
        $pin = $repo->find($id);
        if (!$pin) {
            throw $this->createNotFoundException('this pin not found');
        }
//        $pin->setImageName(
//            new File($this->getParameter('images_directory').'/'.$pin->getImageName())
//        );
        $form = $this->createForm(FormPinType::class, $pin, [
            'method' => 'PUT',
        ]);
        // juste apr??s cela, il va vers FormPinType

        // here: setTitle take place before validation
        // em has already one Pin(em->flush). we don't create it like in Create()
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Pin successfully edited');
            return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
        }

        return $this->render('/pins/edit.html.twig', [
            'form' => $form->createView(),
            'pin' => $pin]
        );


    }
}
