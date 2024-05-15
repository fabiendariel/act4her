<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessConcurrent
 *
 * @ORM\Table(name="t_access_concurrent_acc")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AccessConcurrentRepository")
 */
class AccessConcurrent
{
    /**
     * @var int
     *
     * @ORM\Column(name="acc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="acc_date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="access")
     * @ORM\JoinColumn(name="acc_usr_id", referencedColumnName="id")
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Professionnel", inversedBy="access")
     * @ORM\JoinColumn(name="acc_prs_id", referencedColumnName="prs_id")
     */
    private $professionnel;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set utilisateur
     *
     * @param integer $utilisateur
     *
     * @return AccessConcurrent
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return int
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set professionnel
     *
     * @param integer $professionnel
     *
     * @return AccessConcurrent
     */
    public function setProfessionnel($professionnel)
    {
        $this->professionnel = $professionnel;

        return $this;
    }

    /**
     * Get professionnel
     *
     * @return int
     */
    public function getProfessionnel()
    {
        return $this->professionnel;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return AccessConcurrent
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set role
     *
     * @param \AppBundle\Entity\Professionnel $role
     *
     * @return AccessConcurrent
     */
    public function setRole(\AppBundle\Entity\Professionnel $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \AppBundle\Entity\Professionnel
     */
    public function getRole()
    {
        return $this->role;
    }
}
