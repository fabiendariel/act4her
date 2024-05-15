<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Specialite
 *
 * @ORM\Table(name="t_specialite_spe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialiteRepository")
 */
class Specialite
{
    /**
     * @var int
     *
     * @ORM\Column(name="spe_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="spe_lib", type="string", length=50)
     */
    private $label;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Professionnel", mappedBy="specialite")
     */
    private $professionnels;

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
     * Set label
     *
     * @param string $label
     *
     * @return Specialite
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->professionnels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add professionnel
     *
     * @param \AppBundle\Entity\Professionnel $professionnel
     *
     * @return Specialite
     */
    public function addProfessionnel(\AppBundle\Entity\Professionnel $professionnel)
    {
        $this->professionnels[] = $professionnel;

        return $this;
    }

    /**
     * Remove professionnel
     *
     * @param \AppBundle\Entity\Professionnel $professionnel
     */
    public function removeProfessionnel(\AppBundle\Entity\Professionnel $professionnel)
    {
        $this->professionnels->removeElement($professionnel);
    }

    /**
     * Get professionnels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfessionnels()
    {
        return $this->professionnels;
    }
}
