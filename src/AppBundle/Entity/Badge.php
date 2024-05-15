<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Badge
 *
 * @ORM\Table(name="t_badge_bad")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BadgeRepository")
 */
class Badge
{
    /**
     * @var int
     *
     * @ORM\Column(name="bad_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="bad_lib", type="string", length=255)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Professionnel", mappedBy="badge")
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
     * @return Badge
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
     * @return Badge
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
