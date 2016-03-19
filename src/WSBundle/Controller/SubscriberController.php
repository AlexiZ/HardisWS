<?php

namespace WSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use WSBundle\Entity\Subscriber;

class SubscriberController extends Controller
{
    /**
    * Controller action to get informations about a subcriber
    *
    * @param int id
    */
    public function uniqueAction($id)
    {
        //fetch a subscriber by his unique id
        $subscriber = $this->getDoctrine()
           ->getRepository('WSBundle:Subscriber')
           ->findOneBy(
                array(
                   'id' => $id
                )
            );

        //render results to template
        return $this->render(
            'WSBundle:Subscriber:unique.html.twig',
            array(
                'id' => $id,
                'subscriber' => $subscriber
            )
        );
    }
    
    /**
    * Controller action to get informations about all asubscribers
    *
    */
    public function subscribersAction()
    {
        //fetch all subscribers ordered by fields 'nom' and 'prenom'
        $subscribers = $this->getDoctrine()
            ->getRepository('WSBundle:Subscriber')
            ->findAll(
                array(),
                array(
                    'nom' => 'ASC',
                    'prenom' => 'ASC'
                )
            );
        //render results to template
        return $this->render(
            'WSBundle:Subscriber:subscribers.html.twig',
            array(
                'subscribers' => $subscribers
            )
        );
    }

    /**
    * Controller action to import data from a CSV file
    *
    * @param Request $request
    */
    public function importAction(Request $request)
    {
        //create a form with an attachment field and a submit button
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

        //if data have been posted
        if ($request->getMethod('post') == 'POST') {
            //set variables for reading
            $header = NULL;
            $data = array();
                
            // Bind request to the form
            $form->bind($request);

            // If form is valid
            if ($form->isValid()) {
                //get the posted file
                $file = $form['attachment']->getData();
                //get its name
                $filename = $file->getPathname();

                //open the file
                if (($handle = fopen($filename, 'r')) !== FALSE) {
                    //read the file lines
                    while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
                        //get headers data
                        if(!$header)
                            $header = $row;
                        else
                            $data[] = array_combine($header, $row);
                    }
                    //end of reading, closing the file
                    fclose($handle);

                    //create an entityManager element
                    $em = $this->container->get('doctrine')->getEntityManager();
                    //read the lines from the previous file
                    foreach ($data as $line) {
                        //if the subscriber already exists, get it by its id
                        $subscriber = $this->getDoctrine()
                           ->getRepository('WSBundle:Subscriber')
                           ->findOneBy(
                                array(
                                    'id' => trim($line['identifiant'])
                                )
                            );

                        //check if subscriber already exists
                        if (!$subscriber) {
                            //if not, create a new one
                            $subscriber = new Subscriber();
                            //and set its id
                            $subscriber->setId(trim($line['identifiant']));
                        }
                        //in any case, set its other field
                        //it will update the entity if already exists
                        $subscriber->setNom(trim($line['nom']));
                        $subscriber->setPrenom(trim($line['prenom']));
                        $subscriber->setTelephone(trim($line['telephone']));

                        //persists it in the database
                        $em->persist($subscriber);
                        $em->flush();
                    }
                }
            }
        }

        //render the template
        return $this->render(
            'WSBundle:Subscriber:import.html.twig',
            array(
                'form' => $form->createView(),
                'lines' => $data
            )
        );
    }
}
