<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CardRepository")
 * @ORM\Table(name="card")
 * @ORM\HasLifecycleCallbacks
 */
class Card {

    const STATUS_INACTIVE = 'Inactive';
    const STATUS_ACTIVE = 'Active';
    const STATUS_EXPIRED = 'Expired';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=4)
     */
    protected $series;

    /**
     * @ORM\Column(type="string", length=12)
     */
    protected $number;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $issue_date;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiry_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $usage_date;

    /**
     * @ORM\Column(type="decimal", scale=2,  options={"default":0})
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="card")
     */
    protected $purchase;

    /** @ORM\PostLoad */
    public function doStuffOnPostLoad() {
        if ($this->getExpiryDate() < new \DateTime) {
            $this->setStatus(self::STATUS_EXPIRED);
        }
    }

    public function __construct() {
        $this->purchase = new ArrayCollection();
    }

    public function versatileGetter($property) {
        return $this->$property;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set series
     *
     * @param integer $series
     *
     * @return Card
     */
    public function setSeries($series) {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return integer
     */
    public function getSeries() {
        return $this->series;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Card
     */
    public function setNumber($number) {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     *
     * @return Card
     */
    public function setIssueDate($issueDate) {
        $this->issue_date = $issueDate;

        return $this;
    }

    /**
     * Get issueDate
     *
     * @return \DateTime
     */
    public function getIssueDate() {
        return $this->issue_date;
    }

    /**
     * Set expiryDate
     *
     * @param \DateTime $expiryDate
     *
     * @return Card
     */
    public function setExpiryDate($expiryDate) {
        $this->expiry_date = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return \DateTime
     */
    public function getExpiryDate() {
        return $this->expiry_date;
    }

    /**
     * Set usageDate
     *
     * @param \DateTime $usageDate
     *
     * @return Card
     */
    public function setUsageDate($usageDate) {
        $this->usage_date = $usageDate;

        return $this;
    }

    /**
     * Get usageDate
     *
     * @return \DateTime
     */
    public function getUsageDate() {
        return $this->usage_date;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Card
     */
    public function setAmount($amount) {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Card
     */
    public function setStatus($status) {
        if (is_int($status)) {
            $status = self::getStatusList()[$status];
        }
        if (!in_array($status, array(self::STATUS_INACTIVE, self::STATUS_ACTIVE, self::STATUS_EXPIRED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        /*
          if (!self::getStatusList()[$status]) {
          throw new \InvalidArgumentException("Invalid status");
          }
         */
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    public static function getStatusList() {
        return array(
            self::STATUS_INACTIVE,
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED
        );
    }

    /**
     * Add purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     *
     * @return Card
     */
    public function addPurchase(\AppBundle\Entity\Purchase $purchase) {
        $this->purchase[] = $purchase;

        return $this;
    }

    /**
     * Remove purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     */
    public function removePurchase(\AppBundle\Entity\Purchase $purchase) {
        $this->purchase->removeElement($purchase);
    }

    /**
     * Get purchase
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchase() {
        return $this->purchase;
    }

}
