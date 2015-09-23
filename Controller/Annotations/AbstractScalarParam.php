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
    public $array = false;
    /** @var bool */
    public $allowBlank = true;

    /** {@inheritdoc} */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();

        $constraints[] = new Constraints\Type(array('type' => 'scalar'));
        $requirements = $this->resolve($this->requirements);

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
        if ($this->array) {
            $constraints = array(
                new Constraints\All($constraints),
            );
        }

        return $constraints;
    }
}
