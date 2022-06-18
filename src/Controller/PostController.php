<?php
namespace App\Controller;

use App\Entity\Uploads;
use App\Form\UploadImageType;
use App\Repository\UploadsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/upload')]
class PostController extends AbstractController{

    /**
     * @param UploadsRepository $repository
     */
    private $repository;

     /**
     * @param EntityManagerInterface $em
     */
    private $em;

    public function __construct(UploadsRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    #[Route('/', name: 'post.index')]
    public function index():Response
    {
        $uploads = $this->repository->findAll();
        return $this->render('post/upload.html.twig', compact('uploads'));
    }

    #[Route('/new', name: 'post.new')]
    public function newupload(Request $request){
        $upload = new Uploads;

        $form = $this->createForm(UploadImageType::class, $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the image file must be processed only when a file is uploaded
            if($imageFile){
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = str_replace("/","",(strtolower($originalFilename)));
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                
                // Move the file to the directory where upload are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    die(var_dump($e));
                }

                $upload->setName($newFilename);
            }

            // ... perform some action, such as saving the task to the database
            $this->em->persist($upload);
            $this->em->flush();

            return $this->redirectToRoute('post.index');
        }

        return $this->render('post/new.html.twig', [
            "upload" => $upload,
            'form' => $form->createView()
        ]);
    }

}