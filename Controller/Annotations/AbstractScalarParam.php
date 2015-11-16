<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.Com>
 */
abstract class AbstractScalarParam extends AbstractParam
{
    /** @var mixed */
    public $requirements = null;
    /** @var bool */
    public $map = false;
    /** @var bool */
    public $allowBlank = true;

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();

        $requirements = $this->requirements;

        if ($this->requirements instanceof Constraint) {
            $constraints[] = $requirements;
        } elseif (is_scalar($requirements)) {
            $constraints[] = new Constraints\Regex(array(
                'pattern' => '#^(?:'.$requirements.')$#xsu',
                'message' => sprintf(
                    'Parameter \'%s\' value, does not match requirements \'%s\'',
                    $this->getName(),
                    $requirements
                ),
            ));
        } elseif (is_array($requirements) && isset($requirements['rule']) && $requirements['error_message']) {
            $constraints[] = new Constraints\Regex(array(
                'pattern' => '#^(?:'.$requirements['rule'].')$#xsu',
                'message' => $requirements['error_message'],
            ));
        }

        if (false === $this->allowBlank) {
            $constraints[] = new Constraints\NotBlank();
        }

        // If an array is expected apply the constraints to each element.
        if ($this->map) {
            $constraints = array(
                new Constraints\All($constraints),
            );
        }

        return $constraints;
    }

    public function __get($name)
    {
        if ($name == 'array') {
            $this->triggerArrayDeprecation();

            return $this->map;
        } else {
            parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if ($name == 'array') {
            $this->triggerArrayDeprecation();
            $this->map = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    private function triggerArrayDeprecation()
    {
        @trigger_error('AbstractScalarParam::$array is deprecated since 1.7 and will be removed in 2.0. Use AbstractScalarParam::$map instead.', E_USER_DEPRECATED);
    }
}
