<?php

namespace App\Controller\Apply;

use DateTimeZone;
use App\Entity\Apply;
use App\Entity\Offers;
use App\Entity\Company;
use App\Entity\Student;
use App\Repository\ApplyRepository;
use App\Service\Mailer\ApplyMailer;
use App\Repository\OffersRepository;
use App\Repository\CompanyRepository;
use App\Repository\StudentRepository;
use App\Service\Recruitment\ApplyHelper;
use App\Service\Recruitment\RecruitHelper;
use App\Service\UserChecker\StudentChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/apply")
 */
class ApplyActionsController extends AbstractController
{
    /**
     * @Route("/offers/{id}/student/{student_id}/page/{page}", name="apply", methods={"POST"})
     * @IsGranted("ROLE_SUPER_STUDENT")
     * @ParamConverter("student", options={"id" = "student_id"})
     */
    public function apply(Offers $offers, Student $student, ApplyRepository $repository, StudentChecker $checker, Request $request, ApplyHelper $helper, ApplyMailer $mailer, $page)
    {
        // check if apply is available
        if($helper->checkHired('offers', $offers) 
        || $helper->checkAgree('offers', $offers) 
        || $helper->checkConfirmed('offers', $offers) 
        || $helper->checkFinished('offers', $offers) ) {  
            $this->addFlash('error', 'Offre Indisponible');
            return $this->redirectToRoute('offers_index');
        }

        // check if some offers are waiting
        if($helper->checkHired('student', $student)){
            $this->addFlash('error', 'Vous avez des offres en attente. Consultez votre profil');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }

        // check if student is already hired
        if($helper->checkAgree('student', $student) || $helper->checkConfirmed('student', $student)) {
            $this->addFlash('error', 'Vous êtes déjà embauché ailleurs. Rendez-vous sur votre profil.');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }

        // check if student is refused
        if( $helper->checkRefused($offers, $student)) {  
            $this->addFlash('error', 'Offre non disponible');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }

        // check if student have already applied to current offer 
        if($helper->checkApply($offers, $student)) {  
            $this->addFlash('error', 'Vous avez déjà postulé');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }

        if($this->isCsrfTokenValid('apply'.$student->getId(), $request->request->get('_token'))) {
            // send notification
            $content = "<p>L'Offre : <strong>" .$offers->getTitle(). "</strong> a trouvé preneur et plusieurs personnes ont déjà postulées ! </p><br>
            <p>Nous vous invitons à aller voir votre compte et à répondre aux candidats aussi vite que possible.</p><br>";
            // $mailer->sendApplyNotification($offers, $content);
            $mailer->sendAppliesNotification(
                $offers->getCompany()->getUser()->getEmail(),
                $student->getName(), 
                $content
            );

            $apply = new Apply; 
            $apply->setHired(false);
            $apply->setAgree(false);
            $apply->setConfirmed(false);
            $apply->setRefused(false);
            $apply->setUnavailable(false);
            $apply->setFinished(false);
            $apply->setDateRecruit(new \DateTime('now', new DateTimeZone('Europe/Paris')));
            $apply->setDateFinished(new \DateTime('now', new DateTimeZone('Europe/Paris')));
            $apply->setOffers($offers);
            $apply->setStudent($student);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($apply);
            $manager->flush();

            $this->addFlash('success', 'Postulation enregistrée !');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('offers_show', ['id' => $offers->getId(), 'page' => $page]);
        }
    }

    /**
     * @Route("/hire/{id}", name="hire", methods={"POST"}) *******
     * @IsGranted("ROLE_SUPER_COMPANY")
     */
    public function hire(Apply $apply, ApplyRepository $repository, Request $request, ApplyHelper $helper)
    {   
        // get users
        $student = $apply->getStudent();
        $offers = $apply->getOffers();

        // check if student is available
        if($helper->checkAgree('student', $student) || $helper->checkConfirmed('student', $student)) {
            $this->addFlash('error', 'Cet étudiant n\'est plus disponible.');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }

        if($this->isCsrfTokenValid('hire'.$apply->getId(), $request->request->get('_token')))
        {
            // hire
            $helper->hire($apply, $student, $offers);
            // save
            $entityManager = $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Elève Embauché !');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);  
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }
    }

