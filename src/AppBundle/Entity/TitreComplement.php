<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TitreComplement
 *
 * @ORM\Table(name="t_titre_complement_tic")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TitreComplementRepository")
 */
class TitreComplement
{
    /**
     * @var int
     *
     * @ORM\Column(name="tic_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tic_lib", type="string", length=255)
     */
    private $label;

    /**
     * @var bool
     *
     * @ORM\Column(name="tic_autre", type="boolean")
     */
    private $autre;


    /**
     * @var int
     *
     * @ORM\Column(name="tic_ordre", type="integer")
     */
    private $ordre;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Professionnel", mappedBy="titreComplement")
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
     * @return TitreComplement
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
     * Set autre
     *
     * @param boolean $autre
     *
     * @return TitreComplement
     */
    public function setAutre($autre)
    {
        $this->autre = $autre;

        return $this;
    }

    /**
     * Get autre
     *
     * @return bool
     */
    public function getAutre()
    {
        return $this->autre;
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
     * @return TitreComplement
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

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return TitreComplement
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
}
