<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

/**
 * Specialized ViewInterface that allows dynamic configuration of JMS serializer context aspects.
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
interface ConfigurableViewHandlerInterface extends ViewHandlerInterface
{
    /**
     * @param string[]|string $groups
     */
    public function setExclusionStrategyGroups($groups);

    public function setExclusionStrategyVersion(string $version);

    public function setSerializeNullStrategy(bool $isEnabled);
}
