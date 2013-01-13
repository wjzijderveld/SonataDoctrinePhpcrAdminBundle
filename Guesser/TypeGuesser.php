<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Guesser;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\MappingException;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class TypeGuesser implements TypeGuesserInterface
{
    protected $registry;

    private $cache;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->cache = array();
    }

    /**
     * @param string $class
     * @param string $property
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface $modelManager
     * @return TypeGuess
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }

        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            switch ($mapping['type']) {
                case ClassMetadata::ONE_TO_MANY:
                    return new TypeGuess('phpcr_one_to_many', array(), Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY_TO_MANY:
                    return new TypeGuess('phpcr_many_to_many', array(), Guess::HIGH_CONFIDENCE);


            }
        }

        switch ($metadata->getTypeOfField($property)) {
            //case 'array':
            //  return new TypeGuess('Collection', array(), Guess::HIGH_CONFIDENCE);
            case 'boolean':
                return new TypeGuess('checkbox', array(), Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess('date', array(), Guess::HIGH_CONFIDENCE);

            case 'decimal':
            case 'double':
                return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'long':
                return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess('integer', array(), Guess::HIGH_CONFIDENCE);
            case 'binary':
            case 'uri':
                return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);
            case 'node':
                return new TypeGuess('node', array(), Guess::MEDIUM_CONFIDENCE);
            case 'reference':
            case 'weakreference':
            default:
                return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }
    }

    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;
        foreach ($this->registry->getManagers() as $name => $dm) {
            try {
                return $this->cache[$class] = array($dm->getClassMetadata($class), $name);
            } catch (MappingException $e) {
                // not an entity or mapped super class
            }
        }
    }
}
