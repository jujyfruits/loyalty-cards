<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Card;
use AppBundle\Entity\Purchase;
use AppBundle\Form\CardGenerate;
use AppBundle\Form\CardSearch;
use Symfony\Component\HttpFoundation\Session\Session;

class CardController extends Controller {

    /**
     * @Route("/", name="card_list")
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $cards = $em->getRepository('AppBundle:Card')->findAll();
        $searchCard = new Card();
        $searchForm = $this->createForm(new CardSearch(), $searchCard);
        $generateForm = $this->createForm(new CardGenerate(), $searchCard);

        return $this->render('card/index.html.twig', array(
                    'cards' => $cards,
                    'search_form' => $searchForm->createView(),
                    'generate_form' => $generateForm->createView()
        ));
    }

    /**
     * @Route("/card/{cardId}/show", name="card_show")
     */
    public function showAction($cardId) {
        $em = $this->getDoctrine()->getManager();
        $card = $em->getRepository('AppBundle:Card')->find($cardId);
        $purchases = $em->getRepository('AppBundle:Card')->findCardPurchases($cardId);

        return $this->render('card/show.html.twig', array(
                    'card' => $card,
                    'purchases' => $purchases
        ));
    }

    /**
     * @Route("/card/{cardId}/purchase/generate", name="generate_purchase")
     */
    public function generatePurchaseAction($cardId) {
        $em = $this->getDoctrine()->getManager();
        $card = $em->getRepository('AppBundle:Card')->find($cardId);
        if ($card->getStatus() == Card::getStatusList()[2]) {
            throw $this->createException('Карта просрочена!');
        }
        for ($i = 0; $i < mt_rand(0, 8); $i++) {
            $purchase = new Purchase();
            $purchase->setCard($card);
            $purchase->setAmount($this->generateAmount(500, 4000));
            $purchase->setDate(new \DateTime());
            $cardAmount = $card->getAmount();
            $card->setAmount($cardAmount - $purchase->getAmount());
            $em->persist($purchase);
        }
        $em->flush();
        return $this->redirect($this->generateUrl('card_show', array('cardId' => $cardId)));
    }

    /**
     * @Route("/card/change", name="change_card")
     */
    public function changeCardAction(Request $request) {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $cardId = $request->request->get('cardId');
        $action = $request->request->get('action');
        $statusList = Card::getStatusList();
        $card = $em->getRepository('AppBundle:Card')->findOneById($cardId);
        if (!empty($card)) {
            if ($action == 'status') {
                if ($card->getStatus() == 'Inactive') {
                    $card->setStatus($statusList[1]);
                    $card->setUsageDate(new \DateTime);
                } elseif ($card->getStatus() == 'Active') {
                    $card->setStatus($statusList[0]);
                    $card->setUsageDate(null);
                }
                $em->persist($card);
            } elseif ($action == 'remove') {
                $em->remove($card);
            } else {
                return;
            }
            $em->flush();
            $cards = $em->getRepository('AppBundle:Card')->findAll();
            $view = $this->renderView('card/partial/card_list.html.twig', array(
                'cards' => $cards,
            ));
            $response = new Response($view);
            return $response;
        } else {
            return;
        }
    }

    /**
     * @Route("/card/generate", name="generate_cards")
     */
    public function generateCardsAction(Request $request) {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        /**/
        $entity = new Card();
        $form = $this->createForm(new CardGenerate(), $entity);
        $form->handleRequest($request);

        $expiryPeriod = $form->get("expiryPeriod")->getData();
        $series = $form->get("series")->getData();
        $amount = $form->get("amount")->getData();
        if ($amount && $series && $expiryPeriod) {
            list($currentNumber, $series) = $this->getMaxNumberOfSeries($series);
            for ($i = 0; $i < $amount; $i++) {
                $newCard = new Card;
                $newCard->setSeries($series);
                $newCard->setNumber(str_pad( ++$currentNumber, 8, "0", STR_PAD_LEFT));
                $newCard->setIssueDate(new \DateTime());
                $expiryDate = new \DateTime();
                $newCard->setExpiryDate($expiryDate->modify('+' . $expiryPeriod . ' months'));
                $newCard->setAmount(0);
                $newCard->setStatus(Card::STATUS_INACTIVE);
                $em->persist($newCard);
            }
            $em->flush();
            $cards = $em->getRepository('AppBundle:Card')->findAll();
            $view = $this->renderView('card/partial/card_list.html.twig', array(
                'cards' => $cards,
            ));
            $response = new Response($view);
            return $response;
        } else {
            return;
        }
    }

    /**
     * @Route("/card/search", name="search_cards")
     */
    public function searchCardsAction(Request $request) {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $entity = new Card();

        $form = $this->createForm(new CardSearch(), $entity);
        $form->handleRequest($request);
        $card = $form->getData();

        $repository = $em->getRepository('AppBundle:Card');

        $qb = $repository->createQueryBuilder('c');

        $columns = $em->getClassMetadata(get_class($entity))->getColumnNames();
        $count = 0;
        foreach ($columns as $column) {
            if (empty($card->versatileGetter($column))) {
                continue;
            }
            $property = $card->versatileGetter($column);
            if (is_a($property, 'DateTime')) {
                $property = $property->format("Y-m-d H:i:s");
            }
            if ($count == 0) {
                $qb->where('c.' . $column . '= :' . $column);
            } else {
                $qb->andWhere('c.' . $column . ' = :' . $column);
            }
            $qb->setParameter($column, $card->versatileGetter($column));
            $count++;
        }
        $qb->orderBy('c.id', 'ASC');
        $query = $qb->getQuery();

        $cards = $query->getResult();
        $view = $this->renderView('card/partial/card_list.html.twig', array(
            'cards' => $cards,
        ));
        $response = new Response($view);
        return $response;
    }

    public function getMaxNumberOfSeries($series) {
        $series = str_pad($series, 4, "0", STR_PAD_LEFT);
        $em = $this->getDoctrine()->getManager();
        $currentNumber = $em->getRepository('AppBundle:Card')->getMaxNumberOfSeries($series);
        if (!$currentNumber) {
            $currentNumber = 0;
        }
        if ($currentNumber == '99999999') {
            return getMaxNumberOfSeries( ++$series);
        } else {
            return array(
                $currentNumber,
                str_pad($series, 4, "0", STR_PAD_LEFT)
            );
        }
    }

    public function generateAmount($min, $max) {
        return number_format(($min + lcg_value() * (abs($max - $min))), 2, '.', '');
    }

}
