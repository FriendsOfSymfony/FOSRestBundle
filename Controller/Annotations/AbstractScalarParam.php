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
use Symfony\Component\Validator\Constraints\NotNull;

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
            $constraints[] = $this->requirements;
        } elseif (is_scalar($this->requirements)) {
            $constraints[] = new Regex(array(
                'pattern' => '#^(?:'.$this->requirements.')$#xsu',
                'message' => sprintf(
                    'Parameter \'%s\' value, does not match requirements \'%s\'',
                    $this->getName(),
                    $this->requirements
                ),
            ));
        } elseif (is_array($this->requirements) && isset($this->requirements['rule']) && $this->requirements['error_message']) {
            $constraints[] = new Regex(array(
                'pattern' => '#^(?:'.$this->requirements['rule'].')$#xsu',
                'message' => $this->requirements['error_message'],
            ));
        } elseif (is_array($this->requirements)) {
            foreach ($this->requirements as $requirement) {
                if ($requirement instanceof Constraint) {
                    $constraints[] = $requirement;
                } else {
                    @trigger_error('Using an array not only containing `Constraint`s as requirements is deprecated since version 2.6.', E_USER_DEPRECATED);
                }
            }
        }

        if (false === $this->allowBlank) {
            $notBlank = new NotBlank();
            if (property_exists(NotBlank::class, 'allowNull')) {
                $notBlank->allowNull = $this->nullable;
            }
            $constraints[] = $notBlank;
        }

        // If the user wants to map the value, apply all constraints to every
        // value of the map
        if ($this->map) {
            $constraints = array(
                new All(array('constraints' => $constraints)),
            );
            if (false === $this->nullable) {
                $constraints[] = new NotNull();
            }
        }

        return $constraints;
    }
}
