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
     * Set the default serialization groups.
     *
     * @param array $groups
     */
    public function setExclusionStrategyGroups($groups);

    /**
     * Set the default serialization version.
     *
     * @param string $version
     */
    public function setExclusionStrategyVersion($version);

    /**
     * If nulls should be serialized.
     *
     * @param bool $isEnabled
     */
    public function setSerializeNullStrategy($isEnabled);
}
