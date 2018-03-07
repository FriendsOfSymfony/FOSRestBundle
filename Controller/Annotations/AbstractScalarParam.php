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

use FOS\RestBundle\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\All;

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

    /** {@inheritdoc} */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();

        if ($this->requirements instanceof Constraint) {
            trigger_error('Using a single constraint as requirements is deprecated. Use an array of constraints instead.', E_USER_DEPRECATED);
            $constraints[] = $this->requirements;
        } elseif (is_scalar($this->requirements)) {
            trigger_error('Using a scalar as requirements is deprecated. Use an array of constraints instead.', E_USER_DEPRECATED);
            $constraints[] = new Regex(array(
                'pattern' => '#^(?:'.$this->requirements.')$#xsu',
                'message' => sprintf(
                    'Parameter \'%s\' value, does not match requirements \'%s\'',
                    $this->getName(),
                    $this->requirements
                ),
            ));
        } elseif (is_array($this->requirements) && isset($this->requirements['rule']) && $this->requirements['error_message']) {
            trigger_error('Using the "rule" and "error_message" options as requirements is deprecated. Use an array of constraints instead.', E_USER_DEPRECATED);
            $constraints[] = new Regex(array(
                'pattern' => '#^(?:'.$this->requirements['rule'].')$#xsu',
                'message' => $this->requirements['error_message'],
            ));
        } elseif (is_array($this->requirements)) {
            foreach ($this->requirements as $requirement) {
                if ($requirement instanceof Constraint) {
                    $constraints[] = $requirement;
                }
            }
        }

        if (false === $this->allowBlank) {
            $constraints[] = new NotBlank();
        }

        // If the user wants to map the value, apply all constraints to every
        // value of the map
        if ($this->map) {
            $constraints = array(
                new All(array('constraints' => $constraints)),
            );
        }

        return $constraints;
    }
}
