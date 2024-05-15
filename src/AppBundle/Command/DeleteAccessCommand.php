<?php

namespace AppBundle\Command;

use AppBundle\Entity\Envoi;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// */5 * * * * php /var/www/covidom_onboarding/bin/console appbundle:delete:access

/**
 * Class Generate documentation.
 */
class DeleteAccessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('appbundle:delete:access')
            ->setDescription('Supprime les access de plus de 10 minutes');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $access = $doctrine->getRepository('AppBundle:AccessConcurrent')->getAccessToDelete();

        if ($access) {
            $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
            foreach($access as $acces){
                $entityManager->remove($acces);
                $entityManager->flush();
            }
        } else {
            $output->writeln('<comment>Aucun access</comment>');
        }
    }
}
