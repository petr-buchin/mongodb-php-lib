<?php

namespace Tequila\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DeleteManyOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        DeleteOptions::configureOptions($resolver);
        $resolver->setDefault('limit', 0);
        $resolver->setNormalizer('limit', function (Options $options, $limit) {
            if (1 === $limit) {
                throw new InvalidArgumentException(
                    'Option "limit" cannot be set to 1 for DeleteMany operation. If you want to delete one document - use DeleteOne'
                );
            }

            return $limit;
        });
    }
}