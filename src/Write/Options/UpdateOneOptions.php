<?php

namespace Tequilla\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

class UpdateOneOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        UpdateOptions::configureOptions($resolver);
        $resolver->setDefault('multi', false);
        $resolver->setNormalizer('multi', function(Options $options, $multi) {
            if ($multi) {
                throw new InvalidArgumentException(
                    'UpdateOne operation does not allow option "multi" to be true'
                );
            }

            return $multi;
        });
    }
}