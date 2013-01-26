<?php

namespace Carew\Plugin\Sami;

use Carew\ExtensionInterface;
use Carew\Carew;
use Carew\Plugin\Sami\Command\UpdateCommand;

class SamiExtension implements ExtensionInterface
{
    public function register(Carew $carew)
    {
        $dic = $carew->getContainer();
        $carew->addCommand(new UpdateCommand($carew->getContainer()->offsetGet('config')));
    }
}
