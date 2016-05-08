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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Regex;

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
        }

        if (false === $this->allowBlank) {
            $constraints[] = new NotBlank();
        }

        // If the user wants to map the value
        if ($this->map) {
            $constraints = array(
                new All($constraints),
            );
        }

        return $constraints;
    }
}
