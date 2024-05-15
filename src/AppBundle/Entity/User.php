<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    protected $lastname;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email(message = "L'email {{ value }} n'est pas valide.", checkMX = true)
     */
    protected $email;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $password;


    /**
     * Cet attribut sert à décider si on met à jour le mot de passe en mode édition
     * @var string
     */
    protected $override_constraint;
    

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Professionnel", inversedBy="utilisateurs")
     * @ORM\JoinColumn(name="num_fiche", referencedColumnName="num_fiche_prof", nullable=true)
     */
    private $professionnel;

    /**
     * @var int

     * @ORM\Column(name="nombre_tentative", type="integer")
     */
    private $nombreTentative;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_tentative", type="datetime")
     */
    private $lastTentative;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set firstname
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }


    /**
     * Set password
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set professionnel
     * @param \AppBundle\Entity\Professionnel $professionnel
     * @return User
     */
    public function setProfessionnel(\AppBundle\Entity\Professionnel $professionnel)
    {
        $this->professionnel = $professionnel;

        return $this;
    }

    /**
     * Get professionnel
     * @return \AppBundle\Entity\Professionnel
     */
    public function getProfessionnel()
    {
        return $this->professionnel;
    }

    /**
     * Set lastTentative
     *
     * @param \DateTime $lastTentative
     *
     * @return User
     */
    public function setLastTentative($lastTentative)
    {
        $this->lastTentative = $lastTentative;

        return $this;
    }

    /**
     * Get lastTentative
     *
     * @return \DateTime
     */
    public function getLastTentative()
    {
        return $this->lastTentative;
    }

    /**
     * Set nombreTentative
     *
     * @param integer $nombreTentative
     *
     * @return User
     */
    public function setNombreTentative($nombreTentative)
    {
        $this->nombreTentative = $nombreTentative;

        return $this;
    }

    /**
     * Get nombreTentative
     *
     * @return integer
     */
    public function getNombreTentative()
    {
        return $this->nombreTentative;
    }
}
