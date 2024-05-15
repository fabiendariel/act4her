<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatutProfessionnel
 *
 * @ORM\Table(name="t_statut_professionnel_stp")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StatutProfessionnelRepository")
 */
class StatutProfessionnel
{
    /**
     * @var int
     *
     * @ORM\Column(name="stp_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="stp_lib", type="string", length=50)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Professionnel", mappedBy="statutProfessionnel")
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
     * @return StatutProfessionnel
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
     * @return StatutProfessionnel
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
