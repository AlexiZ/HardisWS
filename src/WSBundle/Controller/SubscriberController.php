<?php

namespace WSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use WSBundle\Entity\Subscriber;

class SubscriberController extends Controller
{
    public function uniqueAction($id)
    {
        $subscriber = $this->getDoctrine()
           ->getRepository('WSBundle:Subscriber')
           ->findOneBy(
                array(
                   'id' => $id
                )
            );
        return $this->render(
            'WSBundle:Subscriber:unique.html.twig',
            array(
                'id' => $id,
                'subscriber' => $subscriber
            )
        );
    }
    
    public function subscribersAction()
    {
        $subscribers = $this->getDoctrine()
            ->getRepository('WSBundle:Subscriber')
            ->findAll(
                array(),
                array(
                    'id' => 'DESC'
                )
            );
        return $this->render(
            'WSBundle:Subscriber:subscribers.html.twig',
            array(
                'subscribers' => $subscribers
            )
        );
    }

    public function importAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add(
                'attachment',
                'file',
                array(
                    'label' => 'File to Submit'
                )
            )
            ->add(
                'submit',
                'submit',
                array(
                    'label' => 'Envoyer le fichier'
                )
            )
            ->getForm();
        if ($request->getMethod('post') == 'POST') {
            // Bind request to the form
            $form->bind($request);

            // If form is valid
            if ($form->isValid()) {
                $file = $form['attachment']->getData();
                $filename = $file->getPathname();

                $header = NULL;
                $data = array();
                if (($handle = fopen($filename, 'r')) !== FALSE) {
                    while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
                        if(!$header)
                            $header = $row;
                        else
                            $data[] = array_combine($header, $row);
                    }
                    fclose($handle);

                    $em = $this->container->get('doctrine')->getEntityManager();
                    foreach ($data as $line) {
                        $subscriber = $this->getDoctrine()
                           ->getRepository('WSBundle:Subscriber')
                           ->findOneBy(
                                array(
                                    'id' => trim($line['identifiant'])
                                )
                            );
                        if (!$subscriber) {
                            $subscriber = new Subscriber();
                            $subscriber->setId(trim($line['identifiant']));
                        }
                        $subscriber->setNom(trim($line['nom']));
                        $subscriber->setPrenom(trim($line['prenom']));
                        $subscriber->setTelephone(trim($line['telephone']));

                        $em->persist($subscriber);
                        $em->flush();
                    }
                }
            }
        }

        return $this->render(
            'WSBundle:Subscriber:import.html.twig',
            array(
                'form' => $form->createView(),
                'lines' => $data
            )
        );
    }
}