    /**
     * @Route("/agree/{id}", name="agree", methods={"POST"})
     * @IsGranted("ROLE_SUPER_STUDENT")
     */
    public function agree(ApplyRepository $repository, Apply $apply, Request $request, StudentChecker $checker, ApplyMailer $mailer, ApplyHelper $helper)
    {
        // get entities
        $student = $apply->getStudent();
        $offers = $apply->getOffers();

        if($this->isCsrfTokenValid('agree'.$student->getId(), $request->request->get('_token'))) {  
            // agree
            $helper->agree($apply, $student, $offers);
            
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'offre acceptée');
            return $this->redirectToRoute('student_apply', ['id' => $student->getId()]);
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('student_apply', ['id' => $student->getId()]);
        }
    }

    /**
     * @Route("/confirm/{id}", name="confirm", methods={"POST"})
     * @IsGranted("ROLE_SUPER_COMPANY")
     */
    public function confirm(Apply $apply, ApplyRepository $repository, Request $request, ApplyHelper $helper)
    {
        // get other applies
        $student = $apply->getStudent();
        $offers = $apply->getOffers();

        if($this->isCsrfTokenValid('confirm'.$apply->getId(), $request->request->get('_token'))) {
            // confirm 
            $helper->confirm($apply, $student, $offers);
    
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Mission Commencée. Bon travail !');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }
    }

     /**
     * @Route("/refuse/{id}", name="apply_refuse", methods={"POST"})
     * @IsGranted("ROLE_SUPER_COMPANY")
     */
    public function refuse(ApplyRepository $repository, Apply $apply, Request $request, ApplyMailer $mailer, ApplyHelper $helper)
    {
        // get users
        $student = $apply->getStudent();
        $offers = $apply->getOffers();
        // check if student has been refused already 
        if($apply->getRefused() == true) {
            $this->addFlash('error', 'Vous avez déjà refusé cette candidature');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }

        if($this->isCsrfTokenValid('refuse'.$apply->getId(), $request->request->get('_token'))) {
            // refuse
            $helper->refuse($apply, $student, $offers);
            
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Candidat refusée');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('offers_edit', ['id' => $offers->getId(), 'company' => $offers->getCompany()->getId()]);
        }
    }

    /**
     * @Route("/delete/{id}", name="apply_delete", methods={"DELETE"})
     * @IsGranted("ROLE_SUPER_STUDENT")
     * @ParamConverter("apply", options={"id" = "id"})
     */
    public function delete(Request $request, Apply $apply, ApplyRepository $repository, OffersRepository $offersRepository, CompanyRepository $companyRepository, ApplyMailer $mailer, StudentChecker $checker, ApplyHelper $helper): Response
    {
        $student = $apply->getStudent();
        $offers = $apply->getOffers();

        if ($this->isCsrfTokenValid('delete'.$apply->getId(), $request->request->get('_token'))) {
            // delete  
            $helper->delete($apply, $student, $offers);
           
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apply);
            $entityManager->flush();

            $this->addFlash('success', 'Candidature supprimée !');
            return $this->redirectToRoute('student_apply', ['id' => $student->getId()]);
        }
        else {
            $this->addFlash('error', 'requête invalide');
            return $this->redirectToRoute('student_apply', ['id' => $student->getId()]);
        }
    }

    /**
     * @Route("/delete/empty/student/{id}", name="delete_empty_studentSide", methods={"DELETE"})
     * @IsGranted("ROLE_SUPER_STUDENT")
     */
    public function deleteEmptyStudentSide(Request $request, Apply $apply): Response
    {   
        $student = $apply->getStudent();

        if ($this->isCsrfTokenValid('delete'.$apply->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apply);
            $entityManager->flush();

            return $this->redirectToRoute('student_finished', ['id' => $student->getId()]);
        }
    }

    /**
     * @Route("/delete/empty/company/{id}", name="delete_empty_companySide", methods={"DELETE"})
     * @IsGranted("ROLE_SUPER_COMPANY")
     */
    public function deleteEmptyCompanySide(Offers $offer, Request $request): Response 
    {
        $companyId = $offer->getCompany()->getId();

        if ($this->isCsrfTokenValid('delete'.$offer->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $applies = $offer->getApplies(); 

            foreach($applies as $applies) 
            {        
                $entityManager->remove($applies);
            }

            $entityManager->remove($offer);
            $entityManager->flush();

            return $this->redirectToRoute('offers_company_finished', ['id' =>  $companyId]);
        }
    }
}
